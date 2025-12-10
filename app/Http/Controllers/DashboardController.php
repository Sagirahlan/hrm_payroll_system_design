<?php
namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\AuditTrail;
use App\Models\Models\Leave;
use App\Models\Addition;
use App\Models\Deduction;
use App\Models\PayrollRecord;
use App\Models\PromotionHistory;
use App\Models\PendingEmployeeChange;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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

        // Payroll approval statistics
        $currentMonth = now()->format('Y-m');
        $payrollRecords = PayrollRecord::where('payroll_month', 'like', "$currentMonth%")->count();
        // Payroll records that are pending approval (in various stages of the approval workflow)
        $pendingPayrollApprovals = PayrollRecord::whereIn('status', ['Pending Review', 'Under Review', 'Reviewed', 'Pending Final Approval'])->count();
        $approvedPayrollRecords = PayrollRecord::where('status', 'Approved')->count();

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

        // Approval-related statistics (for dashboard)
        // Note: Addition and Deduction models don't have approval_status, so we'll have to check if these fields exist
        $pendingAdditions = 0; // Default to 0 if no approval field exists
        $pendingDeductions = 0; // Default to 0 if no approval field exists

        $pendingPromotions = PromotionHistory::where('status', 'pending')->count();

        // For probation, check employees who are on probation (status = pending)
        $pendingProbations = Employee::where('on_probation', true)
                                    ->where('probation_status', 'pending')
                                    ->count();

        $pendingEmployeeChanges = PendingEmployeeChange::where('status', 'pending')->count();

        // Total pending approvals
        $totalPendingApprovals = $pendingAdditions + $pendingDeductions + $pendingPromotions +
                                 $pendingLeaveRequests + $pendingPayrollApprovals + $pendingProbations +
                                 $pendingEmployeeChanges + $pendingDisciplinaryActions;

        // Calculate employees retiring within 6 months
        $employeesRetiringWithin6Months = $this->getEmployeesRetiringWithin6Months();

        // Get employees eligible for retirement confirmation (pending retirement)
        $pendingRetirementConfirmations = $this->getPendingRetirementConfirmations();

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
            'gradeLevels',
            'pendingAdditions',
            'pendingDeductions',
            'pendingPromotions',
            'pendingPayrollApprovals',
            'approvedPayrollRecords',
            'pendingProbations',
            'pendingEmployeeChanges',
            'totalPendingApprovals',
            'employeesRetiringWithin6Months',
            'pendingRetirementConfirmations'
        ));
    }

    /**
     * Get employees who are retiring within 6 months
     */
    private function getEmployeesRetiringWithin6Months()
    {
        $employees = \App\Models\Employee::where('status', 'Active')
            ->with(['department', 'gradeLevel.salaryScale'])
            ->get();

        $sixMonthsFromNow = now()->addMonths(6);

        // Filter employees approaching retirement within 6 months using the same logic as the retirement page
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

        return $approachingRetirement;
    }

    /**
     * Get employees eligible for retirement confirmation (pending retirement)
     */
    private function getPendingRetirementConfirmations()
    {
        $employees = \App\Models\Employee::with(['department', 'gradeLevel.salaryScale', 'retirement'])
            ->where('status', 'Active')
            ->get();

        // Filter employees who are eligible for retirement but haven't been processed yet
        $pendingRetirements = $employees->filter(function ($employee) {
            if (!$employee->gradeLevel || !$employee->gradeLevel->salaryScale) {
                return false;
            }

            $retirementAge = (int) $employee->gradeLevel->salaryScale->max_retirement_age;
            $yearsOfService = (int) $employee->gradeLevel->salaryScale->max_years_of_service;

            // Check if employee has valid dates
            if (!$employee->date_of_birth || !$employee->date_of_first_appointment) {
                return false;
            }

            $age = Carbon::parse($employee->date_of_birth)->age;
            $serviceDuration = Carbon::parse($employee->date_of_first_appointment)->diffInYears(Carbon::now());

            // Check if retirement conditions are met (exact match - not close to being met)
            $isAgeEligible = $age >= $retirementAge;
            $isServiceEligible = $serviceDuration >= $yearsOfService;

            // Check if already has a retirement record
            $hasRetirementRecord = $employee->retirement ? true : false;

            // Only include employees who are eligible (have met either condition) but don't have a retirement record
            return ($isAgeEligible || $isServiceEligible) && !$hasRetirementRecord;
        });

        return $pendingRetirements;
    }
}