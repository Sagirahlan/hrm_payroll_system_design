<?php

namespace App\Http\Controllers;

use App\Models\{Retirement, Employee, PayrollRecord, AuditTrail};
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RetirementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(['permission:view_retirements'], ['only' => ['index', 'retiredList', 'getAllRetiredStatuses']]);
        $this->middleware(['permission:create_retirement'], ['only' => ['create', 'store']]);
    }

    public function index(Request $request)
    {
        $query = Employee::with('gradeLevel.salaryScale', 'department')
            ->where('status', 'Active');

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('employee_id', 'like', "%{$searchTerm}%")
                  ->orWhere('first_name', 'like', "%{$searchTerm}%")
                  ->orWhere('surname', 'like', "%{$searchTerm}%");
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $employees = $query->get();

        $sixMonthsFromNow = now()->addMonths(6);

        $approachingRetirement = $employees->filter(function ($employee) use ($sixMonthsFromNow) {
            if (!$employee->gradeLevel || !$employee->gradeLevel->salaryScale) {
                return false;
            }

            $retirementAge = (int) $employee->gradeLevel->salaryScale->max_retirement_age;
            $yearsOfService = (int) $employee->gradeLevel->salaryScale->max_years_of_service;

            // Calculate retirement date based on age
            $retirementDateByAge = Carbon::parse($employee->date_of_birth)->addYears($retirementAge);

            // Calculate retirement date based on service
            $retirementDateByService = Carbon::parse($employee->date_of_first_appointment)->addYears($yearsOfService);

            // The actual retirement date is the earlier of the two
            $actualRetirementDate = $retirementDateByAge->min($retirementDateByService);

            return $actualRetirementDate->isBetween(now(), $sixMonthsFromNow);
        });

        // Add calculated properties to each employee for use in the view
        $approachingRetirement = $approachingRetirement->map(function ($employee) {
            $retirementAge = (int) $employee->gradeLevel->salaryScale->max_retirement_age;
            $yearsOfService = (int) $employee->gradeLevel->salaryScale->max_years_of_service;

            // Calculate retirement date based on age
            $retirementDateByAge = Carbon::parse($employee->date_of_birth)->addYears($retirementAge);

            // Calculate retirement date based on service
            $retirementDateByService = Carbon::parse($employee->date_of_first_appointment)->addYears($yearsOfService);

            // The actual retirement date is the earlier of the two
            $actualRetirementDate = $retirementDateByAge->min($retirementDateByService);

            // Add calculated properties
            $employee->calculated_retirement_date = $actualRetirementDate;
            $employee->age = Carbon::parse($employee->date_of_birth)->age;
            $employee->expected_retirement_date = $actualRetirementDate->format('Y-m-d');

            // Determine retirement reason
            $age = Carbon::parse($employee->date_of_birth)->age;
            $serviceDuration = Carbon::parse($employee->date_of_first_appointment)->diffInYears(Carbon::now());

            // Check if the employee has reached the maximum years of service first
            if ($serviceDuration >= $yearsOfService) {
                $employee->retirement_reason = 'By Years of Service';
            } elseif ($age >= $retirementAge) {
                $employee->retirement_reason = 'By Old Age';
            } else {
                // If neither condition is met, determine by which will happen first
                $actualRetirementDate = Carbon::parse($employee->date_of_birth)->addYears($retirementAge)->min(Carbon::parse($employee->date_of_first_appointment)->addYears($yearsOfService));
                if ($actualRetirementDate->eq(Carbon::parse($employee->date_of_birth)->addYears($retirementAge))) {
                    $employee->retirement_reason = 'By Old Age';
                } else {
                    $employee->retirement_reason = 'By Years of Service';
                }
            }

            // Calculate years of service
            $employee->years_of_service = round($serviceDuration, 1);

            return $employee;
        });

        // Manually paginate the filtered collection
        $page = $request->get('page', 1);
        $perPage = 10;
        $paginatedItems = new \Illuminate\Pagination\LengthAwarePaginator(
            $approachingRetirement->forPage($page, $perPage),
            $approachingRetirement->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'viewed',
            'description' => 'Viewed employees approaching retirement',
            'action_timestamp' => now(),
            'entity_type' => 'Retirement',
            'entity_id' => 0,
        ]);

        return view('retirements.index', ['retirements' => $paginatedItems]);
    }

    public function retiredList(Request $request)
    {
        // Get all employees who have retirement records
        $query = \App\Models\Employee::with(['gradeLevel', 'department', 'retirement', 'rank', 'step'])
            ->where('status', 'Retired')
            ->whereHas('retirement'); // Only include employees who have retirement records

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('employee_id', 'like', "%{$searchTerm}%")
                  ->orWhere('first_name', 'like', "%{$searchTerm}%")
                  ->orWhere('surname', 'like', "%{$searchTerm}%");
            });
        }

        // Apply retirement date filter if retirement record exists
        if ($request->filled('retirement_date')) {
            $query->whereHas('retirement', function ($q) use ($request) {
                $q->whereDate('retirement_date', $request->retirement_date);
            });
        }

        $retiredEmployees = $query->orderBy('surname', 'asc')->paginate(10);

        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'viewed_retired_list',
            'description' => 'Viewed list of retired employees.',
            'action_timestamp' => now(),
            'entity_type' => 'Employee',
            'entity_id' => null,
        ]);

        return view('retirements.retired', compact('retiredEmployees'));
    }

    public function getAllRetiredStatuses()
    {
        // Get all retirement records with status containing "retired" (case insensitive)
        $retiredRecords = \App\Models\Retirement::whereRaw('LOWER(status) LIKE ?', ['%retired%'])
            ->with('employee')
            ->get();

        // Get distinct statuses
        $distinctStatuses = \App\Models\Retirement::select('status')
            ->distinct()
            ->whereRaw('LOWER(status) LIKE ?', ['%retired%'])
            ->pluck('status');

        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'viewed_all_retired_statuses',
            'description' => 'Viewed all retired statuses.',
            'action_timestamp' => now(),
            'entity_type' => 'Retirement',
            'entity_id' => null,
        ]);

        return response()->json([
            'retired_records' => $retiredRecords,
            'distinct_statuses' => $distinctStatuses,
            'count' => $retiredRecords->count()
        ]);
    }

    public function create()
    {
        $employees = Employee::with(['gradeLevel.salaryScale', 'department', 'rank', 'step'])
            ->whereIn('status', ['Active', 'Deceased'])
            ->get();

        $eligibleEmployees = $employees->filter(function ($employee) {
            // Automatically include Deceased employees
            if ($employee->status === 'Deceased') {
                return true;
            }

            if (!$employee->gradeLevel || !$employee->gradeLevel->salaryScale) {
                return false;
            }

            $retirementAge = (int) $employee->gradeLevel->salaryScale->max_retirement_age;
            $yearsOfService = (int) $employee->gradeLevel->salaryScale->max_years_of_service;

            // Check if required dates exist before parsing
            if (!$employee->date_of_birth || !$employee->date_of_first_appointment) {
                return false;
            }

            $age = \Carbon\Carbon::parse($employee->date_of_birth)->age;
            $serviceDuration = \Carbon\Carbon::parse($employee->date_of_first_appointment)->diffInYears(\Carbon\Carbon::now());

            return $age >= $retirementAge || $serviceDuration >= $yearsOfService;
        });

        return view('retirements.create', compact('eligibleEmployees'));
    }

    public function store(Request $request)
    {
        try {
            \Log::info('Retirement store method called with data:', $request->all());

            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,employee_id',
                'retirement_date' => 'required|date',
                'notification_date' => 'nullable|date',
                'gratuity_amount' => 'nullable|numeric|min:0',
                'status' => 'nullable|string|in:pending,complete',
                'retire_reason' => 'nullable|string|max:500',
            ]);

            \Log::info('Validation passed, validated data:', $validated);

            $employee = Employee::with('gradeLevel.salaryScale')->where('employee_id', $validated['employee_id'])->firstOrFail();
            \Log::info('Found employee:', ['employee_data' => $employee->toArray()]);

            if (Retirement::where('employee_id', $employee->employee_id)->exists()) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Employee already processed for retirement.'
                    ], 400);
                }
                return redirect()->back()->with('error', 'Employee already processed for retirement.');
            }

            // Determine the actual retirement reason based on which condition was met
            $retireReason = $validated['retire_reason'] ?? null;
            
            // For Deceased employees, force the reason
            if ($employee->status === 'Deceased') {
                $retireReason = 'Death in Service';
            }
            // Logic for active employees if reason not provided
            elseif (!$retireReason && $employee->gradeLevel && $employee->gradeLevel->salaryScale) {
                $retirementAge = (int) $employee->gradeLevel->salaryScale->max_retirement_age;
                $yearsOfService = (int) $employee->gradeLevel->salaryScale->max_years_of_service;

                $age = \Carbon\Carbon::parse($employee->date_of_birth)->age;
                $serviceDuration = \Carbon\Carbon::parse($employee->date_of_first_appointment)->diffInYears(\Carbon\Carbon::now());

                // Check if the employee has reached the maximum years of service first
                if ($serviceDuration >= $yearsOfService) {
                    $retireReason = 'By Years of Service';
                } elseif ($age >= $retirementAge) {
                    $retireReason = 'By Old Age';
                } else {
                    // If neither condition is met, determine by which will happen first
                    $actualRetirementDate = \Carbon\Carbon::parse($employee->date_of_birth)->addYears($retirementAge)->min(\Carbon\Carbon::parse($employee->date_of_first_appointment)->addYears($yearsOfService));
                    if ($actualRetirementDate->eq(\Carbon\Carbon::parse($employee->date_of_birth)->addYears($retirementAge))) {
                        $retireReason = 'By Old Age';
                    } else {
                        $retireReason = 'By Years of Service';
                    }
                }
            }

            \Log::info('Retirement data to be created:', [
                'employee_id' => $employee->employee_id,
                'retirement_date' => $validated['retirement_date'],
                'status' => 'Completed',
                'notification_date' => $validated['notification_date'] ?? now(),
                'gratuity_amount' => $validated['gratuity_amount'] ?? $this->calculateGratuity($employee),
                'retire_reason' => $retireReason,
            ]);

            $retirement = Retirement::create([
                'employee_id' => $employee->employee_id,
                'retirement_date' => $validated['retirement_date'],
                'status' => 'complete',
                'notification_date' => $validated['notification_date'] ?? now(),
                'gratuity_amount' => $validated['gratuity_amount'] ?? $this->calculateGratuity($employee),
                'retire_reason' => $retireReason,
            ]);

            $employee->update([
                'status' => 'Retired',
                'date_of_retirement' => $validated['retirement_date']
            ]);

            // Now move the retired employee to the pensioners table
            $this->moveToPensioners($employee, $retirement);

            AuditTrail::create([
                'user_id' => auth()->id(),
                'action' => 'created',
                'description' => 'Retirement processed for employee ID ' . $employee->employee_id,
                'action_timestamp' => now(),
                'entity_type' => 'Retirement',
                'entity_id' => $retirement->id,
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Retirement processed successfully and employee moved to pensioners.',
                    'data' => [
                        'employee_id' => $employee->employee_id,
                        'retirement_id' => $retirement->id,
                        'retire_reason' => $retireReason,
                        'gratuity_amount' => $retirement->gratuity_amount
                    ]
                ]);
            }

            return redirect()->route('retirements.create')->with([
                'success' => 'Retirement processed successfully and employee moved to pensioners.',
                'processed_employee_id' => $employee->employee_id,
            ]);

        } catch (\Exception $e) {
            \Log::error('Retirement processing error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while processing retirement: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'An error occurred while processing retirement: ' . $e->getMessage());
        }
    }

    private function calculateGratuity(Employee $employee)
    {
        // Get the last payroll record for the employee (by created_at, fallback to payroll_date)
        $lastPayroll = \App\Models\PayrollRecord::where('employee_id', $employee->employee_id)
            ->orderByDesc('created_at')
            ->first();

        if (!$lastPayroll || !$employee->date_of_first_appointment) {
            return 0;
        }

        // Use the retirement date if available, otherwise use today
        $retirementDate = $employee->retirement_date
            ? \Carbon\Carbon::parse($employee->retirement_date)
            : now();

        $dateOfFirstAppointment = \Carbon\Carbon::parse($employee->date_of_first_appointment);

        // If retirement date is before appointment, return 0
        if ($retirementDate->lessThanOrEqualTo($dateOfFirstAppointment)) {
            return 0;
        }

        // For Nigerian CPS: Gratuity = 100% of last gross annual emoluments
        // Gross emoluments = Basic salary + all allowances (housing, transport, etc.)
        $grossMonthlyEmoluments = $lastPayroll->basic_salary + $lastPayroll->total_additions;
        $grossAnnualEmoluments = $grossMonthlyEmoluments * 12; // Annual calculation

        // Nigerian CPS gratuity formula: 100% of last gross annual emoluments
        $gratuity = $grossAnnualEmoluments;

        return round($gratuity, 2);
    }

    /**
     * Redirect to pension computation form with employee data pre-filled
     */
    public function redirectToPensionComputation($employeeId)
    {
        try {
            \Log::info('Redirecting to pension computation for employee ID: ' . $employeeId);

            $employee = Employee::with(['bank', 'nextOfKin', 'gradeLevel', 'gradeLevel.salaryScale', 'department', 'rank', 'step'])->where('employee_id', $employeeId)->first();

            if (!$employee) {
                \Log::error('Employee not found for ID: ' . $employeeId);
                return redirect()->route('retirements.create')->with('error', 'Employee not found.');
            }

            \Log::info('Found employee: ' . $employee->first_name . ' ' . $employee->surname);

            // Try to get the corresponding bank from bank_list table
            $bankId = null;
            if ($employee->bank) {
                \Log::info('Employee has bank: ' . $employee->bank->bank_name ?? 'Unknown');

                // First try to look up the bank in bank_list table by bank code
                $bankList = DB::table('bank_list')
                    ->where('bank_code', $employee->bank->bank_code ?? $employee->bank->id)
                    ->first();

                \Log::info('Bank lookup by code result: ' . ($bankList ? 'Found' : 'Not found'));

                // If not found by code, try by name if available
                if (!$bankList && isset($employee->bank->bank_name)) {
                    $bankList = DB::table('bank_list')
                        ->where(function($query) use ($employee) {
                            $query->where('bank_name', 'LIKE', '%' . $employee->bank->bank_name . '%')
                                  ->orWhere('bank_name', 'LIKE', '%' . str_replace(' ', '', $employee->bank->bank_name ?? '') . '%');
                        })
                        ->first();

                    \Log::info('Bank lookup by name result: ' . ($bankList ? 'Found' : 'Not found'));
                }

                if ($bankList) {
                    $bankId = $bankList->id;
                }
            }

            // Build query parameters for the pension computation form
            $params = [
                'employee_id' => $employee->employee_id,
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
                'retire_date' => now()->toDateString(), // Default to current date
            ];

            \Log::info('Redirecting to pension computation with params: ' . json_encode($params));

            return redirect()->route('pension.create', $params);
        } catch (\Exception $e) {
            \Log::error('Error in redirectToPensionComputation: ' . $e->getMessage());
            return redirect()->route('retirements.create')->with('error', 'Error processing employee data: ' . $e->getMessage());
        }
    }

    /**
     * Move retired employee to pensioners table
     */
    private function moveToPensioners(Employee $employee, $retirement)
    {
        // Check if pensioner already exists
        if (\App\Models\Pensioner::where('employee_id', $employee->employee_id)->exists()) {
            return; // Already exists, don't duplicate
        }

        // Get the beneficiary computation record if it exists
        $beneficiaryComputation = \App\Models\ComputeBeneficiary::where('id_no', $employee->employee_id)
            ->orWhere('id_no', $employee->staff_id ?? $employee->employee_id)
            ->first();

        // Calculate years of service
        $dateOfFirstAppointment = \Carbon\Carbon::parse($employee->date_of_first_appointment);
        $retirementDate = \Carbon\Carbon::parse($retirement->retirement_date);
        $yearsOfService = $dateOfFirstAppointment->diffInYears($retirementDate);

        // Determine pension and gratuity amounts from computation if available
        $pensionAmount = $beneficiaryComputation ? $beneficiaryComputation->pension_per_mnth : $retirement->gratuity_amount;
        $gratuityAmount = $beneficiaryComputation ? $beneficiaryComputation->gratuity_amt : $retirement->gratuity_amount;
        $totalDeathGratuity = $beneficiaryComputation ? $beneficiaryComputation->total_death_gratuity : $retirement->gratuity_amount;
        $retirementType = $beneficiaryComputation ? $beneficiaryComputation->gtype : 'RB'; // RB (Retirement Benefits) or DG (Death Gratuity)

        // Create pensioner record
        \App\Models\Pensioner::create([
            'employee_id' => $employee->employee_id,
            'full_name' => $employee->full_name,
            'surname' => $employee->surname,
            'first_name' => $employee->first_name,
            'middle_name' => $employee->middle_name,
            'email' => $employee->email,
            'phone_number' => $employee->phone,
            'date_of_birth' => $employee->date_of_birth,
            'place_of_birth' => $employee->place_of_birth,
            'date_of_first_appointment' => $employee->date_of_first_appointment,
            'date_of_retirement' => $retirement->retirement_date,
            'retirement_reason' => $retirement->retire_reason,
            'retirement_type' => $retirementType,
            'department_id' => $employee->department_id,
            'rank_id' => $employee->rank_id,
            'step_id' => $employee->step_id,
            'grade_level_id' => $employee->grade_level_id,
            'salary_scale_id' => $employee->salary_scale_id,
            'local_gov_area_id' => $employee->lga_id,
            'bank_id' => $employee->bank_id,
            'account_number' => $employee->account_number,
            'account_name' => $employee->account_name,
            'pension_amount' => $pensionAmount,
            'gratuity_amount' => $gratuityAmount,
            'total_death_gratuity' => $totalDeathGratuity,
            'years_of_service' => $yearsOfService,
            'pension_percentage' => $beneficiaryComputation ? $beneficiaryComputation->pct_pension : 0,
            'gratuity_percentage' => $beneficiaryComputation ? $beneficiaryComputation->pct_gratuity : 0,
            'address' => $employee->address,
            'next_of_kin_name' => $employee->next_of_kin_name,
            'next_of_kin_phone' => $employee->next_of_kin_phone,
            'next_of_kin_address' => $employee->next_of_kin_address,
            'status' => 'Active',
            'retirement_id' => $retirement->id,
            'beneficiary_computation_id' => $beneficiaryComputation ? $beneficiaryComputation->id : null,
            'created_by' => auth()->id() ?? 1, // Use 1 as default if no authenticated user
        ]);
    }


}