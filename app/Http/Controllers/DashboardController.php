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
        $holdEmployees = Employee::where('status', 'Hold')->count();

        // Statistics for contract and permanent employees
        $permanentEmployees = Employee::where('appointment_type_id', 1)->count();
        $contractEmployees = Employee::where('appointment_type_id', 2)->count();

        // Department statistics
        $totalDepartments = Department::count();

        // Leave statistics
        $totalLeaveRequests = Leave::count();
        $pendingLeaveRequests = Leave::where('status', 'pending')->count();
        $approvedLeaveRequests = Leave::where('status', 'approved')->count();
        $rejectedLeaveRequests = Leave::where('status', 'rejected')->count();

        // Payroll statistics (last month)
        $currentMonth = now()->format('Y-m');
        $payrollRecords = \App\Models\PayrollRecord::where('payroll_month', 'like', "$currentMonth%")->count();
        // Note: Removed totalPayrollAmount to exclude from dashboard

        // Disciplinary action statistics
        $openDisciplinaryActions = \App\Models\DisciplinaryAction::where('status', 'Open')->count();
        $resolvedDisciplinaryActions = \App\Models\DisciplinaryAction::where('status', 'Resolved')->count();
        $pendingDisciplinaryActions = \App\Models\DisciplinaryAction::where('status', 'Pending')->count();

        // Cadre statistics
        $cadreCount = \App\Models\Cadre::count();

        // Appointment type statistics
        $appointmentTypes = \App\Models\AppointmentType::withCount('employees')->get();

        // Recent audit trail (last 5 records)
        $recentAudits = AuditTrail::with('user')->latest()->take(5)->get();

        // Departments with employee counts
        $departments = Department::withCount('employees')->get();

        // Grade level distribution
        $gradeLevels = \App\Models\GradeLevel::withCount('employees')->get();

        // Do not include: maleEmployees, femaleEmployees, avgYearsOfService

        return view('dashboard', compact(
            'employeeCount',
            'activeEmployees',
            'suspendedEmployees',
            'terminatedEmployees',
            'deceasedEmployees',
            'retiredEmployees',
            'holdEmployees',
            'permanentEmployees',
            'contractEmployees',
            'recentAudits',
            'departments',
            'totalLeaveRequests',
            'pendingLeaveRequests',
            'approvedLeaveRequests',
            'rejectedLeaveRequests',
            'totalDepartments',
            'payrollRecords',
            'openDisciplinaryActions',
            'resolvedDisciplinaryActions',
            'pendingDisciplinaryActions',
            'cadreCount',
            'appointmentTypes',
            'gradeLevels'
        ));
    }
}