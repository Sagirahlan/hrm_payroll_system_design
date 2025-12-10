<?php

namespace App\Http\Controllers;

use App\Models\ComputeBeneficiary; // This should probably be renamed to Pensioner now
use App\Services\PensionCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PensionComputationController extends Controller
{
    protected $calculationService;

    public function __construct(PensionCalculationService $calculationService)
    {
        $this->calculationService = $calculationService;
    }

    /**
     * Display the pension computation form
     */
    public function create()
    {
        $retiredEmployees = DB::table('employees')
            ->leftJoin('banks', 'employees.employee_id', '=', 'banks.employee_id')
            ->where('employees.status', 'Retired')
            ->select(
                'employees.employee_id', 
                'employees.first_name', 
                'employees.middle_name', 
                'employees.surname', 
                'employees.staff_no', 
                'employees.date_of_birth', 
                'employees.date_of_first_appointment', 
                'employees.department_id', 
                'employees.rank_id', 
                'employees.grade_level_id', 
                'employees.step_id', 
                'employees.mobile_no',
                'banks.account_no',
                'banks.bank_name'
            )
            ->get();

        $data = [
            'banks' => $this->getBanks(),
            'lgas' => $this->getLGAs(),
            'departments' => $this->getDepartments(),
            'ranks' => $this->getRanks(),
            'salaryScales' => $this->getSalaryScales(),
            'gradeLevels' => $this->getGradeLevels(),
            'steps' => $this->getSteps(),
            'retiredEmployees' => $retiredEmployees,
        ];

        return view('pension.computation', $data);
    }

    /**
     * Get steps based on GL ID
     */
    public function getStepsByGL(Request $request)
    {
        $glId = $request->input('gl_id');

        if (!$glId) {
            return response()->json(['steps' => []]);
        }

        $steps = DB::table('steps')
            ->where('grade_level_id', $glId)
            ->orderBy('name', 'asc')
            ->get(['id as stepid', 'name as step']);

        return response()->json(['steps' => $steps]);
    }

    /**
     * Compute gratuity and pension
     */
    public function compute(Request $request)
    {
        $validator = $this->validateInputs($request);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();

            // Calculate date span
            $apptDate = \Carbon\Carbon::parse($data['appt_date']);
            $retirementDate = \Carbon\Carbon::parse($data['dod_r']);
            $dob = \Carbon\Carbon::parse($data['dob']);

            $dateSpan = $this->calculationService->getDateSpan($apptDate, $retirementDate);
            $totalMonths = $this->calculationService->getTotalMonths($apptDate, $retirementDate);

            // Get basic salary
            $salaryData = $this->calculationService->getStaffBasicSalaryAtRetirement(
                $data['stepid'],
                $retirementDate,
                $data['salary_scale_id']
            );

            if ($salaryData['salary_scale_circular_id'] <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Salary Scale Circular Not Found!'
                ], 422);
            }

            // Check if service years should be elevated
            $isElevated = ($dateSpan['months'] >= 6) ? 1 : 0;

            // Get computation percentages
            $computationYrs = $this->calculationService->getComputationYears(
                $isElevated,
                $dateSpan['years']
            );

            $percentages = $this->calculationService->getComputationPercentages($computationYrs);

            if ($percentages['gratuity_pct'] <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Valid Gratuity found for Staff!'
                ], 422);
            }

            // Calculate amounts
            $basicSalaryPerAnnum = $salaryData['rate_per_annum'];
            $totalEmolument = $basicSalaryPerAnnum;
            $calculatedGratuity = round($percentages['gratuity_pct'] * ($basicSalaryPerAnnum / 100), 2);
            $calculatedPensionPerAnnum = round($percentages['pension_pct'] * ($basicSalaryPerAnnum / 100), 2);
            $calculatedPensionPerMnth = round($calculatedPensionPerAnnum / 12, 2);

            $gratuityType = $data['gtype'] == 'DG' ? 'Death Gratuity' : 'Gratuity';
            $pensionType = $data['gtype'] == 'DG' ? 'Accrued Pension' : 'Pension';

            $accruedPensionYrs = 0;
            $accruedPension = 0;
            $totalDeathGratuity = 0;

            if ($data['gtype'] == 'DG') {
                $accruedPensionYrs = 5;
                $accruedPension = round($calculatedPensionPerAnnum * $accruedPensionYrs, 2);
                $totalDeathGratuity = $calculatedGratuity + $accruedPension;
            }

            // Calculate overstay
            $ageSpan = $this->calculationService->getDateSpan($dob, $retirementDate);
            $overstayRemark = $this->calculationService->calculateOverstay($ageSpan, $dateSpan);

            // Calculate apportionment (if needed)
            $apportionment = $this->calculateApportionment($calculatedGratuity, $calculatedPensionPerAnnum, $data);

            return response()->json([
                'success' => true,
                'computation' => [
                    'period' => [
                        'from' => $apptDate->format('d-m-Y'),
                        'to' => $retirementDate->format('d-m-Y'),
                        'years' => $dateSpan['years'],
                        'months' => $dateSpan['months'],
                        'days' => $dateSpan['days'],
                        'total_months' => $totalMonths,
                    ],
                    'basic_salary' => [
                        'per_annum' => number_format($basicSalaryPerAnnum, 2),
                        'per_month' => number_format($salaryData['rate_per_mnth'], 2),
                    ],
                    'total_emolument' => number_format($totalEmolument, 2),
                    'gratuity' => [
                        'type' => $gratuityType,
                        'percentage' => $percentages['gratuity_pct'],
                        'amount' => number_format($calculatedGratuity, 2),
                    ],
                    'pension' => [
                        'type' => $pensionType,
                        'percentage' => $percentages['pension_pct'],
                        'per_annum' => number_format($calculatedPensionPerAnnum, 2),
                        'per_month' => number_format($calculatedPensionPerMnth, 2),
                    ],
                    'accrued_pension' => [
                        'years' => $accruedPensionYrs,
                        'amount' => number_format($accruedPension, 2),
                    ],
                    'total_death_gratuity' => number_format($totalDeathGratuity, 2),
                    'overstay' => $overstayRemark,
                    'apportionment' => $apportionment,
                    'salary_scale_circular_id' => $salaryData['salary_scale_circular_id'],
                    'is_elevated_service_yrs' => $isElevated,
                    'service_yrs_for_compute' => $computationYrs,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error computing pension: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save beneficiary computation
     */
    public function store(Request $request)
    {
        $validator = $this->validateInputs($request);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // First compute to get all values
            $computeResponse = $this->compute($request);
            $computeData = json_decode($computeResponse->getContent(), true);

            if (!$computeData['success']) {
                return $computeResponse;
            }

            $computation = $computeData['computation'];
            $data = $request->all();

            // Get rank name
            $rank = DB::table('ranks')
                ->where('id', $data['rankid'])
                ->value('name');

            // Get salary scale
            $salaryScale = DB::table('salary_scales')
                ->where('id', $data['salary_scale_id'])
                ->first();

            // Use the ComputeBeneficiary model for pension computations
            $beneficiary = \App\Models\ComputeBeneficiary::create([
                'fulname' => ucwords(strtolower($data['fulname'])),
                'lgaid' => $data['lgaid'],
                'gtype' => $data['gtype'],
                'acc_no' => $data['acc_no'] ?? null,
                'bankid' => $data['bankid'] ?? null,
                'reg_user' => auth()->id(),
                'reg_date' => now(),
                'stepid' => $data['stepid'],
                'deptid' => $data['deptid'],
                'mobile' => $data['mobile'] ?? null,
                'nxtkin_fulname' => ucwords(strtolower($data['nxtkin_fulname'] ?? '')),
                'nxtkin_mobile' => $data['nxtkin_mobile'] ?? null,
                'appt_date' => $data['appt_date'],
                'dod_r' => $data['dod_r'],
                'id_no' => trim($data['id_no']),
                'period_yrs' => $computation['period']['years'],
                'period_mnths' => $computation['period']['months'],
                'period_days' => $computation['period']['days'],
                'period_total_mnths' => $computation['period']['total_months'],
                'basic_sal_annum' => str_replace(',', '', $computation['basic_salary']['per_annum']),
                'basic_sal_mnth' => str_replace(',', '', $computation['basic_salary']['per_month']),
                'total_emolument' => str_replace(',', '', $computation['total_emolument']),
                'pct_gratuity' => $computation['gratuity']['percentage'],
                'pct_pension' => $computation['pension']['percentage'],
                'gratuity_amt' => str_replace(',', '', $computation['gratuity']['amount']),
                'total_death_gratuity' => str_replace(',', '', $computation['total_death_gratuity']),
                'pension_per_annum' => str_replace(',', '', $computation['pension']['per_annum']),
                'pension_per_mnth' => str_replace(',', '', $computation['pension']['per_month']),
                'accrued_pension' => str_replace(',', '', $computation['accrued_pension']['amount']),
                'accrued_pension_yrs' => $computation['accrued_pension']['years'],
                'apportion_fg_pct' => $computation['apportionment']['fg_pct'],
                'apportion_state_pct' => $computation['apportionment']['state_pct'],
                'apportion_lga_pct' => $computation['apportionment']['lga_pct'],
                'apportion_fg_amt' => str_replace(',', '', $computation['apportionment']['fg_amt']),
                'apportion_state_amt' => str_replace(',', '', $computation['apportionment']['state_amt']),
                'apportion_lga_amt' => str_replace(',', '', $computation['apportionment']['lga_amt']),
                'sscale_circular_id' => $computation['salary_scale_circular_id'],
                'is_elevated_service_yrs' => $computation['is_elevated_service_yrs'],
                'service_yrs_for_compute' => $computation['service_yrs_for_compute'],
                'status' => 0,
                'approval_date' => null,
                'open_file_no' => $data['open_file_no'] ?? null,
                'secret_file_no' => $data['secret_file_no'] ?? null,
                'dob' => $data['dob'],
                'overstay_remark' => $computation['overstay'],
                'rank' => $rank,
                'rankid' => $data['rankid'],
                'salary_scale_id' => $data['salary_scale_id'],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Beneficiary Saved Successfully.',
                'computation_id' => $beneficiary->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saving beneficiary: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error saving beneficiary. Please try again. If the problem persists, contact Administrator.'
            ], 500);
        }
    }

    /**
     * Validate form inputs
     */
    private function validateInputs(Request $request)
    {
        return Validator::make($request->all(), [
            'gtype' => 'required|in:RB,DG',
            'fulname' => 'required|string|max:255',
            'lgaid' => 'required|integer',
            'deptid' => 'required|integer',
            'rankid' => 'required|integer',
            'salary_scale_id' => 'required|integer',
            'gl_id' => 'required|integer',
            'stepid' => 'required|integer',
            'id_no' => 'required|string|max:255',
            'appt_date' => 'required|date',
            'dod_r' => 'required|date|after_or_equal:appt_date',
            'dob' => 'required|date|before:appt_date',
            'bankid' => 'nullable|integer',
            'acc_no' => 'nullable|string|required_with:bankid',
            'mobile' => 'nullable|string|min:11|max:11',
            'nxtkin_mobile' => 'nullable|string|min:11|max:11',
            'open_file_no' => 'nullable|string|max:255',
            'secret_file_no' => 'nullable|string|max:255',
        ]);
    }

    /**
     * Calculate apportionment percentages and amounts
     */
    private function calculateApportionment($gratuity, $pension, $data)
    {
        // Default apportionment values - adjust based on your business logic
        $fgPct = $data['apportion_fg_pct'] ?? 0;
        $statePct = $data['apportion_state_pct'] ?? 0;
        $lgaPct = $data['apportion_lga_pct'] ?? 0;

        $total = $gratuity + $pension;

        return [
            'fg_pct' => $fgPct,
            'state_pct' => $statePct,
            'lga_pct' => $lgaPct,
            'fg_amt' => round($total * ($fgPct / 100), 2),
            'state_amt' => round($total * ($statePct / 100), 2),
            'lga_amt' => round($total * ($lgaPct / 100), 2),
        ];
    }

    /**
     * Get banks from database
     */
    private function getBanks()
    {
        return DB::table('bank_list')
            ->where('is_active', 1)
            ->orderBy('bank_name', 'asc')
            ->get(['id as bankid', 'bank_name as bank']);
    }

    /**
     * Get LGAs from database
     */
    private function getLGAs()
    {
        return DB::table('lgas')
            ->orderBy('name', 'asc')
            ->get(['id as lgaid', 'name as lga', 'state_id as zoneid']);
    }

    /**
     * Get departments from database
     */
    private function getDepartments()
    {
        return DB::table('departments')
            ->orderBy('department_name', 'asc')
            ->get(['department_id as deptid', 'department_name as dept']);
    }

    /**
     * Get ranks from database
     */
    private function getRanks()
    {
        return DB::table('ranks')
            ->orderBy('name', 'asc')
            ->get(['id as rankid', 'name as rank']);
    }

    /**
     * Get salary scales from database
     */
    private function getSalaryScales()
    {
        return DB::table('salary_scales')
            ->orderBy('full_name', 'asc')
            ->get(['id as salary_scale_id', 'full_name as salary_scale_title']);
    }

    /**
     * Get grade levels from database
     */
    private function getGradeLevels()
    {
        return DB::table('grade_levels')
            ->orderBy('name', 'asc')
            ->get(['id as gl_id', 'name as grade']);
    }

    /**
     * Get all steps from database
     */
    private function getSteps()
    {
        return DB::table('steps')
            ->orderBy('name', 'asc')
            ->get(['id as stepid', 'grade_level_id as gl_id', 'name as step']);
    }
}