<?php

namespace App\Services;

use App\Models\Employee;
use Carbon\Carbon;

class EmployeeReportService
{
    public function generateEmployeeReportData(Employee $employee, string $reportType): array
    {
        $baseData = [
            'employee_info' => $this->getEmployeeInfo($employee),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'report_type' => $reportType
        ];

        switch ($reportType) {
            case 'comprehensive':
                return array_merge($baseData, [
                    'disciplinary_actions' => $this->getDisciplinaryData($employee),
                    'payroll_records' => $this->getPayrollData($employee),
                    'deductions' => $this->getDeductionsData($employee),
                    'additions' => $this->getAdditionsData($employee),
                    'retirement_info' => $this->getRetirementInfo($employee),
                    'statistics' => $this->getStatistics($employee)
                ]);

            case 'disciplinary':
                return array_merge($baseData, [
                    'disciplinary_records' => $this->getDisciplinaryData($employee)
                ]);

            case 'payroll':
                return array_merge($baseData, [
                    'payroll_records' => $this->getPayrollData($employee),
                    'deductions' => $this->getDeductionsData($employee),
                    'additions' => $this->getAdditionsData($employee)
                ]);

            case 'retirement':
                return array_merge($baseData, [
                    'retirement_info' => $this->getRetirementInfo($employee),
                    'service_summary' => $this->getServiceSummary($employee)
                ]);

            default:
                return $baseData;
        }
    }

    private function getEmployeeInfo(Employee $employee): array
    {
        return [
            'employee_id' => $employee->employee_id,
            'first_name' => $employee->first_name,
            'surname' => $employee->surname,
            'middle_name' => $employee->middle_name,
            'full_name' => trim($employee->first_name . ' ' . $employee->middle_name . ' ' . $employee->surname),
            'gender' => $employee->gender,
            'date_of_birth' => $employee->date_of_birth,
            'state_of_origin' => $employee->state_of_origin,
            'lga' => $employee->lga,
            'nationality' => $employee->nationality,
            'nin' => $employee->nin,
            'mobile_no' => $employee->mobile_no,
            'email' => $employee->email,
            'address' => $employee->address,
            'date_of_first_appointment' => $employee->date_of_first_appointment,
            'cadre' => optional($employee->cadre)->name,
            'reg_no' => $employee->reg_no,
            'grade_level' => optional($employee->gradeLevel)->name,
            'department' => optional($employee->department)->department_name,
            'expected_next_promotion' => $employee->expected_next_promotion,
            'expected_retirement_date' => $employee->expected_retirement_date,
            'status' => $employee->status,
            'highest_certificate' => $employee->highest_certificate,
            'grade_level_limit' => $employee->grade_level_limit,
            'appointment_type' => $employee->appointmentType->name ?? null,
            'service_years' => $employee->years_of_service,
        ];
    
    }

    private function getDisciplinaryData(Employee $employee): array
    {
        return $employee->disciplinaryRecords()->get()->map(function ($record) {
            return [
                'action_date' => $record->action_date,
                'action_type' => $record->action_type,
                'description' => $record->description,
                'initiated_by' => $record->initiated_by,
                'status' => $record->status,
                'resolution' => $record->resolution
            ];
        })->toArray();
    }

    private function getPayrollData(Employee $employee): array
    {
        return $employee->payrollRecords()
            ->orderBy('payment_date', 'desc')
            ->limit(12)
            ->get()
            ->map(function ($record) {
                return [
                    'payroll_id' => $record->payroll_id,
                    'basic_salary' => $record->basic_salary,
                    'total_deductions' => $record->total_deductions,
                    'total_additions' => $record->total_additions,
                    'net_salary' => $record->net_salary,
                    'payment_date' => $record->payment_date,
                    'status' => $record->status
                ];
            })->toArray();
    }

    private function getDeductionsData(Employee $employee): array
    {
        return $employee->deductions()
            ->whereHas('employee', function ($query) {
            $query->where('status', 'active');
            })
            ->get()
            ->map(function ($deduction) {
                return [
                    'deduction_type' => $deduction->name_type,
                    'amount' => $deduction->amount,
                    'frequency' => $deduction->period,
                    'start_date' => $deduction->start_date,
                    'end_date' => $deduction->end_date,
                    'description' => $deduction->description
                ];
            })->toArray();
    }

    private function getAdditionsData(Employee $employee): array
    {
        return $employee->additions()
            ->whereHas('employee', function ($query) {
            $query->where('status', 'active');
            })
            ->get()
            ->map(function ($addition) {
                return [
                    'addition_type' => $addition->name_type,
                    'amount' => $addition->amount,
                    'frequency' => $addition->period,
                    'start_date' => $addition->start_date,
                    'end_date' => $addition->end_date,
                    'description' => $addition->description
                ];
            })->toArray();
    }

    private function getPromotionData(Employee $employee): array
    {
        return $employee->promotionHistory()
            ->orderBy('effective_date', 'desc')
            ->get()
            ->map(function ($promotion) {
                return [
                    'promotion_date' => $promotion->promotion_date,
                    'from_grade' => $promotion->from_grade,
                    'to_grade' => $promotion->to_grade,
                    'effective_date' => $promotion->effective_date,
                    'approving_authority' => $promotion->approving_authority
                ];
            })->toArray();
    }

    private function getRetirementInfo(Employee $employee): array
    {
        $yearsToRetirement = $employee->expected_retirement_date 
            ? abs(round(now()->floatDiffInYears($employee->expected_retirement_date, false), 1))
            : null;

        return [
            'expected_retirement_date' => $employee->expected_retirement_date,
            'years_to_retirement' => $yearsToRetirement,
            'service_years' => $employee->date_of_first_appointment
                ? round(\Carbon\Carbon::parse($employee->date_of_first_appointment)->floatDiffInYears(now()))
                : null,
            'pension_scheme' => 'Contributory Pension Scheme', // Default
            'estimated_gratuity' => optional($employee->retirement)->gratuity_amount,
            'retirement_status' => $yearsToRetirement <= 2 ? 'retired' : 'active'
        ];
    }   
    private function getStatistics(Employee $employee): array
    {
        // Consider employee if status is 'Active' or 'Suspended'
        if (!in_array($employee->status, ['Active', 'Suspended'])) {
            return [];
        }

        $totalDeductions = $employee->deductions()
            ->where('status', 'active')
            ->sum('amount');

        $totalAllowances = $employee->additions()
            ->where('status', 'active')
            ->sum('amount');

        $lifetimeEarnings = $employee->payrollHistory()
            ->sum('gross_salary');

        return [
            'total_service_years' => $employee->service_years,
            'current_grade_level' => $employee->current_grade_level,
            'total_lifetime_earnings' => $lifetimeEarnings,
            'total_monthly_deductions' => $totalDeductions,
            'total_monthly_allowances' => $totalAllowances,
            'active_disciplinary_cases' => $employee->disciplinaryRecords()
                ->where('status', 'active')
                ->count(),
            'last_promotion_date' => $employee->promotionHistory()
                ->orderBy('effective_date', 'desc')
                ->first()?->effective_date?->format('Y-m-d')
        ];
    }

    private function getServiceSummary(Employee $employee): array
    {
        return [
            'total_service_years' => $employee->service_years,
            'disciplinary_actions' => $employee->disciplinaryRecords()->count(),
            
        ];
    }

    private function calculateEstimatedGratuity(Employee $employee): float
    {
        // Basic gratuity calculation (this would need to be adjusted based on actual formula)
        $monthsOfService = $employee->service_years * 12;
        return ($employee->basic_salary * $monthsOfService) / 12;
    }
}
