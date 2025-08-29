<?php

namespace App\Http\Controllers;

use App\Models\{Retirement, Pensioner, Employee, PayrollRecord, AuditTrail};
use Illuminate\Http\Request;

class RetirementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:manage_retirement']);
    }

    public function index(Request $request)
    {
        // Automatically update employee status to 'Retired' if retirement date is today
        Employee::where('status', '!=', 'Retired')
            ->whereDate('expected_retirement_date', now()->toDateString())
            ->update(['status' => 'Retired']);

        $threeMonthsFromNow = now()->addMonths(3);
        $today = now();

        $query = Employee::query()
            ->where('status', '!=', 'Retired')
            ->whereBetween('expected_retirement_date', [$today, $threeMonthsFromNow])
            ->orderBy('expected_retirement_date', 'asc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('employee_id', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%");
            });
        }

        $retirements = $query->paginate(10);

        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'viewed',
            'description' => 'Viewed employees approaching retirement',
            'action_timestamp' => now(),
            'entity_type' => 'Retirement',
            'entity_id' => 0,
        ]);

        return view('retirements.index', compact('retirements'));
    }

    public function retiredList()
    {
        $retiredEmployees = Employee::with(['gradeLevel', 'payrollRecords'])
            ->where('status', 'Retired')
            ->orderBy('expected_retirement_date', 'desc')
            ->paginate(10);

        return view('retirements.index', compact('retiredEmployees'));
    }

    public function create(Request $request)
    {
        $query = Employee::with(['gradeLevel', 'payrollRecords'])
            ->where('status', 'Retired');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('reg_no', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('expected_retirement_date', [$request->input('date_from'), $request->input('date_to')]);
        }

        $retiredEmployees = $query->orderBy('expected_retirement_date', 'asc')->paginate(10);

        return view('retirements.create', compact('retiredEmployees'));
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
