<?php
namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\AuditTrail;
use App\Models\Models\Leave;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $employeeCount = Employee::count();
        $activeEmployees = Employee::where('status', 'Active')->count();
        $suspendedEmployees = Employee::where('status', 'Suspended')->count();
        $terminatedEmployees = Employee::where('status', 'Terminated')->count();
        $deceasedEmployees = Employee::where('status', 'Deceased')->count();
        $retiredEmployees = Employee::where('status', 'Retired')->count();

        // Statistics for contract and permanent employees
        $permanentEmployees = Employee::where('appointment_type_id', 1)->count();
        $contractEmployees = Employee::where('appointment_type_id', 2)->count();

        // Leave statistics
        $totalLeaveRequests = Leave::count();
        $pendingLeaveRequests = Leave::where('status', 'pending')->count();
        $approvedLeaveRequests = Leave::where('status', 'approved')->count();
        $rejectedLeaveRequests = Leave::where('status', 'rejected')->count();

        $recentAudits = AuditTrail::with('user')->latest()->take(5)->get();
        $departments = Department::withCount('employees')->get();

        // Ensure terminatedEmployees is included in compact
        return view('dashboard', compact(
            'employeeCount',
            'activeEmployees',
            'suspendedEmployees',
            'terminatedEmployees',
            'deceasedEmployees',
            'retiredEmployees',
            'permanentEmployees',
            'contractEmployees',
            'recentAudits',
            'departments',
            'totalLeaveRequests',
            'pendingLeaveRequests',
            'approvedLeaveRequests',
            'rejectedLeaveRequests'
        ));
    }
}