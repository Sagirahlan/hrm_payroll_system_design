<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\AuditTrail;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExampleProbationEvaluationController extends Controller
{
    /**
     * Evaluate probation after 3 months
     */
    public function evaluateProbation(Request $request, Employee $employee)
    {
        // Validate that the employee is on probation
        if (!$employee->on_probation || $employee->probation_status !== 'pending') {
            return redirect()->back()->with('error', 'Employee is not on probation or probation is already finalized.');
        }

        // Check if 3 months have passed since probation start date
        $probationStartDate = Carbon::parse($employee->probation_start_date);
        $threeMonthsLater = $probationStartDate->copy()->addMonths(3);
        $today = Carbon::now();

        if ($today->lt($threeMonthsLater)) {
            return redirect()->back()->with('error', 
                'Employee cannot be evaluated yet. Probation period is still ongoing. ' .
                'Remaining days: ' . $today->diffInDays($threeMonthsLater)
            );
        }

        // Validate request
        $request->validate([
            'evaluation_decision' => 'required|in:approve,reject',
            'evaluation_notes' => 'nullable|string|max:1000',
            'reason' => 'nullable|string|max:500'
        ]);

        // Process the evaluation
        if ($request->evaluation_decision === 'approve') {
            // Approve the employee's probation
            $employee->update([
                'on_probation' => false,
                'probation_status' => 'approved',
                'status' => 'Active', // Change status back to active
                'probation_notes' => $employee->probation_notes . "\n" . 
                    "Probation approved on " . now()->format('Y-m-d H:i:s') . 
                    ". Decision: Approved. Notes: " . ($request->evaluation_notes ?: 'No notes')
            ]);

            // Log the approval
            AuditTrail::create([
                'user_id' => auth()->id(),
                'action' => 'probation_approved',
                'description' => "Approved probation for employee: {$employee->first_name} {$employee->surname}",
                'action_timestamp' => now(),
                'log_data' => json_encode([
                    'entity_type' => 'Employee', 
                    'entity_id' => $employee->employee_id,
                    'previous_status' => 'On Probation',
                    'new_status' => 'Active'
                ]),
            ]);

            return redirect()->route('employees.index')
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

            // Log the rejection
            AuditTrail::create([
                'user_id' => auth()->id(),
                'action' => 'probation_rejected',
                'description' => "Rejected probation for employee: {$employee->first_name} {$employee->surname}. Reason: " . ($request->reason ?: 'No reason provided'),
                'action_timestamp' => now(),
                'log_data' => json_encode([
                    'entity_type' => 'Employee', 
                    'entity_id' => $employee->employee_id,
                    'previous_status' => 'On Probation',
                    'new_status' => 'Terminated'
                ]),
            ]);

            return redirect()->route('probation.index')
                ->with('success', "Employee {$employee->first_name} {$employee->surname} probation rejected. Employee terminated.");
        }
    }

    /**
     * Show employees whose probation period has ended and need evaluation
     */
    public function pendingEvaluations()
    {
        $probationEmployees = Employee::where('on_probation', true)
            ->where('probation_status', 'pending')
            ->whereDate('probation_end_date', '<=', Carbon::now())
            ->with(['department', 'appointmentType'])
            ->get();

        return view('probation.pending-evaluations', compact('probationEmployees'));
    }

    /**
     * Extend probation if needed (special cases)
     */
    public function extendProbation(Request $request, Employee $employee)
    {
        if (!$employee->on_probation || $employee->probation_status !== 'pending') {
            return redirect()->back()->with('error', 'Employee is not on probation.');
        }

        $request->validate([
            'extension_months' => 'required|integer|min:1|max:3',
            'extension_reason' => 'required|string|max:500'
        ]);

        // Extend the probation period
        $newProbationEndDate = Carbon::parse($employee->probation_end_date)
            ->addMonths($request->extension_months);

        $employee->update([
            'probation_end_date' => $newProbationEndDate,
            'probation_notes' => $employee->probation_notes . "\n" . 
                "Probation extended by {$request->extension_months} months on " . now()->format('Y-m-d H:i:s') . 
                ". Reason: {$request->extension_reason}"
        ]);

        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'probation_extended',
            'description' => "Extended probation for employee: {$employee->first_name} {$employee->surname} by {$request->extension_months} months",
            'action_timestamp' => now(),
            'log_data' => json_encode([
                'entity_type' => 'Employee', 
                'entity_id' => $employee->employee_id,
                'extension_months' => $request->extension_months,
                'reason' => $request->extension_reason
            ]),
        ]);

        return redirect()->back()->with('success', 
            "Probation extended for {$request->extension_months} months.");
    }
}