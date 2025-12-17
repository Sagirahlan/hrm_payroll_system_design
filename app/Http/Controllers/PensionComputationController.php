<?php

namespace App\Http\Controllers;

use App\Models\ComputeBeneficiary;
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
        $this->middleware(['auth']);
        // Apply permissions to all methods except create
        $this->middleware(['permission:manage_pensioners'], ['except' => ['create']]);
    }

    /**
     * Display the pension computation form
     */
    public function create(Request $request)
    {
        // Check that user has either pension management or retirement management permissions
        $user = auth()->user();
        if (!$user || (!$user->can('manage_pensioners') && !$user->can('manage_retirement'))) {
            abort(403, 'Unauthorized access to pension computation.');
        }

        $data = [
            'banks' => $this->getBanks(),
            'lgas' => $this->getLGAs(),
            'departments' => $this->getDepartments(),
            'ranks' => $this->getRanks(),
            'salaryScales' => $this->getSalaryScales(),
            'gradeLevels' => $this->getGradeLevels(),
            'steps' => $this->getSteps(),
            'retiredEmployees' => $this->getRetiredEmployees(),
        ];

        // Check if there are query parameters to pre-fill the form
        if ($request->has('employee_id')) {
            $employee = \App\Models\Employee::with(['bank', 'nextOfKin', 'gradeLevel', 'gradeLevel.salaryScale', 'department', 'rank', 'step'])->where('employee_id', $request->query('employee_id'))->first();

            if ($employee) {
                // Try to get the corresponding bank from bank_list table
                $bankId = null;
                if ($employee->bank) {
                    // First try to look up the bank in bank_list table by bank code
                    $bankList = DB::table('bank_list')
                        ->where('bank_code', $employee->bank->bank_code ?? $employee->bank->id)
                        ->first();

                    // If not found by code, try by name if available
                    if (!$bankList && isset($employee->bank->bank_name)) {
                        $bankList = DB::table('bank_list')
                            ->where(function($query) use ($employee) {
                                $query->where('bank_name', 'LIKE', '%' . $employee->bank->bank_name . '%')
                                      ->orWhere('bank_name', 'LIKE', '%' . str_replace(' ', '', $employee->bank->bank_name ?? '') . '%');
                            })
                            ->first();
                    }

                    if ($bankList) {
                        $bankId = $bankList->id;
                    }
                }

                $data['pre_filled_data'] = [
                    'fulname' => trim("{$employee->surname} {$employee->first_name} {$employee->middle_name}"),
                    'id_no' => $employee->staff_no ?? $employee->employee_id,
                    'appt_date' => $employee->date_of_first_appointment ? \Carbon\Carbon::parse($employee->date_of_first_appointment)->format('Y-m-d') : null,
                    'dob' => $employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->format('Y-m-d') : null,
                    'mobile' => $employee->mobile_no, // Correct field name
                    'deptid' => $employee->department_id,
                    'rankid' => $employee->rank_id,
                    'lgaid' => $employee->lga_id,
                    'gl_id' => $employee->grade_level_id,
                    'stepid' => $employee->step_id,
                    'salary_scale_id' => $employee->gradeLevel && $employee->gradeLevel->salaryScale ? $employee->gradeLevel->salaryScale->id : null,
                    'bankid' => $bankId, // Use the bank_id from bank_list table
                    'acc_no' => $employee->bank ? $employee->bank->account_no : null, // Correct field from bank model
                    'nxtkin_fulname' => $employee->nextOfKin ? $employee->nextOfKin->name : null, // From nextOfKin relationship
                    'nxtkin_mobile' => $employee->nextOfKin ? $employee->nextOfKin->mobile_no : null, // From nextOfKin relationship
                    'dod_r' => $request->query('retire_date', now()->toDateString()), // Use provided retire_date or default to today
                ];
            }
        }

        return view('pension.computation', $data);
    }


    /**
     * Get retired employees
     */
    private function getRetiredEmployees()
    {
        return DB::table('employees')
            ->where('status', 'Retired')
            ->orderBy('surname', 'asc')
            ->select('employee_id', 'first_name', 'surname', 'middle_name', 'staff_no')
            ->get()
            ->map(function($emp) {
                // Ensure unique name handling if needed, or just formatting
                $emp->fullname = trim("{$emp->surname} {$emp->first_name} {$emp->middle_name}");
                return $emp;
            });
    }

    /**
     * Get employee details for auto-filling form
     */
    public function getEmployeeDetails(Request $request) 
    {
        $employeeId = $request->query('employee_id');
        
        if (!$employeeId) {
            return response()->json(['success' => false, 'message' => 'Employee ID required'], 400);
        }

        $employee = \App\Models\Employee::with(['bank', 'nextOfKin', 'gradeLevel', 'gradeLevel.salaryScale'])
            ->where('employee_id', $employeeId)
            ->first();

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found'], 404);
        }

        // Determine retirement date - check retirement record first, or calculate
        $retirementDate = null;
        $retirementRecord = \App\Models\Retirement::where('employee_id', $employeeId)->first();
        if ($retirementRecord) {
            $retirementDate = $retirementRecord->retirement_date->format('Y-m-d');
        } elseif ($employee->expected_retirement_date) {
            $retirementDate = \Carbon\Carbon::parse($employee->expected_retirement_date)->format('Y-m-d');
        }

        return response()->json([
            'success' => true,
            'data' => [
                'fulname' => trim("{$employee->surname} {$employee->first_name} {$employee->middle_name}"),
                'id_no' => $employee->staff_no ?? $employee->employee_id,
                'appt_date' => $employee->date_of_first_appointment ? \Carbon\Carbon::parse($employee->date_of_first_appointment)->format('Y-m-d') : null,
                'dob' => $employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->format('Y-m-d') : null,
                'mobile' => $employee->mobile_no,
                'deptid' => $employee->department_id,
                'rankid' => $employee->rank_id,
                'lgaid' => $employee->lga_id,
                'gl_id' => $employee->grade_level_id,
                'stepid' => $employee->step_id,
                'salary_scale_id' => $employee->gradeLevel && $employee->gradeLevel->salaryScale ? $employee->gradeLevel->salaryScale->id : null,
                'bankid' => $employee->bank ? $employee->bank->bank_id : null, // Note: Assuming bank_id in employees table or linked bank model
                'acc_no' => $employee->bank ? $employee->bank->account_no : null,
                'dod_r' => $retirementDate,
                'nxtkin_fulname' => $employee->nextOfKin ? $employee->nextOfKin->name : null,
                'nxtkin_mobile' => $employee->nextOfKin ? $employee->nextOfKin->mobile_no : null, // Assuming mobile column in NextOfKin
            ]
        ]);
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
            ->select('id as stepid', 'name as step')
            ->get();

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
                'message' => 'Validation Error',
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

            \Illuminate\Support\Facades\Log::info('Pension Computation Debug:', [
                'appt_date' => $data['appt_date'],
                'retirement_date' => $data['dod_r'],
                'date_span' => $dateSpan,
                'is_elevated' => $isElevated,
                'computation_years' => $computationYrs
            ]);

            $percentages = $this->calculationService->getComputationPercentages($computationYrs);

            \Illuminate\Support\Facades\Log::info('Fetched Percentages:', $percentages);

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

            // Calculate overstay amount using new service method
            // Sanitize salary input to remove commas if present
            $monthlySalaryClean = str_replace(',', '', $salaryData['rate_per_mnth']);
            
            $overstayData = $this->calculationService->calculateOverstayAmount(
                $dob, 
                $apptDate, 
                $retirementDate, 
                (float)$monthlySalaryClean
            );

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
                    // Add overstay data to response
                    'overstay' => $overstayRemark,
                    'overstay_amount' => number_format($overstayData['amount'], 2),
                    'overstay_days' => $overstayData['days'],

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
                'message' => 'Validation Error',
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

            $beneficiary = ComputeBeneficiary::create([
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

            // Update employee status to Retired
            $employee = \App\Models\Employee::find($data['employee_id']);
            if ($employee) {
                $employee->update(['status' => 'Retired']);

                // Create or update Retirement record
                $retireReason = ($data['gtype'] === 'DG') ? 'Death in Service' : 'Statutory';
                $storedGratuityAmount = ($data['gtype'] === 'DG') 
                    ? str_replace(',', '', $computation['total_death_gratuity']) 
                    : str_replace(',', '', $computation['gratuity']['amount']);

                $retirement = \App\Models\Retirement::firstOrCreate(
                    ['employee_id' => $employee->employee_id],
                    [
                        'retirement_date' => $data['dod_r'],
                        'notification_date' => now(), // Default to now if unknown
                        'gratuity_amount' => $storedGratuityAmount,
                        'status' => 'Approved',
                        'retire_reason' => $retireReason,
                        'years_of_service' => $computation['service_yrs_for_compute'],
                    ]
                );

                // Create Pensioner record if not exists
                if (!\App\Models\Pensioner::where('retirement_id', $retirement->id)->exists()) {
                    \Log::info('Attempting to create Pensioner record for Employee: ' . $employee->employee_id);
                    try {
                     $pensionerStatus = ($data['gtype'] === 'DG') ? 'Deceased' : 'Active';
                     
                     $pensioner = \App\Models\Pensioner::create([
                        'employee_id' => $employee->employee_id,
                        'full_name' => $employee->full_name,
                        'surname' => $employee->surname,
                        'first_name' => $employee->first_name,
                        'middle_name' => $employee->middle_name,
                        'email' => $employee->email,
                        'phone_number' => $employee->mobile_no, // Corrected from phone
                        'date_of_birth' => $data['dob'],
                        'place_of_birth' => null, // Employee model doesn't have this, setting to null
                        'date_of_first_appointment' => $data['appt_date'],
                        'date_of_retirement' => $data['dod_r'],
                        'retirement_reason' => $retirement->retire_reason,
                        'retirement_type' => $data['gtype'], // RB or DG
                        'department_id' => $data['deptid'],
                        'rank_id' => $data['rankid'],
                        'step_id' => $data['stepid'],
                        'grade_level_id' => $data['gl_id'],
                        'salary_scale_id' => $data['salary_scale_id'],
                        'local_gov_area_id' => $data['lgaid'],
                        'bank_id' => $data['bankid'] ?? null,
                        'account_number' => $data['acc_no'] ?? null,
                        'account_name' => $data['fulname'], 
                        'pension_amount' => str_replace(',', '', $computation['pension']['per_month']), // Monthly pension
                        'gratuity_amount' => $storedGratuityAmount,
                        'total_death_gratuity' => str_replace(',', '', $computation['total_death_gratuity']),
                        'years_of_service' => $computation['service_yrs_for_compute'],
                        'pension_percentage' => $computation['pension']['percentage'],
                        'gratuity_percentage' => $computation['gratuity']['percentage'],
                        'address' => $employee->address,
                        'next_of_kin_name' => $data['nxtkin_fulname'] ?? null,
                        'next_of_kin_phone' => $data['nxtkin_mobile'] ?? null,
                        'next_of_kin_address' => $employee->next_of_kin_address, // Assuming exists
                        'status' => $pensionerStatus,
                        'retirement_id' => $retirement->id,
                        'beneficiary_computation_id' => $beneficiary->id,
                        'created_by' => auth()->id(),
                    ]);
                    \Log::info('Pensioner created: ' . $pensioner->id);
                    } catch (\Exception $e) {
                        \Log::error('Failed to create Pensioner record: ' . $e->getMessage());
                        throw $e; // Re-throw to trigger rollback
                    }
                } else {
                    \Log::info('Pensioner record already exists for Retirement ID: ' . $retirement->id);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Beneficiary Saved & Pensioner Record Created Successfully.',
                'computation_id' => $beneficiary->id,
                'redirect_url' => route('pensioners.index')
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
            'employee_id' => 'required|exists:employees,employee_id',
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
            ->select('id as lgaid', 'name as lga')
            ->get();
    }

    /**
     * Get departments from database
     */
    private function getDepartments()
    {
        return DB::table('departments')
            ->orderBy('department_name', 'asc')
            ->select('department_id as deptid', 'department_name as dept')
            ->get();
    }

    /**
     * Get ranks from database
     */
    private function getRanks()
    {
        return DB::table('ranks')
            ->orderBy('name', 'asc')
            ->select('id as rankid', 'name as rank')
            ->get();
    }

    /**
     * Get salary scales from database
     */
    private function getSalaryScales()
    {
        return DB::table('salary_scales')
            ->orderBy('full_name', 'asc')
            ->select('id as salary_scale_id', 'full_name as salary_scale_title')
            ->get();
    }

    /**
     * Get grade levels from database
     */
    private function getGradeLevels()
    {
        return DB::table('grade_levels')
            ->orderBy('name', 'asc')
            ->select('id as gl_id', 'name as grade')
            ->get();
    }

    /**
     * Get all steps from database
     */
    private function getSteps()
    {
        return DB::table('steps')
            ->orderBy('name', 'asc')
            ->select('id as stepid', 'grade_level_id as gl_id', 'name as step')
            ->get();
    }
}

