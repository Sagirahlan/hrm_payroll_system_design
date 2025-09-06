<?php

namespace App\Http\Controllers;

use App\Models\{Retirement, Pensioner, Employee, PayrollRecord, AuditTrail};
use Illuminate\Http\Request;
use Carbon\Carbon;

class RetirementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:manage_retirement']);
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
        // Get all employees with status "Retired" from the employees table
        $query = \App\Models\Employee::with(['gradeLevel', 'department', 'retirement'])
            ->where('status', 'Retired');

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

        return response()->json([
            'retired_records' => $retiredRecords,
            'distinct_statuses' => $distinctStatuses,
            'count' => $retiredRecords->count()
        ]);
    }

    public function create()
    {
        $employees = Employee::with(['gradeLevel.salaryScale', 'department'])
            ->where('status', 'Active')
            ->get();

        $eligibleEmployees = $employees->filter(function ($employee) {
            if (!$employee->gradeLevel || !$employee->gradeLevel->salaryScale) {
                return false;
            }

            $retirementAge = (int) $employee->gradeLevel->salaryScale->max_retirement_age;
            $yearsOfService = (int) $employee->gradeLevel->salaryScale->max_years_of_service;

            $age = \Carbon\Carbon::parse($employee->date_of_birth)->age;
            $serviceDuration = \Carbon\Carbon::parse($employee->date_of_first_appointment)->diffInYears(\Carbon\Carbon::now());

            return $age >= $retirementAge || $serviceDuration >= $yearsOfService;
        });

        return view('retirements.create', compact('eligibleEmployees'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,employee_id',
                'retirement_date' => 'required|date',
                'notification_date' => 'nullable|date',
                'gratuity_amount' => 'nullable|numeric|min:0',
                'status' => 'nullable|string|in:pending,complete',
            ]);

            $employee = Employee::where('employee_id', $validated['employee_id'])->firstOrFail();

            if (Retirement::where('employee_id', $employee->employee_id)->exists()) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Employee already processed for retirement.'
                    ], 400);
                }
                return redirect()->back()->with('error', 'Employee already processed for retirement.');
            }

            $retirement = Retirement::create([
                'employee_id' => $employee->employee_id,
                'retirement_date' => $validated['retirement_date'],
                'status' => 'Completed',
                'notification_date' => $validated['notification_date'] ?? now(),
                'gratuity_amount' => $validated['gratuity_amount'] ?? $this->calculateGratuity($employee),
            ]);

            $employee->update(['status' => 'Retired']);

            Pensioner::create([
                'employee_id' => $employee->employee_id,
                'pension_start_date' => $validated['retirement_date'],
                'pension_amount' => $this->calculatePension($employee),
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
            \Log::error('Retirement processing error: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while processing retirement.'
                ], 500);
            }

            return redirect()->back()->with('error', 'An error occurred while processing retirement.');
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

        // Calculate years of service (count partial years as full year)
        $yearsOfService = $dateOfFirstAppointment->diffInYears($retirementDate);
        if ($dateOfFirstAppointment->copy()->addYears($yearsOfService)->lt($retirementDate)) {
            $yearsOfService += 1;
        }

        if ($yearsOfService < 1) {
            return 0;
        }

        // Use gross_salary if available, otherwise fallback to basic_salary
        $salary = $lastPayroll->basic_salary;

        // Gratuity formula: 10% of last salary per year of service
        $gratuity = $salary * 0.1 * $yearsOfService;

        return round($gratuity, 2);
        
    }


    private function calculatePension(Employee $employee)
    {
        $lastPayroll = PayrollRecord::where('employee_id', $employee->employee_id)->latest()->first();
        return $lastPayroll ? ($lastPayroll->basic_salary * 0.5) : 0;
    }
}