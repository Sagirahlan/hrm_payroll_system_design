<?php

namespace App\Http\Controllers;

use App\Models\{Retirement, Pensioner, Employee, PayrollRecord, AuditTrail};
use Illuminate\Http\Request;
use Carbon\Carbon;

class RetirementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(['permission:view_retirements'], ['only' => ['index', 'retiredList', 'getAllRetiredStatuses']]);
        $this->middleware(['permission:create_retirements'], ['only' => ['create', 'store']]);
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
            ->where('status', 'Active')
            ->get();

        $eligibleEmployees = $employees->filter(function ($employee) {
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
            if (!$retireReason && $employee->gradeLevel && $employee->gradeLevel->salaryScale) {
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

            $employee->update(['status' => 'Retired']);

            // Calculate pension details based on RSA
            $pensionDetails = $this->calculatePensionDetails($employee);
            
            Pensioner::create([
                'employee_id' => $employee->employee_id,
                'pension_start_date' => $validated['retirement_date'],
                'pension_amount' => $pensionDetails['monthly_pension'],
                'rsa_balance_at_retirement' => $pensionDetails['rsa_balance'],
                'lump_sum_amount' => $pensionDetails['lump_sum'],
                'pension_type' => $pensionDetails['pension_type'],
                'expected_lifespan_months' => $pensionDetails['expected_lifespan_months'],
                'status' => 'Active',
            ]);

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
                    'message' => 'Retirement processed successfully.',
                    'data' => [
                        'employee_id' => $employee->employee_id,
                        'retirement_id' => $retirement->id,
                        'retire_reason' => $retireReason,
                        'gratuity_amount' => $retirement->gratuity_amount,
                        'pension_amount' => $this->calculatePension($employee)
                    ]
                ]);
            }

            return redirect()->route('retirements.create')->with([
                'success' => 'Retirement processed successfully.',
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


    private function calculatePension(Employee $employee)
    {
        $lastPayroll = PayrollRecord::where('employee_id', $employee->employee_id)->latest()->first();
        return $lastPayroll ? ($lastPayroll->basic_salary * 0.5) : 0;
    }
    
    private function calculatePensionDetails(Employee $employee)
    {
        // Get the last payroll record for the employee (by created_at, fallback to payroll_date)
        $lastPayroll = \App\Models\PayrollRecord::where('employee_id', $employee->employee_id)
            ->orderByDesc('created_at')
            ->first();

        if (!$lastPayroll || !$employee->rsa_balance) {
            return [
                'monthly_pension' => 0,
                'rsa_balance' => 0,
                'lump_sum' => 0,
                'pension_type' => 'PW',
                'expected_lifespan_months' => 240
            ];
        }

        $rsaBalance = $employee->rsa_balance;
        
        // Calculate lump sum (up to 25% of RSA balance)
        $lumpSum = min($rsaBalance * 0.25, $rsaBalance); // At most 25% of the balance or the whole balance if less than 25%
        
        // Calculate remaining RSA balance after lump sum for monthly payments
        $remainingRsaBalance = $rsaBalance - $lumpSum;
        
        // Default to Programmed Withdrawal (PW) method
        $pensionType = 'PW'; // Could be 'PW' for Programmed Withdrawal or 'Annuity'
        
        // Expected lifespan: default to 20 years (240 months) for PW calculation
        $expectedLifespanMonths = 240;
        
        // Monthly pension calculation based on Programmed Withdrawal method
        $monthlyPension = $remainingRsaBalance / $expectedLifespanMonths;
        
        // Ensure minimum pension guarantee as per Nigerian CPS (₦32,000/month as of Sept 2025)
        $minimumPension = 32000;
        $monthlyPension = max($monthlyPension, $minimumPension);
        
        return [
            'monthly_pension' => $monthlyPension,
            'rsa_balance' => $rsaBalance,
            'lump_sum' => $lumpSum,
            'pension_type' => $pensionType,
            'expected_lifespan_months' => $expectedLifespanMonths
        ];
    }
}