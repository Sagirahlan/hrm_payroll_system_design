<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\PayrollRecord;
use App\Models\Deduction;
use App\Models\Addition;
use App\Models\DisciplinaryAction;
use App\Models\PromotionHistory;
use App\Models\Loan;
use App\Models\Retirement;
use Carbon\Carbon;

class ComprehensiveReportService
{
    public function generateEmployeeMasterReport($employeeId = null, $filters = [])
    {
        $query = Employee::with(['department', 'cadre', 'gradeLevel', 'step', 'bank', 'disciplinaryRecords', 'payrollRecords', 'deductions', 'additions', 'loans', 'promotionHistory', 'retirement', 'appointmentType']);

        // Apply appointment type filter if provided
        if (!empty($filters['appointment_type_id'])) {
            $query->where('appointment_type_id', $filters['appointment_type_id']);
        }

        // Apply status filter if provided
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $employees = $employeeId
            ? $query->where('employee_id', $employeeId)->get()
            : $query->get();

        $reportData = [
            'report_title' => 'Employee Master Report',
            'generated_date' => now()->format('Y-m-d H:i:s'),
            'total_employees' => $employees->count(),
            'employees' => []
        ];

        foreach ($employees as $employee) {
            $reportData['employees'][] = $this->formatEmployeeMasterData($employee);
        }

        return $reportData;
    }

    private function formatEmployeeMasterData($employee)
    {
        return [
            'employee_id' => $employee->employee_id,
            'full_name' => trim($employee->first_name . ' ' . $employee->middle_name . ' ' . $employee->surname),
            'department' => $employee->department->department_name ?? 'N/A',
            'cadre' => $employee->cadre->name ?? 'N/A',
            'grade_level' => $employee->gradeLevel->name ?? 'N/A',
            'step' => $employee->step->name ?? 'N/A',
            'status' => $employee->status,
            'appointment_type' => $employee->appointmentType->name ?? 'N/A',
            'date_of_first_appointment' => $employee->date_of_first_appointment,
            'years_of_service' => $employee->getYearsOfServiceAttribute(),
            'basic_salary' => $employee->isContractEmployee() ?
                ($employee->amount ?? $employee->basic_salary ?? 0) :
                ($employee->basic_salary ?? $employee->amount ?? $employee->step?->basic_salary ?? 0),
            'email' => $employee->email,
            'mobile_no' => $employee->mobile_no,
            'address' => $employee->address,
            'disciplinary_count' => $employee->disciplinaryRecords->count(),
            'total_deductions' => $employee->deductions->sum('amount'),
            'total_additions' => $employee->additions->sum('amount'),
            'loan_count' => $employee->loans->count(),
            'promotion_count' => $employee->promotionHistory->count(),
            'last_payroll_date' => $employee->payrollRecords->last()?->payment_date ?? 'N/A',
        ];
    }

    public function generateEmployeeDirectoryReport($filters = [])
    {
        $query = Employee::with(['department', 'gradeLevel', 'step']);

        if (isset($filters['department_id']) && $filters['department_id']) {
            $query->where('department_id', $filters['department_id']);
        }

        if (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        }

        $employees = $query->orderBy('surname')->get();

        $reportData = [
            'report_title' => 'Employee Directory Report',
            'generated_date' => now()->format('Y-m-d H:i:s'),
            'total_employees' => $employees->count(),
            'employees' => []
        ];

        foreach ($employees as $employee) {
            $reportData['employees'][] = [
                'employee_id' => $employee->employee_id,
                'full_name' => trim($employee->first_name . ' ' . $employee->middle_name . ' ' . $employee->surname),
                'department' => $employee->department->department_name ?? 'N/A',
                'grade_level' => $employee->gradeLevel->name ?? 'N/A',
                'step' => $employee->step->name ?? 'N/A',
                'status' => $employee->status,
                'email' => $employee->email,
                'mobile_no' => $employee->mobile_no,
                'extension' => $employee->extension ?? 'N/A'
            ];
        }

        return $reportData;
    }

    public function generateEmployeeStatusReport()
    {
        $statusCounts = Employee::select('status', \DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        $reportData = [
            'report_title' => 'Employee Status Report',
            'generated_date' => now()->format('Y-m-d H:i:s'),
            'status_summary' => $statusCounts->toArray(),
            'employees_by_status' => []
        ];

        foreach ($statusCounts as $status) {
            $employees = Employee::where('status', $status->status)
                ->with(['department', 'gradeLevel', 'step'])
                ->get();

            $reportData['employees_by_status'][$status->status] = $employees->map(function($employee) {
                return [
                    'employee_id' => $employee->employee_id,
                    'full_name' => trim($employee->first_name . ' ' . $employee->middle_name . ' ' . $employee->surname),
                    'department' => $employee->department->department_name ?? 'N/A',
                    'grade_level' => $employee->gradeLevel->name ?? 'N/A',
                    'step' => $employee->step->name ?? 'N/A',
                    'status' => $employee->status, // Add the status field
                    'date_of_first_appointment' => $employee->date_of_first_appointment,
                    'years_of_service' => $employee->getYearsOfServiceAttribute(),
                ];
            })->toArray();
        }

        return $reportData;
    }

    public function generatePayrollSummaryReport($year = null, $month = null, $filters = [])
    {
        $query = PayrollRecord::with(['employee.department', 'employee.gradeLevel', 'employee.appointmentType']);

        if ($year) {
            $query->whereYear('payroll_month', $year);
        }

        if ($month) {
            // Convert month name to number if needed
            $monthNumber = $this->convertMonthToNumber($month);
            $query->whereMonth('payroll_month', $monthNumber);
        }

        // Apply appointment type filter if provided
        if (!empty($filters['appointment_type_id'])) {
            $query->whereHas('employee', function($q) use ($filters) {
                $q->where('appointment_type_id', $filters['appointment_type_id']);
            });
        }

        // Apply status filter if provided
        if (!empty($filters['status'])) {
            $query->whereHas('employee', function($q) use ($filters) {
                $q->where('status', $filters['status']);
            });
        }

        $payrollRecords = $query->get();

        $reportData = [
            'report_title' => 'Payroll Summary Report',
            'generated_date' => now()->format('Y-m-d H:i:s'),
            'period' => $year . '-' . ($month ? $this->getMonthName($month) : '*'),
            'total_records' => $payrollRecords->count(),
            'total_basic_salary' => $payrollRecords->sum('basic_salary'),
            'total_deductions' => $payrollRecords->sum('total_deductions'),
            'total_additions' => $payrollRecords->sum('total_additions'),
            'total_net_salary' => $payrollRecords->sum('net_salary'),
            'payroll_records' => []
        ];

        foreach ($payrollRecords as $record) {
            $reportData['payroll_records'][] = [
                'employee_id' => $record->employee->employee_id,
                'full_name' => trim($record->employee->first_name . ' ' . $record->employee->middle_name . ' ' . $record->employee->surname),
                'department' => $record->employee->department->department_name ?? 'N/A',
                'grade_level' => $record->employee->gradeLevel->name ?? 'N/A',
                'basic_salary' => $record->basic_salary,
                'total_deductions' => $record->total_deductions,
                'total_additions' => $record->total_additions,
                'net_salary' => $record->net_salary,
                'payment_date' => $record->payment_date,
                'status' => $record->status
            ];
        }

        return $reportData;
    }

    private function convertMonthToNumber($month)
    {
        if (is_numeric($month)) {
            return (int)$month;
        }

        // Try to convert month name to number
        $monthNumber = date('n', strtotime("2025-$month-01"));
        if ($monthNumber !== false && $monthNumber > 0 && $monthNumber <= 12) {
            return $monthNumber;
        }

        // If month name is in full format like "October", try to parse it
        $monthNumber = date('n', strtotime($month . " 1"));
        if ($monthNumber !== false && $monthNumber > 0 && $monthNumber <= 12) {
            return $monthNumber;
        }

        // Default to 1 if conversion fails
        return 1;
    }

    private function getMonthName($month)
    {
        if (is_numeric($month)) {
            return date('F', mktime(0, 0, 0, $month, 1));
        }

        // If it's already a month name, return it
        return ucfirst(strtolower($month));
    }

    public function generateDeductionSummaryReport($filters = [])
    {
        $query = Deduction::with(['employee.department', 'deductionType']);

        if (isset($filters['employee_id']) && $filters['employee_id']) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (isset($filters['deduction_type_id']) && $filters['deduction_type_id']) {
            $query->where('deduction_type_id', $filters['deduction_type_id']);
        }

        // Apply year and month filters if provided
        if (isset($filters['year']) && $filters['year']) {
            $year = $filters['year'];
            $query->whereYear('start_date', $year);
        }

        if (isset($filters['month']) && $filters['month']) {
            $month = $filters['month'];
            $query->whereMonth('start_date', $month);
        }

        $deductions = $query->get();

        $reportData = [
            'report_title' => 'Deduction Summary Report',
            'generated_date' => now()->format('Y-m-d H:i:s'),
            'total_deductions' => $deductions->count(),
            'total_amount' => $deductions->sum('amount'),
            'deductions' => []
        ];

        foreach ($deductions as $deduction) {
            $reportData['deductions'][] = [
                'employee_id' => $deduction->employee->employee_id,
                'employee_name' => trim($deduction->employee->first_name . ' ' . $deduction->employee->middle_name . ' ' . $deduction->employee->surname),
                'department' => $deduction->employee->department->department_name ?? 'N/A',
                'deduction_type' => $deduction->deductionType->name ?? 'N/A',
                'amount' => $deduction->amount,
                'start_date' => $deduction->start_date,
                'end_date' => $deduction->end_date,
                'frequency' => $deduction->deduction_period ?? 'N/A'
            ];
        }

        return $reportData;
    }

    public function generateAdditionSummaryReport($filters = [])
    {
        $query = Addition::with(['employee.department', 'additionType']);

        if (isset($filters['employee_id']) && $filters['employee_id']) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (isset($filters['addition_type_id']) && $filters['addition_type_id']) {
            $query->where('addition_type_id', $filters['addition_type_id']);
        }

        // Apply year and month filters if provided
        if (isset($filters['year']) && $filters['year']) {
            $year = $filters['year'];
            $query->whereYear('start_date', $year);
        }

        if (isset($filters['month']) && $filters['month']) {
            $month = $filters['month'];
            $query->whereMonth('start_date', $month);
        }

        $additions = $query->get();

        $reportData = [
            'report_title' => 'Addition Summary Report',
            'generated_date' => now()->format('Y-m-d H:i:s'),
            'total_additions' => $additions->count(),
            'total_amount' => $additions->sum('amount'),
            'additions' => []
        ];

        foreach ($additions as $addition) {
            $reportData['additions'][] = [
                'employee_id' => $addition->employee->employee_id,
                'employee_name' => trim($addition->employee->first_name . ' ' . $addition->employee->middle_name . ' ' . $addition->employee->surname),
                'department' => $addition->employee->department->department_name ?? 'N/A',
                'addition_type' => $addition->additionType->name ?? 'N/A',
                'amount' => $addition->amount,
                'start_date' => $addition->start_date,
                'end_date' => $addition->end_date,
                'frequency' => $addition->period ?? 'N/A'
            ];
        }

        return $reportData;
    }

    public function generatePromotionHistoryReport($filters = [])
    {
        $query = PromotionHistory::with(['employee.department', 'employee.gradeLevel']);

        if (isset($filters['employee_id']) && $filters['employee_id']) {
            $query->where('employee_id', $filters['employee_id']);
        }

        $promotions = $query->get();

        $reportData = [
            'report_title' => 'Promotion History Report',
            'generated_date' => now()->format('Y-m-d H:i:s'),
            'total_promotions' => $promotions->count(),
            'promotions' => []
        ];

        foreach ($promotions as $promotion) {
            $reportData['promotions'][] = [
                'employee_id' => $promotion->employee->employee_id,
                'employee_name' => trim($promotion->employee->first_name . ' ' . $promotion->employee->middle_name . ' ' . $promotion->employee->surname),
                'department' => $promotion->employee->department->department_name ?? 'N/A',
                'previous_grade' => $promotion->previous_grade_level ?? 'N/A',
                'new_grade' => $promotion->new_grade_level ?? 'N/A',
                'promotion_date' => $promotion->promotion_date,
                'promotion_type' => $promotion->promotion_type ?? 'N/A',
                'reason' => $promotion->reason ?? 'N/A',
                'status' => $promotion->status ?? 'N/A'
            ];
        }

        return $reportData;
    }

    public function generateDisciplinaryReport($filters = [])
    {
        $query = DisciplinaryAction::with(['employee.department']);

        if (isset($filters['employee_id']) && $filters['employee_id']) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        }

        $actions = $query->get();

        $reportData = [
            'report_title' => 'Disciplinary Action Report',
            'generated_date' => now()->format('Y-m-d H:i:s'),
            'total_actions' => $actions->count(),
            'actions' => []
        ];

        foreach ($actions as $action) {
            $reportData['actions'][] = [
                'employee_id' => $action->employee->employee_id,
                'employee_name' => trim($action->employee->first_name . ' ' . $action->employee->middle_name . ' ' . $action->employee->surname),
                'department' => $action->employee->department->department_name ?? 'N/A',
                'action_type' => $action->action_type ?? 'N/A',
                'action_date' => $action->action_date,
                'description' => $action->description ?? 'N/A',
                'status' => $action->status ?? 'N/A',
                'resolution' => $action->resolution ?? 'N/A'
            ];
        }

        return $reportData;
    }

    public function generateRetirementPlanningReport($filters = [])
    {
        // Default to 2 years if no filter is provided
        $retirementWithinMonths = !empty($filters['retirement_within_months']) ? (int)$filters['retirement_within_months'] : 24; // 24 months = 2 years by default

        // Ensure the value is positive and reasonable (between 1 and 60 months)
        $retirementWithinMonths = max(1, min(60, $retirementWithinMonths));

        // Get employees approaching retirement based on the specified time period
        $approachingRetirement = Employee::where('expected_retirement_date', '<=', now()->addMonths($retirementWithinMonths)->format('Y-m-d'))
            ->where('status', 'Active')
            ->with(['department', 'gradeLevel'])
            ->get();

        $reportData = [
            'report_title' => 'Retirement Planning Report',
            'generated_date' => now()->format('Y-m-d H:i:s'),
            'retirement_within_months' => $retirementWithinMonths,
            'retirement_period_label' => $this->getRetirementPeriodLabel($retirementWithinMonths),
            'total_approaching_retirement' => $approachingRetirement->count(),
            'employees_approaching_retirement' => []
        ];

        foreach ($approachingRetirement as $employee) {
            $reportData['employees_approaching_retirement'][] = [
                'employee_id' => $employee->employee_id,
                'full_name' => trim($employee->first_name . ' ' . $employee->middle_name . ' ' . $employee->surname),
                'department' => $employee->department->department_name ?? 'N/A',
                'grade_level' => $employee->gradeLevel->name ?? 'N/A',
                'date_of_birth' => $employee->date_of_birth,
                'age' => Carbon::parse($employee->date_of_birth)->age,
                'date_of_first_appointment' => $employee->date_of_first_appointment,
                'years_of_service' => $employee->getYearsOfServiceAttribute(),
                'expected_retirement_date' => $employee->expected_retirement_date,
                'months_to_retirement' => Carbon::parse($employee->expected_retirement_date)->diffInMonths(now())
            ];
        }

        return $reportData;
    }

    /**
     * Helper method to get a user-friendly label for the retirement period
     */
    private function getRetirementPeriodLabel($months)
    {
        $months = (int)$months;

        if ($months == 6) {
            return 'Within 6 Months';
        } elseif ($months == 12) {
            return 'Within 1 Year';
        } elseif ($months == 18) {
            return 'Within 18 Months';
        } elseif ($months == 24) {
            return 'Within 2 Years';
        } else {
            $years = floor($months / 12);
            $remainingMonths = $months % 12;
            if ($remainingMonths == 0) {
                return "Within {$years} Year" . ($years > 1 ? 's' : '');
            } else {
                return "Within {$years} Year" . ($years > 1 ? 's' : '') . " {$remainingMonths} Month" . ($remainingMonths > 1 ? 's' : '');
            }
        }
    }

    public function generateLoanStatusReport($filters = [])
    {
        $query = Loan::with(['employee.department', 'deductionType']);

        if (isset($filters['employee_id']) && $filters['employee_id']) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['loan_type']) && $filters['loan_type']) {
            // This handles the loan_type filter from the dropdown, which could be either loan_type or deduction_type_id
            // First, let's check if it's a deduction type ID
            if (is_numeric($filters['loan_type'])) {
                $query->where('deduction_type_id', $filters['loan_type']);
            } else {
                // Otherwise, treat it as loan type name
                $query->where('loan_type', $filters['loan_type']);
            }
        }

        if (isset($filters['year']) && $filters['year']) {
            $query->whereYear('created_at', $filters['year']);
        }

        if (isset($filters['month']) && $filters['month']) {
            $query->whereMonth('created_at', $filters['month']);
        }

        $loans = $query->get();

        $reportData = [
            'report_title' => 'Loan Status Report',
            'generated_date' => now()->format('Y-m-d H:i:s'),
            'total_loans' => $loans->count(),
            'total_principal' => $loans->sum('principal_amount'),
            'total_repaid' => $loans->sum('total_repaid'),
            'total_remaining' => $loans->sum('remaining_balance'),
            'loans' => []
        ];

        foreach ($loans as $loan) {
            $reportData['loans'][] = [
                'employee_id' => $loan->employee->employee_id,
                'employee_name' => trim($loan->employee->first_name . ' ' . $loan->employee->middle_name . ' ' . $loan->employee->surname),
                'department' => $loan->employee->department->department_name ?? 'N/A',
                'loan_type' => ($loan->loan_type ?? 'N/A') . ($loan->deductionType ? ' (' . $loan->deductionType->name . ')' : ''),
                'principal_amount' => $loan->principal_amount,
                'monthly_deduction' => $loan->monthly_deduction,
                'total_months' => $loan->total_months,
                'total_repaid' => $loan->total_repaid,
                'remaining_balance' => $loan->remaining_balance,
                'status' => $loan->status ?? 'N/A',
                'application_date' => $loan->created_at->format('Y-m-d')
            ];
        }

        return $reportData;
    }

    public function generateDepartmentSummaryReport()
    {
        $departments = \App\Models\Department::with(['employees'])->get();

        $reportData = [
            'report_title' => 'Department Summary Report',
            'generated_date' => now()->format('Y-m-d H:i:s'),
            'total_departments' => $departments->count(),
            'departments' => []
        ];

        foreach ($departments as $department) {
            $employees = $department->employees;
            $activeEmployees = $employees->where('status', 'Active');

            $reportData['departments'][] = [
                'department_id' => $department->department_id,
                'department_name' => $department->department_name,
                'total_employees' => $employees->count(),
                'active_employees' => $activeEmployees->count(),
                'suspended_employees' => $employees->where('status', 'Suspended')->count(),
                'retired_employees' => $employees->where('status', 'Retired')->count(),
                'deceased_employees' => $employees->where('status', 'Deceased')->count(),
                'total_basic_salary' => $activeEmployees->sum('basic_salary'),
                'total_contract_amount' => $activeEmployees->sum('amount'),
                'average_years_of_service' => $activeEmployees->avg('years_of_service') ?? 0
            ];
        }

        return $reportData;
    }

    public function generateGradeLevelSummaryReport()
    {
        $gradeLevels = \App\Models\GradeLevel::with(['employees', 'salaryScale'])->get();

        $reportData = [
            'report_title' => 'Grade Level Summary Report',
            'generated_date' => now()->format('Y-m-d H:i:s'),
            'total_grade_levels' => $gradeLevels->count(),
            'grade_levels' => []
        ];

        foreach ($gradeLevels as $gradeLevel) {
            $employees = $gradeLevel->employees;
            $activeEmployees = $employees->where('status', 'Active');

            $reportData['grade_levels'][] = [
                'grade_level_id' => $gradeLevel->id,
                'grade_level_name' => $gradeLevel->name ?? $gradeLevel->grade_level,
                'salary_scale' => $gradeLevel->salaryScale->full_name ?? 'N/A',
                'total_employees' => $employees->count(),
                'active_employees' => $activeEmployees->count(),
                'total_basic_salary' => $activeEmployees->sum('basic_salary'),
                'average_basic_salary' => $activeEmployees->avg('basic_salary') ?? 0,
                'min_basic_salary' => $activeEmployees->min('basic_salary') ?? 0,
                'max_basic_salary' => $activeEmployees->max('basic_salary') ?? 0
            ];
        }

        return $reportData;
    }

    public function generateAuditTrailReport($filters = [])
    {
        $query = \App\Models\AuditTrail::with(['user.employee']);

        if (isset($filters['user_id']) && $filters['user_id']) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['action']) && $filters['action']) {
            $query->where('action', $filters['action']);
        }

        if (isset($filters['start_date']) && $filters['start_date']) {
            $query->where('action_timestamp', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date']) && $filters['end_date']) {
            $query->where('action_timestamp', '<=', $filters['end_date']);
        }

        $auditTrails = $query->orderBy('action_timestamp', 'desc')->get();

        $reportData = [
            'report_title' => 'Audit Trail Report',
            'generated_date' => now()->format('Y-m-d H:i:s'),
            'total_activities' => $auditTrails->count(),
            'activities' => []
        ];

        foreach ($auditTrails as $activity) {
            $employeeName = $activity->user->employee
                ? trim($activity->user->employee->first_name . ' ' . $activity->user->employee->middle_name . ' ' . $activity->user->employee->surname)
                : null;

            $reportData['activities'][] = [
                'user_name' => $employeeName ?: $activity->user->username ?? 'System',
                'action' => $activity->action,
                'description' => $activity->description,
                'timestamp' => $activity->action_timestamp->format('Y-m-d H:i:s'),
                'entity_type' => $activity->log_data['entity_type'] ?? 'N/A',
                'entity_id' => $activity->log_data['entity_id'] ?? 'N/A'
            ];
        }

        return $reportData;
    }

    public function generatePayrollAnalysisReport($year = null, $month = null)
    {
        $query = PayrollRecord::with(['employee.department', 'employee.gradeLevel']);

        if ($year) {
            $query->whereYear('payroll_month', $year);
        }

        if ($month) {
            $monthNumber = $this->convertMonthToNumber($month);
            $query->whereMonth('payroll_month', $monthNumber);
        }

        $payrollRecords = $query->get();

        $reportData = [
            'report_title' => 'Payroll Analysis Report',
            'generated_date' => now()->format('Y-m-d H:i:s'),
            'period' => ($year ?? 'All Years') . '-' . ($month ? $this->getMonthName($month) : '*'),
            'total_records' => $payrollRecords->count(),
            'total_basic_salary' => $payrollRecords->sum('basic_salary'),
            'total_deductions' => $payrollRecords->sum('total_deductions'),
            'total_additions' => $payrollRecords->sum('total_additions'),
            'total_net_salary' => $payrollRecords->sum('net_salary'),
            'average_basic_salary' => $payrollRecords->avg('basic_salary') ?? 0,
            'average_net_salary' => $payrollRecords->avg('net_salary') ?? 0,
            'payroll_records' => []
        ];

        foreach ($payrollRecords as $record) {
            $reportData['payroll_records'][] = [
                'employee_id' => $record->employee->employee_id,
                'full_name' => trim($record->employee->first_name . ' ' . $record->employee->middle_name . ' ' . $record->employee->surname),
                'department' => $record->employee->department->department_name ?? 'N/A',
                'grade_level' => $record->employee->gradeLevel->name ?? 'N/A',
                'basic_salary' => $record->basic_salary,
                'total_deductions' => $record->total_deductions,
                'total_additions' => $record->total_additions,
                'net_salary' => $record->net_salary,
                'payment_date' => $record->payment_date,
                'status' => $record->status
            ];
        }

        return $reportData;
    }
}