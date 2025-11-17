<?php

namespace App\Http\Controllers;

use App\Models\Models\Leave;
use App\Models\Employee;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->can('view_leaves')) {
            // Admins/Managers can view all leaves
            $leaves = Leave::with(['employee' => function($query) {
                $query->with(['department', 'appointmentType']);
            }])->orderBy('created_at', 'desc')->get();
        } else {
            // Regular employees can only view their own leaves
            $employeeId = $user->employee->employee_id ?? null;
            if (!$employeeId) {
                abort(403, 'You do not have an associated employee record.');
            }
            $leaves = Leave::with(['employee' => function($query) {
                $query->with(['department', 'appointmentType']);
            }])->where('employee_id', $employeeId)->orderBy('created_at', 'desc')->get();
        }

        return view('leaves.index', compact('leaves'));
    }

    public function create()
    {
        $user = auth()->user();

        if ($user->can('manage_leaves')) {
            // Admins/Managers can create leaves for any employee
            $employees = Employee::all();
        } else {
            // Regular employees can only create for themselves
            $employeeId = $user->employee->employee_id ?? null;
            if (!$employeeId) {
                abort(403, 'You do not have an associated employee record.');
            }
            $employees = Employee::where('employee_id', $employeeId)->get();
        }

        return view('leaves.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'leave_type' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        // Check if user can create leaves for other employees
        if (auth()->user()->cannot('manage_leaves')) {
            // If user does not have manage_leaves permission, they can only create for themselves
            $employeeId = auth()->user()->employee->employee_id ?? null;
            if ($request->employee_id != $employeeId) {
                abort(403, 'You can only create leave requests for yourself.');
            }
        }

        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);
        $daysRequested = $startDate->diffInDays($endDate) + 1; // +1 to include both start and end dates

        $leave = Leave::create([
            'employee_id' => $request->employee_id,
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'days_requested' => $daysRequested,
            'reason' => $request->reason,
            'status' => 'pending', // Default status
        ]);

        // Log audit trail
        AuditTrail::create([
            'user_id' => Auth::id(),
            'role_id' => Auth::user()->role_id ?? null,
            'action' => 'leave_created_admin',
            'description' => 'Leave request created by admin for employee ID: ' . $request->employee_id,
            'log_data' => [
                'leave_id' => $leave->id,
                'employee_id' => $request->employee_id,
                'leave_type' => $request->leave_type,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'reason' => $request->reason,
                'status' => 'pending'
            ]
        ]);

        return redirect()->route('leaves.index')->with('success', 'Leave request submitted successfully.');
    }

    public function show(Leave $leave)
    {
        $user = auth()->user();

        // Check authorization - only allow viewing if user has view_leaves permission
        // or if the leave belongs to the current user and they only have create_leaves permission
        if ($user->cannot('view_leaves')) {
            $employeeId = $user->employee->employee_id ?? null;
            if ($leave->employee_id != $employeeId) {
                abort(403, 'You can only view your own leave requests.');
            }
        }

        // Load the employee relationship with department and appointment type to avoid N+1 queries
        $leave->load(['employee' => function($query) {
            $query->with(['department', 'appointmentType']);
        }]);

        // Log audit trail for viewing
        AuditTrail::create([
            'user_id' => Auth::id(),
            'role_id' => Auth::user()->role_id ?? null,
            'action' => 'leave_viewed',
            'description' => 'Leave request viewed by user',
            'log_data' => [
                'leave_id' => $leave->id,
                'employee_id' => $leave->employee_id,
            ]
        ]);

        return view('leaves.show', compact('leave'));
    }

    public function edit(Leave $leave)
    {
        $user = auth()->user();

        // Only users with manage_leaves permission can edit leaves
        if ($user->cannot('manage_leaves')) {
            abort(403, 'You do not have permission to edit leave requests.');
        }

        // Load the employee relationship with department and appointment type to avoid N+1 queries
        $leave->load(['employee' => function($query) {
            $query->with(['department', 'appointmentType']);
        }]);

        // Admins/Managers can edit leaves for any employee
        $employees = Employee::all();

        return view('leaves.edit', compact('leave', 'employees'));
    }

    public function update(Request $request, Leave $leave)
    {
        $user = auth()->user();

        // Only users with manage_leaves permission can update leaves
        if ($user->cannot('manage_leaves')) {
            abort(403, 'You do not have permission to update leave requests.');
        }

        // For managers/admins, allow full updates
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'leave_type' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);
        $daysRequested = $startDate->diffInDays($endDate) + 1; // +1 to include both start and end dates

        // Store old values for audit trail
        $oldData = [
            'employee_id' => $leave->employee_id,
            'leave_type' => $leave->leave_type,
            'start_date' => $leave->start_date,
            'end_date' => $leave->end_date,
            'days_requested' => $leave->days_requested,
            'reason' => $leave->reason,
            'status' => $leave->status,
        ];

        $leave->update([
            'employee_id' => $request->employee_id,
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'days_requested' => $daysRequested,
            'reason' => $request->reason,
            'status' => $request->status,
        ]);

        // Log audit trail
        AuditTrail::create([
            'user_id' => Auth::id(),
            'role_id' => Auth::user()->role_id ?? null,
            'action' => 'leave_updated',
            'description' => 'Leave request updated by admin',
            'log_data' => [
                'leave_id' => $leave->id,
                'old_data' => $oldData,
                'new_data' => [
                    'employee_id' => $request->employee_id,
                    'leave_type' => $request->leave_type,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'days_requested' => $daysRequested,
                    'reason' => $request->reason,
                    'status' => $request->status,
                ]
            ]
        ]);

        return redirect()->route('leaves.index')->with('success', 'Leave request updated successfully.');
    }

    public function destroy(Leave $leave)
    {
        $user = auth()->user();

        // Only users with manage_leaves permission can delete leaves
        if ($user->cannot('manage_leaves')) {
            abort(403, 'You do not have permission to delete leave requests.');
        }

        // Store data for audit trail before deletion
        $leaveData = [
            'leave_id' => $leave->id,
            'employee_id' => $leave->employee_id,
            'leave_type' => $leave->leave_type,
            'start_date' => $leave->start_date,
            'end_date' => $leave->end_date,
            'status' => $leave->status,
            'reason' => $leave->reason,
        ];

        $leave->delete();

        // Log audit trail
        AuditTrail::create([
            'user_id' => Auth::id(),
            'role_id' => Auth::user()->role_id ?? null,
            'action' => 'leave_deleted',
            'description' => 'Leave request deleted by admin',
            'log_data' => $leaveData
        ]);

        return redirect()->route('leaves.index')->with('success', 'Leave request deleted successfully.');
    }

    // Approve or reject leave
    public function approve(Request $request, Leave $leave)
    {
        $user = auth()->user();

        // Only users with approve_leaves permission can approve/reject leaves
        if ($user->cannot('approve_leaves')) {
            abort(403, 'You do not have permission to approve or reject leave requests.');
        }

        $request->validate([
            'status' => 'required|in:approved,rejected',
            'approval_remarks' => 'nullable|string',
        ]);

        // Store old status for audit trail
        $oldStatus = $leave->status;

        $leave->update([
            'status' => $request->status,
            'approval_remarks' => $request->approval_remarks,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        // Log audit trail
        AuditTrail::create([
            'user_id' => Auth::id(),
            'role_id' => Auth::user()->role_id ?? null,
            'action' => 'leave_' . $request->status,
            'description' => 'Leave request ' . $request->status . ' by admin',
            'log_data' => [
                'leave_id' => $leave->id,
                'employee_id' => $leave->employee_id,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'approval_remarks' => $request->approval_remarks,
                'approved_by' => Auth::id(),
            ]
        ]);

        $status = $request->status === 'approved' ? 'approved' : 'rejected';
        return redirect()->back()->with('success', "Leave request {$status} successfully.");
    }

    // Display leave requests for the current user
    public function myLeaves()
    {
        $employeeId = auth()->user()->employee->employee_id ?? null;
        if (!$employeeId) {
            abort(403, 'You do not have an associated employee record.');
        }

        $leaves = Leave::with(['employee' => function($query) {
            $query->with(['department', 'appointmentType']);
        }])->where('employee_id', $employeeId)->orderBy('created_at', 'desc')->get();
        return view('leaves.my-leaves', compact('leaves'));
    }

    // Show form to create a new leave request for the current user
    public function createMyLeave()
    {
        $employeeId = auth()->user()->employee->employee_id ?? null;
        if (!$employeeId) {
            abort(403, 'You do not have an associated employee record.');
        }

        return view('leaves.create-my-leave');
    }

    // Store a new leave request for the current user
    public function storeMyLeave(Request $request)
    {
        $request->validate([
            'leave_type' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        $employeeId = auth()->user()->employee->employee_id ?? null;
        if (!$employeeId) {
            abort(403, 'You do not have an associated employee record.');
        }

        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);
        $daysRequested = $startDate->diffInDays($endDate) + 1; // +1 to include both start and end dates

        $leave = Leave::create([
            'employee_id' => $employeeId,
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'days_requested' => $daysRequested,
            'reason' => $request->reason,
            'status' => 'pending', // Default status
        ]);

        // Log audit trail
        AuditTrail::create([
            'user_id' => Auth::id(),
            'role_id' => Auth::user()->role_id ?? null,
            'action' => 'leave_created',
            'description' => 'Leave request created by employee ' . Auth::user()->username,
            'log_data' => [
                'leave_id' => $leave->id,
                'employee_id' => $employeeId,
                'leave_type' => $request->leave_type,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => 'pending'
            ]
        ]);

        return redirect()->route('leaves.my')->with('success', 'Leave request submitted successfully.');
    }
}