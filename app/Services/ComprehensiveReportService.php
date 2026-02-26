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
            'employee_id' => $employee->staff_no ?? $employee->employee_id,
            'full_name' => trim($employee->first_name . ' ' . $employee->middle_name . ' ' . $employee->surname),
            'department' => $employee->department->department_name ?? 'N/A',
            'cadre' => $employee->cadre->name ?? 'N/A',
            'grade_level' => $employee->gradeLevel->name ?? 'N/A',
            'step' => $employee->step->name ?? 'N/A',
            'status' => $employee->status,
            'appointment_type' => $employee->appointmentType->name ?? 'N/A',
            'date_of_first_appointment' => $employee->date_of_first_appointment,
            'years_of_service' => $employee->getYearsOfServiceAttribute(),
            'basic_salary' => $employee->isCasualEmployee() ?
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
                'employee_id' => $employee->staff_no ?? $employee->employee_id,
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
                    'employee_id' => $employee->staff_no ?? $employee->employee_id,
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
        $query = PayrollRecord::with(['employee.department', 'employee.gradeLevel', 'employee.appointmentType', 'employee.bank']);

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

        // Apply payment type filter (Regular, Pension, Gratuity, Permanent, Casual)
        if (!empty($filters['payment_type'])) {
            $query->where('payment_type', $filters['payment_type']);
        }

        $payrollRecords = $query->get();

        $reportData = [
            'report_title' => 'Payroll Summary Report',
            'generated_date' => now()->format('Y-m-d H:i:s'),
            'period' => $year . '-' . ($month ? $this->getMonthName($month) : '*'),
            'payment_type' => $filters['payment_type'] ?? null,
            'total_records' => $payrollRecords->count(),
            'total_basic_salary' => $payrollRecords->sum('basic_salary'),
            'total_deductions' => $payrollRecords->sum('total_deductions'),
            'total_additions' => $payrollRecords->sum('total_additions'),
            'total_net_salary' => $payrollRecords->sum('net_salary'),
            'payroll_records' => []
        ];

        foreach ($payrollRecords as $record) {
            // Get individual deductions for this employee with their types
            // FIX: Filter by payroll month to match "Active During" logic
            $payrollMonth = Carbon::parse($record->payroll_month);
            $employeeDeductions = \App\Models\Deduction::where('employee_id', $record->employee_id)
                ->where('start_date', '<=', $payrollMonth->endOfMonth())
                ->where(function($q) use ($payrollMonth) {
                    $q->whereNull('end_date')
                      ->orWhere('end_date', '>=', $payrollMonth->startOfMonth());
                })
                ->with('deductionType')
                ->get();
            
            // Build deduction breakdown by type name
            $deductionBreakdown = [];
            foreach ($employeeDeductions as $deduction) {
                $typeName = $deduction->deductionType->name ?? $deduction->deduction_type ?? 'Unknown';
                if (!isset($deductionBreakdown[$typeName])) {
                    $deductionBreakdown[$typeName] = 0;
                }
                $deductionBreakdown[$typeName] += $deduction->amount;
            }

            // Get individual additions for this employee with their types
            $employeeAdditions = \App\Models\Addition::where('employee_id', $record->employee_id)
                ->where('start_date', '<=', $payrollMonth->endOfMonth())
                ->where(function($q) use ($payrollMonth) {
                    $q->whereNull('end_date')
                      ->orWhere('end_date', '>=', $payrollMonth->startOfMonth());
                })
                ->with('additionType')
                ->get();

            // Build addition breakdown by type name
            $additionBreakdown = [];
            foreach ($employeeAdditions as $addition) {
                $typeName = $addition->additionType->name ?? 'Unknown';
                if (!isset($additionBreakdown[$typeName])) {
                    $additionBreakdown[$typeName] = 0;
                }
                $additionBreakdown[$typeName] += $addition->amount;
            }
            
            $reportData['payroll_records'][] = [
                'employee_id' => $record->employee->staff_no ?? $record->employee->employee_id,
                'full_name' => trim($record->employee->first_name . ' ' . $record->employee->middle_name . ' ' . $record->employee->surname),
                'department' => $record->employee->department->department_name ?? 'N/A',
                'grade_level' => $record->employee->gradeLevel->name ?? 'N/A',
                'basic_salary' => $record->basic_salary,
                'total_deductions' => $record->total_deductions,
                'total_additions' => $record->total_additions,
                'net_salary' => $record->net_salary,
                'payment_date' => $record->payment_date,
                'status' => $record->status,
                'bank_name' => $record->employee->bank->bank_name ?? 'NO BANK',
                'account_number' => $record->employee->bank->account_no ?? 'N/A',
                'deduction_breakdown' => $deductionBreakdown,
                'addition_breakdown' => $additionBreakdown
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

        if (isset($filters['appointment_type_id']) && $filters['appointment_type_id']) {
            $query->whereHas('employee', function($q) use ($filters) {
                $q->where('appointment_type_id', $filters['appointment_type_id']);
            });
        }

        // Apply year and month filters if provided (Active During logic)
        $dateFilterStart = null;
        $dateFilterEnd = null;

        if (isset($filters['year']) && $filters['year']) {
            $year = $filters['year'];
            $dateFilterStart = Carbon::createFromDate($year, 1, 1)->startOfYear();
            $dateFilterEnd = Carbon::createFromDate($year, 1, 1)->endOfYear();
            
            if (isset($filters['month']) && $filters['month']) {
                $month = $filters['month'];
                if (is_numeric($month)) {
                    $dateFilterStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
                    $dateFilterEnd = Carbon::createFromDate($year, $month, 1)->endOfMonth();
                } else {
                     // Try to parse month name
                    try {
                        $dateObj = Carbon::parse("$year-$month-01");
                        $dateFilterStart = $dateObj->copy()->startOfMonth();
                        $dateFilterEnd = $dateObj->copy()->endOfMonth();
                    } catch (\Exception $e) {}
                }
            }
        }

        if ($dateFilterStart && $dateFilterEnd) {
             $query->where('start_date', '<=', $dateFilterEnd)
                  ->where(function($q) use ($dateFilterStart) {
                      $q->whereNull('end_date')
                        ->orWhere('end_date', '>=', $dateFilterStart);
                  });
        } elseif (isset($filters['month']) && $filters['month']) {
             $month = $filters['month'];
             $query->whereMonth('start_date', $month);
        }

        $deductions = $query->get();

        $reportData = [
            'report_title' => 'Deduction Summary Report',
            'generated_date' => now()->format('Y-m-d H:i:s'),
            'total_deductions' => $deductions->count(),
            'total_amount' => $deductions->sum('amount'),
            'deduction_types' => [],
            'employees' => []
        ];

        $deductionTypes = [];
        $groupedDeductions = [];

        foreach ($deductions as $deduction) {
            $employeeId = $deduction->employee_id;
            
            if (!isset($groupedDeductions[$employeeId])) {
                $groupedDeductions[$employeeId] = [
                    'employee_id' => $deduction->employee->staff_no ?? $deduction->employee->employee_id,
                    'employee_name' => trim($deduction->employee->first_name . ' ' . $deduction->employee->middle_name . ' ' . $deduction->employee->surname),
                    'department' => $deduction->employee->department->department_name ?? 'N/A',
                    'deductions' => [],
                    'total_deductions' => 0
                ];
            }

            $typeName = $deduction->deductionType->name ?? 'N/A';
            $deductionTypes[$typeName] = true;

            if (!isset($groupedDeductions[$employeeId]['deductions'][$typeName])) {
                $groupedDeductions[$employeeId]['deductions'][$typeName] = 0;
            }
            
            $groupedDeductions[$employeeId]['deductions'][$typeName] += $deduction->amount;
            $groupedDeductions[$employeeId]['total_deductions'] += $deduction->amount;
        }

        $reportData['deduction_types'] = array_keys($deductionTypes);
        sort($reportData['deduction_types']);
        $reportData['employees'] = array_values($groupedDeductions);

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

        if (isset($filters['appointment_type_id']) && $filters['appointment_type_id']) {
            $query->whereHas('employee', function($q) use ($filters) {
                $q->where('appointment_type_id', $filters['appointment_type_id']);
            });
        }

        // Apply year and month filters if provided (Active During logic)
        $dateFilterStart = null;
        $dateFilterEnd = null;

        if (isset($filters['year']) && $filters['year']) {
            $year = $filters['year'];
            $dateFilterStart = Carbon::createFromDate($year, 1, 1)->startOfYear();
            $dateFilterEnd = Carbon::createFromDate($year, 1, 1)->endOfYear();
            
            if (isset($filters['month']) && $filters['month']) {
                $month = $filters['month'];
                if (is_numeric($month)) {
                    $dateFilterStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
                    $dateFilterEnd = Carbon::createFromDate($year, $month, 1)->endOfMonth();
                } else {
                    try {
                        $dateObj = Carbon::parse("$year-$month-01");
                        $dateFilterStart = $dateObj->copy()->startOfMonth();
                        $dateFilterEnd = $dateObj->copy()->endOfMonth();
                    } catch (\Exception $e) {}
                }
            }
        }

        if ($dateFilterStart && $dateFilterEnd) {
             $query->where('start_date', '<=', $dateFilterEnd)
                  ->where(function($q) use ($dateFilterStart) {
                      $q->whereNull('end_date')
                        ->orWhere('end_date', '>=', $dateFilterStart);
                  });
        } elseif (isset($filters['month']) && $filters['month']) {
             $month = $filters['month'];
             $query->whereMonth('start_date', $month);
        }

        $additions = $query->get();

        $reportData = [
            'report_title' => 'Addition Summary Report',
            'generated_date' => now()->format('Y-m-d H:i:s'),
            'total_additions' => $additions->count(),
            'total_amount' => $additions->sum('amount'),
            'addition_types' => [],
            'employees' => []
        ];

        $additionTypes = [];
        $groupedAdditions = [];

        foreach ($additions as $addition) {
            $employeeId = $addition->employee_id;
            
            if (!isset($groupedAdditions[$employeeId])) {
                $groupedAdditions[$employeeId] = [
                    'employee_id' => $addition->employee->staff_no ?? $addition->employee->employee_id,
                    'employee_name' => trim($addition->employee->first_name . ' ' . $addition->employee->middle_name . ' ' . $addition->employee->surname),
                    'department' => $addition->employee->department->department_name ?? 'N/A',
                    'additions' => [],
                    'total_additions' => 0
                ];
            }

            $typeName = $addition->additionType->name ?? 'N/A';
            $additionTypes[$typeName] = true;

            if (!isset($groupedAdditions[$employeeId]['additions'][$typeName])) {
                $groupedAdditions[$employeeId]['additions'][$typeName] = 0;
            }
            
            $groupedAdditions[$employeeId]['additions'][$typeName] += $addition->amount;
            $groupedAdditions[$employeeId]['total_additions'] += $addition->amount;
        }

        $reportData['addition_types'] = array_keys($additionTypes);
        sort($reportData['addition_types']);
        $reportData['employees'] = array_values($groupedAdditions);

        return $reportData;
    }

    public function generatePromotionHistoryReport($filters = [])
    {
        $query = PromotionHistory::with(['employee.department', 'employee.gradeLevel', 'employee.appointmentType']);

        if (isset($filters['employee_id']) && $filters['employee_id']) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (isset($filters['appointment_type_id']) && $filters['appointment_type_id']) {
            $query->whereHas('employee', function($q) use ($filters) {
                $q->where('appointment_type_id', $filters['appointment_type_id']);
            });
        }

        // Apply year and month filters
        if (isset($filters['year']) && $filters['year']) {
            $query->whereYear('promotion_date', $filters['year']);
            
            if (isset($filters['month']) && $filters['month']) {
                 if (is_numeric($filters['month'])) {
                    $query->whereMonth('promotion_date', $filters['month']);
                 } else {
                     try {
                        $monthNum = Carbon::parse("2000-{$filters['month']}-01")->month;
                        $query->whereMonth('promotion_date', $monthNum);
                     } catch (\Exception $e) {}
                 }
            }
        }

        $promotions = $query->get();

        $reportData = [
            'report_title' => 'Promotion History Report',
            'generated_date' => now()->format('Y-m-d H:i:s'),
            'total_promotions' => $promotions->count(),
            'promotions' => []
        ];

        foreach ($promotions as $promotion) {
            // Resolve grade level display value
            $previousGrade = $promotion->previous_grade_level;
            $newGrade = $promotion->new_grade_level;
            $previousStep = $promotion->previous_step;
            $newStep = $promotion->new_step;

            // Fall back to current employee GL/step if the promotion record is missing them
            if (empty($previousGrade) && $promotion->employee?->gradeLevel) {
                $previousGrade = $promotion->employee->gradeLevel->name ?? 'N/A';
            }
            if (empty($newGrade) && $promotion->employee?->gradeLevel) {
                $newGrade = $promotion->employee->gradeLevel->name ?? 'N/A';
            }

            // Load step name via Step model if step is stored as an ID (integer)
            if (!empty($previousStep) && is_numeric($previousStep)) {
                $stepModel = \App\Models\Step::find($previousStep);
                $previousStep = $stepModel ? $stepModel->name : $previousStep;
            }
            if (!empty($newStep) && is_numeric($newStep)) {
                $stepModel = \App\Models\Step::find($newStep);
                $newStep = $stepModel ? $stepModel->name : $newStep;
            }

            $reportData['promotions'][] = [
                'employee_id' => $promotion->employee->staff_no ?? $promotion->employee->employee_id,
                'employee_name' => trim($promotion->employee->first_name . ' ' . $promotion->employee->middle_name . ' ' . $promotion->employee->surname),
                'department' => $promotion->employee->department->department_name ?? 'N/A',
                'previous_grade' => $previousGrade ?? 'N/A',
                'new_grade' => $newGrade ?? 'N/A',
                'previous_step' => $previousStep ?? 'N/A',
                'new_step' => $newStep ?? 'N/A',
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
        $query = DisciplinaryAction::with(['employee.department', 'employee.appointmentType']);

        if (isset($filters['employee_id']) && $filters['employee_id']) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['appointment_type_id']) && $filters['appointment_type_id']) {
            $query->whereHas('employee', function($q) use ($filters) {
                $q->where('appointment_type_id', $filters['appointment_type_id']);
            });
        }

        if (isset($filters['year']) && $filters['year']) {
            $query->whereYear('action_date', $filters['year']);

            if (isset($filters['month']) && $filters['month']) {
                if (is_numeric($filters['month'])) {
                    $query->whereMonth('action_date', $filters['month']);
                } else {
                    try {
                        $monthNum = Carbon::parse("2000-{$filters['month']}-01")->month;
                        $query->whereMonth('action_date', $monthNum);
                    } catch (\Exception $e) {}
                }
            }
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
                'employee_id' => $action->employee->staff_no ?? $action->employee->employee_id,
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
        // Default to 6 months if no filter is provided, as per user request context
        $retirementWithinMonths = !empty($filters['retirement_within_months']) ? (int)$filters['retirement_within_months'] : 6;

        // Ensure the value is positive and reasonable (between 1 and 60 months)
        $retirementWithinMonths = max(1, min(60, $retirementWithinMonths));
        
        $targetDate = now()->addMonths($retirementWithinMonths);

        // Get all active employees with necessary relationships
        $employees = Employee::where('status', 'Active')
            ->with(['department', 'gradeLevel.salaryScale'])
            ->get();
            
        // Filter employees approaching retirement using dynamic calculation
        $approachingRetirement = $employees->filter(function ($employee) use ($targetDate) {
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
            
            // Store the calculated date for later use
            $employee->calculated_retirement_date = $actualRetirementDate;

            // Filter: check if the retirement date is between now and the target date
            return $actualRetirementDate->isBetween(now(), $targetDate);
        });

        $reportData = [
            'report_title' => 'Retirement Planning Report',
            'generated_date' => now()->format('Y-m-d H:i:s'),
            'retirement_within_months' => $retirementWithinMonths,
            'retirement_period_label' => $this->getRetirementPeriodLabel($retirementWithinMonths),
            'total_approaching_retirement' => $approachingRetirement->count(),
            'employees_approaching_retirement' => []
        ];

        foreach ($approachingRetirement as $employee) {
            // Recalculate or use stored calculated date
            $actualRetirementDate = $employee->calculated_retirement_date;
            
            // Determine retirement reason
            $age = Carbon::parse($employee->date_of_birth)->age;
            $serviceDuration = Carbon::parse($employee->date_of_first_appointment)->diffInYears(Carbon::now());
            $retirementAge = (int) $employee->gradeLevel->salaryScale->max_retirement_age;
            $yearsOfService = (int) $employee->gradeLevel->salaryScale->max_years_of_service;

            if ($serviceDuration >= $yearsOfService) {
                $retirementReason = 'By Years of Service';
            } elseif ($age >= $retirementAge) {
                $retirementReason = 'By Old Age';
            } else {
                $dateByAge = Carbon::parse($employee->date_of_birth)->addYears($retirementAge);
                if ($actualRetirementDate->eq($dateByAge)) {
                    $retirementReason = 'By Old Age';
                } else {
                    $retirementReason = 'By Years of Service';
                }
            }

            $reportData['employees_approaching_retirement'][] = [
                'employee_id' => $employee->staff_no ?? $employee->employee_id,
                'full_name' => trim($employee->first_name . ' ' . $employee->middle_name . ' ' . $employee->surname),
                'department' => $employee->department->department_name ?? 'N/A',
                'grade_level' => $employee->gradeLevel->name ?? 'N/A',
                'date_of_birth' => $employee->date_of_birth,
                'age' => $age,
                'date_of_first_appointment' => $employee->date_of_first_appointment,
                'years_of_service' => $employee->getYearsOfServiceAttribute(),
                'expected_retirement_date' => $actualRetirementDate->format('Y-m-d'),
                'calculated_retirement_date' => $actualRetirementDate->format('Y-m-d'),
                'months_to_retirement' => Carbon::parse($actualRetirementDate)->diffInMonths(now()),
                'retirement_reason' => $retirementReason,
                'status' => $employee->status
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
                'employee_id' => $loan->employee->staff_no ?? $loan->employee->employee_id,
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
                'employee_id' => $record->employee->staff_no ?? $record->employee->employee_id,
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

    public function generateRetirementPlanningReportWithin6Months()
    {
        // Get all active employees first
        $employees = Employee::where('status', 'Active')
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

            return $actualRetirementDate->lessThanOrEqualTo($sixMonthsFromNow);
        });

        $reportData = [
            'report_title' => 'Retirement Planning Report (Within 6 Months)',
            'generated_date' => now()->format('Y-m-d H:i:s'),
            'retirement_within_months' => 6,
            'retirement_period_label' => 'Within 6 Months',
            'total_approaching_retirement' => $approachingRetirement->count(),
            'employees_approaching_retirement' => []
        ];

        foreach ($approachingRetirement as $employee) {
            // Calculate retirement date based on age
            $retirementDateByAge = Carbon::parse($employee->date_of_birth)->addYears($employee->gradeLevel->salaryScale->max_retirement_age);

            // Calculate retirement date based on service
            $retirementDateByService = Carbon::parse($employee->date_of_first_appointment)->addYears($employee->gradeLevel->salaryScale->max_years_of_service);

            // The actual retirement date is the earlier of the two
            $actualRetirementDate = $retirementDateByAge->min($retirementDateByService);

            // Determine retirement reason
            $age = Carbon::parse($employee->date_of_birth)->age;
            $serviceDuration = Carbon::parse($employee->date_of_first_appointment)->diffInYears(Carbon::now());

            if ($serviceDuration >= $employee->gradeLevel->salaryScale->max_years_of_service) {
                $retirementReason = 'By Years of Service';
            } elseif ($age >= $employee->gradeLevel->salaryScale->max_retirement_age) {
                $retirementReason = 'By Old Age';
            } else {
                $actualRetirementDateForReason = Carbon::parse($employee->date_of_birth)->addYears($employee->gradeLevel->salaryScale->max_retirement_age)->min(Carbon::parse($employee->date_of_first_appointment)->addYears($employee->gradeLevel->salaryScale->max_years_of_service));
                if ($actualRetirementDateForReason->eq(Carbon::parse($employee->date_of_birth)->addYears($employee->gradeLevel->salaryScale->max_retirement_age))) {
                    $retirementReason = 'By Old Age';
                } else {
                    $retirementReason = 'By Years of Service';
                }
            }

            $reportData['employees_approaching_retirement'][] = [
                'employee_id' => $employee->staff_no ?? $employee->employee_id,
                'full_name' => trim($employee->first_name . ' ' . $employee->middle_name . ' ' . $employee->surname),
                'calculated_retirement_date' => $actualRetirementDate->format('Y-m-d'),
                'expected_retirement_date' => $actualRetirementDate->format('Y-m-d'),
                'years_of_service' => $serviceDuration . ' years',
                'age' => $age,
                'retirement_reason' => $retirementReason,
                'status' => $employee->status,
                'department' => $employee->department->department_name ?? 'N/A',
                'grade_level' => $employee->gradeLevel->name ?? 'N/A',
            ];
        }

        return $reportData;
    }

    /**
     * Generate a retirement report showing employees retiring within 6 months (matching the retirement planning page)
     */
    public function generateRetirementReport($filters = [])
    {
        // Default to 6 months if no filter is provided
        $retirementWithinMonths = !empty($filters['retirement_within_months']) ? (int)$filters['retirement_within_months'] : 6; // 6 months by default

        // Ensure the value is positive and reasonable (between 1 and 60 months)
        $retirementWithinMonths = max(1, min(60, $retirementWithinMonths));

        // Get all active employees
        $employees = Employee::where('status', 'Active')
            ->with(['department', 'gradeLevel.salaryScale'])
            ->get();

        $endDate = now()->addMonths($retirementWithinMonths);

        // Filter employees approaching retirement within specified months using the same logic as the retirement page
        $approachingRetirement = $employees->filter(function ($employee) use ($endDate) {
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

            // Check if this retirement date falls within our range
            return $actualRetirementDate->isBetween(now(), $endDate);
        });

        $reportData = [
            'report_title' => 'Retirement Report (Employees Retiring Within ' . $retirementWithinMonths . ' Months)',
            'generated_date' => now()->format('Y-m-d H:i:s'),
            'retirement_within_months' => $retirementWithinMonths,
            'retirement_period_label' => $this->getRetirementPeriodLabel($retirementWithinMonths),
            'total_approaching_retirement' => $approachingRetirement->count(),
            'employees_approaching_retirement' => []
        ];

        foreach ($approachingRetirement as $employee) {
            // Calculate retirement date based on age
            $retirementDateByAge = Carbon::parse($employee->date_of_birth)->addYears($employee->gradeLevel->salaryScale->max_retirement_age);

            // Calculate retirement date based on service
            $retirementDateByService = Carbon::parse($employee->date_of_first_appointment)->addYears($employee->gradeLevel->salaryScale->max_years_of_service);

            // The actual retirement date is the earlier of the two
            $actualRetirementDate = $retirementDateByAge->min($retirementDateByService);

            // Determine retirement reason
            $currentAge = Carbon::parse($employee->date_of_birth)->age;
            $currentServiceDuration = Carbon::parse($employee->date_of_first_appointment)->diffInYears(Carbon::now());
            $maxAge = (int) $employee->gradeLevel->salaryScale->max_retirement_age;
            $maxService = (int) $employee->gradeLevel->salaryScale->max_years_of_service;

            if ($currentServiceDuration >= $maxService) {
                $retirementReason = 'By Years of Service';
            } elseif ($currentAge >= $maxAge) {
                $retirementReason = 'By Old Age';
            } else {
                // Determine which milestone will come first
                $ageBasedRetirementDate = Carbon::parse($employee->date_of_birth)->addYears($maxAge);
                $serviceBasedRetirementDate = Carbon::parse($employee->date_of_first_appointment)->addYears($maxService);

                if ($ageBasedRetirementDate->lte($serviceBasedRetirementDate)) {
                    $retirementReason = 'By Old Age';
                } else {
                    $retirementReason = 'By Years of Service';
                }
            }

            $reportData['employees_approaching_retirement'][] = [
                'employee_id' => $employee->staff_no ?? $employee->employee_id,
                'full_name' => trim($employee->first_name . ' ' . $employee->middle_name . ' ' . $employee->surname),
                'calculated_retirement_date' => $actualRetirementDate->format('Y-m-d'),
                'expected_retirement_date' => $actualRetirementDate->format('Y-m-d'),
                'years_of_service' => $currentServiceDuration . ' years',
                'age' => $currentAge,
                'retirement_reason' => $retirementReason,
                'status' => $employee->status,
                'department' => $employee->department->department_name ?? 'N/A',
                'grade_level' => $employee->gradeLevel->name ?? 'N/A',
            ];
        }

        return $reportData;
    }

    /**
     * Generate a retirement report based on actual retirement records from the retirements table
     */
    public function generateHistoricalRetirementReport($filters = [])
    {
        $query = Retirement::with(['employee.department', 'employee.gradeLevel', 'employee.step']);

        // Apply filters
        if (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['retirement_date_from']) && $filters['retirement_date_from']) {
            $query->where('retirement_date', '>=', $filters['retirement_date_from']);
        }

        if (isset($filters['retirement_date_to']) && $filters['retirement_date_to']) {
            $query->where('retirement_date', '<=', $filters['retirement_date_to']);
        }

        if (isset($filters['retire_reason']) && $filters['retire_reason']) {
            $query->where('retire_reason', 'like', '%' . $filters['retire_reason'] . '%');
        }

        $retirements = $query->orderBy('retirement_date', 'desc')->get();

        $reportData = [
            'report_title' => 'Retirement Report (Historical)',
            'generated_date' => now()->format('Y-m-d H:i:s'),
            'total_retirements' => $retirements->count(),
            'total_gratuity_paid' => $retirements->sum('gratuity_amount'),
            'retirements' => []
        ];

        foreach ($retirements as $retirement) {
            $reportData['retirements'][] = [
                'employee_id' => $retirement->employee->staff_no ?? $retirement->employee->employee_id,
                'full_name' => trim($retirement->employee->first_name . ' ' . $retirement->employee->middle_name . ' ' . $retirement->employee->surname),
                'department' => $retirement->employee->department->department_name ?? 'N/A',
                'grade_level' => $retirement->employee->gradeLevel->name ?? 'N/A',
                'step' => $retirement->employee->step->name ?? 'N/A',
                'retirement_date' => $retirement->retirement_date,
                'notification_date' => $retirement->notification_date,
                'gratuity_amount' => $retirement->gratuity_amount,
                'status' => $retirement->status,
                'retire_reason' => $retirement->retire_reason,
                'date_of_birth' => $retirement->employee->date_of_birth,
                'age_at_retirement' => Carbon::parse($retirement->employee->date_of_birth)->age,
                'years_of_service' => $retirement->employee->getYearsOfServiceAttribute(),
            ];
        }

        return $reportData;
    }
    public function generatePayrollJournalReport($filters = [])
    {
        $year = $filters['year'] ?? now()->year;
        $month = $filters['month'] ?? now()->month;
        $monthNumber = $this->convertMonthToNumber($month);

        // Get all payroll records for the specified month/year
        $payrollRecords = PayrollRecord::whereYear('payroll_month', $year)
            ->whereMonth('payroll_month', $monthNumber)
            ->get();

        if ($payrollRecords->isEmpty()) {
            return [
                'report_title' => 'Payroll Journal Report',
                'generated_date' => now()->format('Y-m-d H:i:s'),
                'period' => $year . '-' . $this->getMonthName($monthNumber),
                'error' => 'No payroll records found for this period.'
            ];
        }

        $employeeIds = $payrollRecords->pluck('employee_id');
        $journalItems = collect();

        // 1. Regular Deductions (Templates active in this month)
        $deductions = Deduction::whereIn('employee_id', $employeeIds)
            ->where(function ($query) use ($year, $monthNumber) {
                $date = Carbon::createFromDate($year, $monthNumber, 1);
                $query->where('start_date', '<=', $date->endOfMonth())
                      ->where(function ($q) use ($date) {
                          $q->where('end_date', '>=', $date->startOfMonth())
                            ->orWhereNull('end_date');
                      });
            })
            ->whereNull('loan_id') // Exclude loan deductions (handled separately)
            ->with('deductionType')
            ->get();

        $groupedDeductions = $deductions->groupBy('deduction_type_id');

        foreach ($groupedDeductions as $typeId => $items) {
            $firstItem = $items->first();
            $journalItems->push([
                'code' => $firstItem->deductionType->id ?? 'D-'.$typeId,
                'description' => $firstItem->deductionType->name ?? 'Unknown Deduction',
                'count' => $items->unique('employee_id')->count(),
                'amount' => $items->sum('amount'),
                'type' => 'Deduction'
            ]);
        }

        // 2. Loan Deductions (Actual records from LoanDeduction table)
        $loanDeductions = \App\Models\LoanDeduction::whereIn('employee_id', $employeeIds)
            ->whereYear('deduction_date', $year)
            ->whereMonth('deduction_date', $monthNumber)
            ->with(['loan.deductionType'])
            ->get();

        $groupedLoanDeductions = $loanDeductions->groupBy(function ($item) {
            return $item->loan->deduction_type_id ?? 'L-' . $item->loan_id;
        });

        foreach ($groupedLoanDeductions as $key => $items) {
            $firstItem = $items->first();
            $description = $firstItem->loan->deductionType->name ?? $firstItem->loan->loan_type ?? 'Loan Deduction';
            
            $existingItem = $journalItems->firstWhere('description', $description);
            
            if ($existingItem) {
                $journalItems = $journalItems->map(function ($item) use ($description, $items) {
                    if ($item['description'] === $description) {
                        $item['count'] += $items->unique('employee_id')->count();
                        $item['amount'] += $items->sum('amount_deducted');
                    }
                    return $item;
                });
            } else {
                $journalItems->push([
                    'code' => $firstItem->loan->deductionType->id ?? 'L-'.$key,
                    'description' => $description,
                    'count' => $items->unique('employee_id')->count(),
                    'amount' => $items->sum('amount_deducted'),
                    'type' => 'Deduction'
                ]);
            }
        }

        // 3. Additions
        $additions = Addition::whereIn('employee_id', $employeeIds)
            ->where(function ($query) use ($year, $monthNumber) {
                $date = Carbon::createFromDate($year, $monthNumber, 1);
                $query->where('start_date', '<=', $date->endOfMonth())
                      ->where(function ($q) use ($date) {
                          $q->where('end_date', '>=', $date->startOfMonth())
                            ->orWhereNull('end_date');
                      });
            })
            ->with('additionType')
            ->get();

        $groupedAdditions = $additions->groupBy('addition_type_id');

        foreach ($groupedAdditions as $typeId => $items) {
            $firstItem = $items->first();
            $journalItems->push([
                'code' => $firstItem->additionType->id ?? 'A-'.$typeId,
                'description' => $firstItem->additionType->name ?? 'Unknown Addition',
                'count' => $items->unique('employee_id')->count(),
                'amount' => $items->sum('amount'),
                'type' => 'Addition'
            ]);
        }

        // 4. Net Pay
        $journalItems->push([
            'code' => 'NET',
            'description' => 'Net Pay',
            'count' => $payrollRecords->count(),
            'amount' => $payrollRecords->sum('net_salary'),
            'type' => 'Net Pay'
        ]);

        $journalItems = $journalItems->sortBy('description')->values();

        return [
            'report_title' => 'Payroll Journal Report',
            'generated_date' => now()->format('Y-m-d H:i:s'),
            'period' => $year . '-' . $this->getMonthName($monthNumber),
            'journal_items' => $journalItems,
            'grand_total' => $journalItems->sum('amount')
        ];
    }

    public function generatePensionerReport($filters = [])
    {
        $query = \App\Models\Pensioner::with(['bank', 'department', 'rank', 'gradeLevel', 'step']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        $pensioners = $query->orderBy('full_name')->get();

        $totalPensionAmount = $pensioners->sum('pension_amount');
        $totalGratuityAmount = $pensioners->sum('gratuity_amount');

        $reportData = [
            'report_title' => 'Pensioner Report with Bank Details',
            'generated_date' => now()->format('Y-m-d H:i:s'),
            'total_pensioners' => $pensioners->count(),
            'total_pension_amount' => $totalPensionAmount,
            'total_gratuity_amount' => $totalGratuityAmount,
            'pensioners' => [],
        ];

        foreach ($pensioners as $pensioner) {
            $fullName = $pensioner->full_name ?? trim(($pensioner->first_name ?? '') . ' ' . ($pensioner->middle_name ?? '') . ' ' . ($pensioner->surname ?? ''));

            $reportData['pensioners'][] = [
                'full_name' => $fullName,
                'staff_no' => $pensioner->employee ? $pensioner->employee->staff_no : 'N/A',
                'department' => $pensioner->department->department_name ?? 'N/A',
                'rank' => $pensioner->rank->name ?? 'N/A',
                'grade_level' => $pensioner->gradeLevel->name ?? 'N/A',
                'step' => $pensioner->step->name ?? 'N/A',
                'date_of_retirement' => $pensioner->date_of_retirement ? $pensioner->date_of_retirement->format('Y-m-d') : 'N/A',
                'retirement_type' => $pensioner->retirement_type ?? 'N/A',
                'years_of_service' => $pensioner->years_of_service ?? 'N/A',
                'pension_amount' => (float) ($pensioner->pension_amount ?? 0),
                'gratuity_amount' => (float) ($pensioner->gratuity_amount ?? 0),
                'bank_name' => $pensioner->bank->bank_name ?? 'N/A',
                'account_number' => $pensioner->account_number ?? 'N/A',
                'account_name' => $pensioner->account_name ?? 'N/A',
                'phone_number' => $pensioner->phone_number ?? 'N/A',
                'status' => $pensioner->status ?? 'N/A',
                'is_gratuity_paid' => $pensioner->is_gratuity_paid ? 'Yes' : 'No',
            ];
        }

        return $reportData;
    }

    public function generateDuplicateBeneficiaryReport()
    {
        // Fetch all active/suspended/retired employees with their bank details and biometric data
        $employees = Employee::with(['bank', 'biometricData', 'department'])
            ->get();

        // Fetch all pensioners with their bank details
        $pensioners = \App\Models\Pensioner::with(['bank', 'department'])
            ->get();

        // normalize data into a common structure
        $beneficiaries = collect();

        foreach ($employees as $employee) {
            $accountNo = $employee->bank ? trim($employee->bank->account_no) : null;
            $nin = $employee->biometricData ? trim($employee->biometricData->nin) : ($employee->nin ? trim($employee->nin) : null);
            
            if ($accountNo || $nin) {
                $beneficiaries->push([
                    'type' => 'Employee',
                    'id' => $employee->staff_no ?? $employee->employee_id,
                    'name' => $employee->full_name,
                    'department' => $employee->department->department_name ?? 'N/A',
                    'account_number' => $accountNo,
                    'bank_name' => $employee->bank->bank_name ?? 'N/A',
                    'nin' => $nin,
                    'status' => $employee->status,
                    'original_record' => $employee
                ]);
            }
        }

        foreach ($pensioners as $pensioner) {
            // Pensioner model has account_number directly
            $accountNo = $pensioner->account_number ? trim($pensioner->account_number) : null;
            
            if ($accountNo) {
                $beneficiaries->push([
                    'type' => 'Pensioner',
                    'id' => $pensioner->employee_id ?? $pensioner->id, // Use employee_id string if available
                    'name' => trim($pensioner->first_name . ' ' . $pensioner->middle_name . ' ' . $pensioner->surname),
                    'department' => $pensioner->department->department_name ?? 'N/A',
                    'account_number' => $accountNo,
                    'bank_name' => $pensioner->bank->bank_name ?? 'N/A', // Assuming relation exists or bank_name is stored
                    'nin' => null, // Pensioners don't have NIN in schema
                    'status' => $pensioner->status,
                    'original_record' => $pensioner
                ]);
            }
        }

        // Group by Account Number
        $duplicateAccounts = $beneficiaries
            ->filter(fn($b) => !empty($b['account_number']))
            ->groupBy('account_number')
            ->filter(fn($group) => $group->count() > 1)
            ->map(fn($group) => $group->values()->all())
            ->toArray();

        // Group by NIN
        $duplicateNins = $beneficiaries
            ->filter(fn($b) => !empty($b['nin']))
            ->groupBy('nin')
            ->filter(fn($group) => $group->count() > 1)
            ->map(fn($group) => $group->values()->all())
            ->toArray();

        return [
            'report_title' => 'Duplicate Beneficiary Report',
            'generated_date' => now()->format('Y-m-d H:i:s'),
            'total_duplicate_account_groups' => count($duplicateAccounts),
            'total_duplicate_nin_groups' => count($duplicateNins),
            'duplicate_accounts' => $duplicateAccounts,
            'duplicate_nins' => $duplicateNins
        ];
    }
    public function generateFullPayrollReport($filters = [])
    {
        $year = $filters['year'] ?? date('Y');
        $month = $filters['month'] ?? date('m');
        $monthNumber = $this->convertMonthToNumber($month);
        
        $monthName = $this->getMonthName($monthNumber);
        $period = "$year-$monthName";

        // Get all payroll records for the period
        $query = PayrollRecord::whereYear('payroll_month', $year)
            ->whereMonth('payroll_month', $monthNumber)
            ->with(['employee.department', 'employee.gradeLevel', 'employee.step']);

        // Apply Appointment Type Filter
        if (!empty($filters['appointment_type_id'])) {
            $appointmentTypeId = $filters['appointment_type_id'];
            $query->whereHas('employee', function($q) use ($appointmentTypeId) {
                $q->where('appointment_type_id', $appointmentTypeId);
            });
        }

        $payrollRecords = $query->get();

        // Collect all unique addition and deduction types (Master List)
        // We fetch ALL types to ensure columns appear even if not used in this specific month
        $additionTypes = \App\Models\AdditionType::pluck('name')->toArray();
        $deductionTypes = \App\Models\DeductionType::pluck('name')->toArray();
        
        // Also add unique Loan Types from the system to deduction types
        $loanTypes = \App\Models\Loan::distinct()->pluck('loan_type')->toArray();
        foreach ($loanTypes as $loanType) {
            if (!in_array($loanType, $deductionTypes)) {
                $deductionTypes[] = $loanType;
            }
        }
        
        // Ensure "Statutory/Other" exists
        if (!in_array('Statutory/Other', $deductionTypes)) {
            $deductionTypes[] = 'Statutory/Other';
        }

        // We need to fetch the actual breakdown for each record. 
        // Since the breakdown is often JSON or related models, let's fetch related deductions/additions for the period.
        // Optimization: Fetch all deductions/additions for these employees in this month
        
        // Define date range for the month
        $startDate = Carbon::createFromDate($year, $monthNumber, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $monthNumber, 1)->endOfMonth();

        // Process each record to build the data structure
        $processedRecords = [];

        foreach ($payrollRecords as $record) {
            $employeeId = $record->employee_id;
            
            // 1. Get Regular Deductions (Instructions active for this period)
            // Note: This relies on the assumption that if a Deduction instruction exists active for the period, it was applied.
            // This is how the system currently estimates historical non-loan deductions.
            $deductions = \App\Models\Deduction::where('employee_id', $employeeId)
                ->where('start_date', '<=', $endDate)
                ->where(function($q) use ($startDate) {
                    $q->whereNull('end_date')
                      ->orWhere('end_date', '>=', $startDate);
                })
                ->whereNull('loan_id') // Exclude deductions linked to loans to avoid potential double counting if we fetch LoanDeductions separately
                ->with('deductionType')
                ->get();

            // 2. Get Loan Deductions (Actual processed records for this month)
            // This captures loans processed by the Loan module
            $loanDeductions = \App\Models\LoanDeduction::where('employee_id', $employeeId)
                ->where('payroll_month', $period) // LoanDeduction stores 'YYYY-MonthName' e.g. '2025-January' or '2025-01'?
                // Let's check how PayrollController saves it: 'payroll_month' => $month (which is '2025-01')
                ->orWhere('payroll_month', $year . '-' . sprintf('%02d', $monthNumber))
                ->with('loan')
                ->get();

            // Get Additions
            $additions = \App\Models\Addition::where('employee_id', $employeeId)
                ->where('start_date', '<=', $endDate)
                ->where(function($q) use ($startDate) {
                    $q->whereNull('end_date')
                      ->orWhere('end_date', '>=', $startDate);
                })
                ->with('additionType')
                ->get();

            $recordAdditions = [];
            $recordDeductions = [];

            // Process Additions
            foreach ($additions as $add) {
                $name = $add->additionType->name ?? 'Unknown';
                if (!in_array($name, $additionTypes)) {
                    $additionTypes[] = $name;
                }
                $recordAdditions[$name] = ($recordAdditions[$name] ?? 0) + $add->amount;
            }

            // Process Regular Deductions
            foreach ($deductions as $ded) {
                $name = $ded->deductionType->name ?? $ded->deduction_type ?? 'Unknown';
                if (!in_array($name, $deductionTypes)) {
                    $deductionTypes[] = $name;
                }
                $recordDeductions[$name] = ($recordDeductions[$name] ?? 0) + $ded->amount;
            }

            // Process Loan Deductions
            foreach ($loanDeductions as $loanDed) {
                $name = $loanDed->loan->loan_type ?? 'Loan Deduction';
                if (!in_array($name, $deductionTypes)) {
                    $deductionTypes[] = $name;
                }
                $recordDeductions[$name] = ($recordDeductions[$name] ?? 0) + $loanDed->amount_deducted;
            }

            // Note: Statutory deductions (PAYE, etc) are calculated on the fly and not stored in tables.
            // If they are not in Deduction table, we must calculate them or find difference.
            // Let's check if total_deductions from DB > sum of captured deductions.
            $capturedDeductionsTotal = array_sum($recordDeductions);
            $difference = $record->total_deductions - $capturedDeductionsTotal;

            if ($difference > 0.01) {
                // Assign difference to "Statutory/Other" or try to breakdown if possible.
                // For now, let's call it "Statutory/Other Deductions" to ensure totals match.
                $name = 'Statutory/Other';
                if (!in_array($name, $deductionTypes)) {
                    $deductionTypes[] = $name;
                }
                $recordDeductions[$name] = ($recordDeductions[$name] ?? 0) + $difference;
            }

            $processedRecords[] = [
                'staff_no' => $record->employee->staff_no ?? $record->employee->employee_id,
                'name' => trim($record->employee->first_name . ' ' . $record->employee->surname),
                'department' => $record->employee->department->department_name ?? 'N/A',
                'rank' => ($record->employee->gradeLevel->name ?? '') . '/' . ($record->employee->step->name ?? ''),
                'basic_salary' => $record->basic_salary,
                'additions' => $recordAdditions,
                'total_additions' => $record->total_additions,
                'gross_salary' => $record->basic_salary + $record->total_additions,
                'deductions' => $recordDeductions,
                'total_deductions' => $record->total_deductions,
                'net_salary' => $record->net_salary
            ];
        }

        return [
            'report_title' => 'Full Payroll Report',
            'generated_date' => now()->format('Y-m-d H:i:s'),
            'period' => $period,
            'addition_types' => $additionTypes,
            'deduction_types' => $deductionTypes,
            'payroll_records' => $processedRecords
        ];
    }

    public function generateEmployeeExportReport($filters = [])
    {
        $query = Employee::with([
            'department', 'state', 'lga', 'ward', 'appointmentType',
            'gradeLevel', 'step', 'rank', 'cadre', 'nextOfKin', 'bank'
        ]);

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['appointment_type_id'])) {
            $query->where('appointment_type_id', $filters['appointment_type_id']);
        }

        $employees = $query->orderBy('employee_id')->get();

        $employeesData = [];
        $nextOfKinData = [];
        $banksData = [];

        foreach ($employees as $emp) {
            $employeesData[] = [
                'employee_id' => $emp->employee_id,
                'staff_no' => $emp->staff_no,
                'first_name' => $emp->first_name,
                'surname' => $emp->surname,
                'middle_name' => $emp->middle_name,
                'gender' => $emp->gender,
                'date_of_birth' => $emp->date_of_birth,
                'state_id' => $emp->state ? $emp->state->state_name : $emp->state_id,
                'lga_id' => $emp->lga ? $emp->lga->lga_name : $emp->lga_id,
                'ward_id' => $emp->ward ? $emp->ward->ward_name : $emp->ward_id,
                'nationality' => $emp->nationality,
                'nin' => $emp->nin,
                'mobile_no' => $emp->mobile_no,
                'email' => $emp->email,
                'address' => $emp->address,
                'date_of_first_appointment' => $emp->date_of_first_appointment,
                'department_id' => $emp->department ? $emp->department->department_name : $emp->department_id,
                'status' => $emp->status,
                'appointment_type_id' => $emp->appointmentType ? $emp->appointmentType->name : $emp->appointment_type_id,
                'photo_path' => $emp->photo_path,
                'pay_point' => $emp->pay_point,
                'grade_level_id' => $emp->gradeLevel ? $emp->gradeLevel->name : $emp->grade_level_id,
                'step_id' => $emp->step ? $emp->step->name : $emp->step_id,
                'rank_id' => $emp->rank ? $emp->rank->name : $emp->rank_id,
                'cadre_id' => $emp->cadre ? $emp->cadre->name : $emp->cadre_id,
                'expected_next_promotion' => $emp->expected_next_promotion,
                'expected_retirement_date' => $emp->expected_retirement_date,
                'highest_certificate' => $emp->highest_certificate,
                'amount' => $emp->amount,
                'casual_start_date' => $emp->contract_start_date,
                'casual_end_date' => $emp->contract_end_date,
            ];

            if ($emp->nextOfKin) {
                $nextOfKinData[] = [
                    'employee_id' => $emp->employee_id,
                    'name' => $emp->nextOfKin->name,
                    'relationship' => $emp->nextOfKin->relationship,
                    'mobile_no' => $emp->nextOfKin->mobile_no,
                    'address' => $emp->nextOfKin->address,
                    'occupation' => $emp->nextOfKin->occupation,
                    'place_of_work' => $emp->nextOfKin->place_of_work,
                ];
            }

            if ($emp->bank) {
                $banksData[] = [
                    'employee_id' => $emp->employee_id,
                    'bank_name' => $emp->bank->bank_name,
                    'bank_code' => $emp->bank->bank_code,
                    'account_name' => $emp->bank->account_name,
                    'account_no' => $emp->bank->account_no,
                ];
            }
        }

        return [
            'report_title' => 'Employee Export Report',
            'generated_date' => now()->format('Y-m-d H:i:s'),
            'total_employees' => count($employeesData),
            'employees' => $employeesData,
            'next_of_kin' => $nextOfKinData,
            'banks' => $banksData,
        ];
    }

    public function generatePensionExportReport($filters = [])
    {
        $query = \App\Models\Pensioner::with(['bank', 'department', 'gradeLevel', 'employee']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        $pensioners = $query->orderBy('full_name')->get();

        $pensionersData = [];

        foreach ($pensioners as $p) {
            $pensionersData[] = [
                'employee_id' => $p->employee_id,
                'staff_number' => $p->employee ? $p->employee->staff_no : 'N/A',
                'first_name' => $p->first_name,
                'middle_name' => $p->middle_name,
                'surname' => $p->surname,
                'department' => $p->department->department_name ?? 'N/A',
                'retired_grade_level' => $p->gradeLevel->name ?? 'N/A',
                'new_pension' => (float) ($p->pension_amount ?? 0),
                'bank_name' => $p->bank->bank_name ?? 'N/A',
                'bank_code' => $p->bank->bank_code ?? 'N/A',
                'account_number' => $p->account_number ?? 'N/A',
                'account_name' => $p->account_name ?? 'N/A',
            ];
        }

        return [
            'report_title' => 'Pension Export Report',
            'generated_date' => now()->format('Y-m-d H:i:s'),
            'total_pensioners' => count($pensionersData),
            'pensioners' => $pensionersData,
        ];
    }
}
