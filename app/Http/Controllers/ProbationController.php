<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AuditTrail;
use App\Services\ProbationService;

class ProbationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(['permission:view_probation'], ['only' => ['index', 'show']]);
        $this->middleware(['permission:manage_probation'], ['only' => ['startProbation', 'extend']]);
        $this->middleware(['permission:approve_probation'], ['only' => ['approve', 'evaluate']]);
        $this->middleware(['permission:reject_probation'], ['only' => ['reject']]);
    }

    /**
     * Display a listing of employees on probation.
     */
    public function index(Request $request)
    {
        $query = Employee::with(['department', 'cadre', 'gradeLevel'])
            ->where('on_probation', true);

        // Filter by probation status
        if ($request->filled('probation_status')) {
            $query->where('probation_status', $request->probation_status);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('employee_id', 'like', "%{$search}%")
                  ->orWhere('staff_no', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile_no', 'like', "%{$search}%")
                  ->orWhere(DB::raw("CONCAT_WS(' ', first_name, middle_name, surname)"), 'like', "%{$search}%")
                  ->orWhere(DB::raw("CONCAT_WS(' ', first_name, surname)"), 'like', "%{$search}%");
            });
        }

        // Department filter
        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'asc');

        $allowedSorts = ['first_name', 'surname', 'employee_id', 'probation_start_date', 'probation_end_date', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = $request->get('per_page', 10);
        $employees = $query->paginate($perPage)->withQueryString();

        // Get filter options
        $departments = \App\Models\Department::orderBy('department_name')->get();

        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'viewed',
            'description' => 'Viewed probation employees list with filters: ' . json_encode($request->only(['search', 'department', 'probation_status'])),
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => null, 'filters' => $request->only(['search', 'department', 'probation_status'])]),
        ]);

        return view('probation.index', compact('employees', 'departments'));
    }

    /**
     * Show a specific probation employee
     */
    public function show(Employee $employee)
    {
        if (!$employee->on_probation) {
            return redirect()->route('employees.index')
                ->with('error', 'Employee is not on probation.');
        }

        $employee->load(['department', 'cadre', 'gradeLevel', 'nextOfKin', 'biometricData', 'bank', 'state', 'lga', 'ward', 'rank']);

        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'viewed',
            'description' => "Viewed probation employee: {$employee->first_name} {$employee->surname}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => $employee->employee_id]),
        ]);

        return view('probation.show', compact('employee'));
    }

    /**
     * Put an employee on probation
     */
    public function startProbation(Request $request, Employee $employee)
    {
        $request->validate([
            'probation_start_date' => 'required|date',
            'probation_notes' => 'nullable|string',
        ]);

        // Set probation to start immediately if no start date provided in the future
        $probationStartDate = Carbon::parse($request->probation_start_date);
        $probationEndDate = $probationStartDate->copy()->addMonths(3);

        $employee->update([
            'on_probation' => true,
            'probation_start_date' => $probationStartDate,
            'probation_end_date' => $probationEndDate,
            'probation_status' => 'pending',
            'probation_notes' => $request->probation_notes,
            'status' => 'On Probation' // Update employee status to reflect probation
        ]);

        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'updated',
            'description' => "Started probation for employee: {$employee->first_name} {$employee->surname}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => $employee->employee_id, 'changes' => [
                'on_probation' => true,
                'probation_start_date' => $probationStartDate,
                'probation_end_date' => $probationEndDate,
                'probation_status' => 'pending'
            ]]),
        ]);

        return redirect()->back()->with('success', 'Employee placed on probation successfully.');
    }

    /**
     * Approve an employee's probation
     */
    public function approve(Request $request, Employee $employee)
    {
        if (!$employee->on_probation) {
            return redirect()->route('employees.index')
                ->with('error', 'Employee is not on probation.');
        }

        if (!$employee->canBeEvaluatedForProbation()) {
            $remainingDays = $employee->getRemainingProbationDays();
            return redirect()->back()
                ->with('error', "Employee cannot be evaluated for probation yet. The 3-month probation period has not ended. {$remainingDays} days remaining.");
        }

        $employee->update([
            'on_probation' => false,
            'probation_status' => 'approved',
            'status' => 'Active', // Set back to active status
            'probation_notes' => $request->probation_notes ? $employee->probation_notes . "\n" . 'Approval Note: ' . $request->probation_notes : $employee->probation_notes
        ]);

        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'updated',
            'description' => "Approved probation for employee: {$employee->first_name} {$employee->surname}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => $employee->employee_id, 'changes' => [
                'on_probation' => false,
                'probation_status' => 'approved',
                'status' => 'Active'
            ]]),
        ]);

        return redirect()->route('probation.index')->with('success', 'Employee probation approved successfully.');
    }

    /**
     * Reject an employee's probation
     */
    public function reject(Request $request, Employee $employee)
    {
        if (!$employee->on_probation) {
            return redirect()->route('employees.index')
                ->with('error', 'Employee is not on probation.');
        }

        // Only allow rejection after probation period has ended - REMOVED per user request
        // if (!$employee->hasProbationPeriodEnded()) {
        //     $remainingDays = $employee->getRemainingProbationDays();
        //     return redirect()->back()
        //         ->with('error', "Employee cannot be rejected before the 3-month probation period ends. {$remainingDays} days remaining.");
        // }

        $request->validate([
            'probation_notes' => 'required|string|max:1000',
        ], [
            'probation_notes.required' => 'A reason for rejection is required.',
        ]);

        $employee->update([
            'on_probation' => false,
            'probation_status' => 'rejected',
            'status' => 'Terminated', // Set status to terminated
            'probation_notes' => $request->probation_notes ? $employee->probation_notes . "\n" . 'Rejection Note: ' . $request->probation_notes : $employee->probation_notes
        ]);

        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'updated',
            'description' => "Rejected probation for employee: {$employee->first_name} {$employee->surname}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => $employee->employee_id, 'changes' => [
                'on_probation' => false,
                'probation_status' => 'rejected',
                'status' => 'Terminated'
            ]]),
        ]);

        return redirect()->route('probation.index')->with('success', 'Employee probation rejected successfully.');
    }

    /**
     * Evaluate employee after probation period ends
     */
    public function evaluate(Request $request, Employee $employee)
    {
        // Validate the employee is on probation
        if (!$employee->on_probation || $employee->probation_status !== 'pending') {
            return redirect()->back()->with('error', 'Employee is not on probation or probation is already processed.');
        }

        // Check if 3 months have passed since probation start date
        $probationStartDate = Carbon::parse($employee->probation_start_date);
        $threeMonthsLater = $probationStartDate->copy()->addMonths(3);
        $today = Carbon::now();

        if ($today->lt($threeMonthsLater)) {
            $remainingDays = $today->diffInDays($threeMonthsLater);
            return redirect()->back()->with('error',
                "Cannot evaluate employee yet. Probation period is still ongoing. {$remainingDays} days remaining."
            );
        }

        // Validate request
        $request->validate([
            'decision' => 'required|in:approve,reject',
            'notes' => 'nullable|string|max:1000',
            'reason' => 'nullable|string|required_if:decision,reject|max:500'
        ]);

        if ($request->decision === 'approve') {
            // Approve the employee's probation
            $employee->update([
                'on_probation' => false,
                'probation_status' => 'approved',
                'status' => 'Active', // Change status back to active
                'probation_notes' => $employee->probation_notes . "\n" .
                    "Probation approved on " . now()->format('Y-m-d H:i:s') .
                    ". Decision: Approved. Notes: " . ($request->notes ?: 'No notes provided')
            ]);

            AuditTrail::create([
                'user_id' => auth()->id(),
                'action' => 'probation_approved',
                'description' => "Approved probation for employee: {$employee->first_name} {$employee->surname}",
                'action_timestamp' => now(),
                'log_data' => json_encode([
                    'entity_type' => 'Employee',
                    'entity_id' => $employee->employee_id,
                    'changes' => [
                        'on_probation' => false,
                        'probation_status' => 'approved',
                        'status' => 'Active'
                    ],
                    'notes' => $request->notes
                ]),
            ]);

            return redirect()->route('probation.index')
                ->with('success', "Employee {$employee->first_name} {$employee->surname} probation approved successfully.");

        } else {
            // Reject the employee's probation
            $employee->update([
                'on_probation' => false,
                'probation_status' => 'rejected',
                'status' => 'Terminated', // Mark as terminated
                'probation_notes' => $employee->probation_notes . "\n" .
                    "Probation rejected on " . now()->format('Y-m-d H:i:s') .
                    ". Decision: Rejected. Reason: " . ($request->reason ?: 'No reason provided')
            ]);

            AuditTrail::create([
                'user_id' => auth()->id(),
                'action' => 'probation_rejected',
                'description' => "Rejected probation for employee: {$employee->first_name} {$employee->surname}. Reason: " . ($request->reason ?: 'No reason provided'),
                'action_timestamp' => now(),
                'log_data' => json_encode([
                    'entity_type' => 'Employee',
                    'entity_id' => $employee->employee_id,
                    'changes' => [
                        'on_probation' => false,
                        'probation_status' => 'rejected',
                        'status' => 'Terminated'
                    ],
                    'reason' => $request->reason
                ]),
            ]);

            return redirect()->route('probation.index')
                ->with('success', "Employee {$employee->first_name} {$employee->surname} probation rejected. Employee terminated.");
        }
    }

    /**
     * Extend probation period if needed
     */
    public function extend(Request $request, Employee $employee)
    {
        if (!$employee->on_probation) {
            return redirect()->route('employees.index')
                ->with('error', 'Employee is not on probation.');
        }

        $request->validate([
            'extension_months' => 'required|integer|min:1|max:6',
            'extension_reason' => 'required|string'
        ]);

        // Extend the probation end date
        $extendedEndDate = Carbon::parse($employee->probation_end_date)->addMonths($request->extension_months);

        $employee->update([
            'probation_end_date' => $extendedEndDate,
            'probation_notes' => $employee->probation_notes . "\n" . 'Extension Note: ' . $request->extension_reason . ' - Extended by ' . $request->extension_months . ' month(s)'
        ]);

        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'updated',
            'description' => "Extended probation for employee: {$employee->first_name} {$employee->surname} by {$request->extension_months} month(s)",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => $employee->employee_id, 'changes' => [
                'probation_end_date' => $extendedEndDate
            ]]),
        ]);

        return redirect()->back()->with('success', 'Probation extended successfully.');
    }
}
