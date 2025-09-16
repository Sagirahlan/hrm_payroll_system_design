<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Deduction;
use App\Models\Addition;
use Carbon\Carbon;

class PayrollCalculationService
{
    public function calculatePayroll(Employee $employee, string $month): array
    {
        $gradeLevel = $employee->relationLoaded('gradeLevel')
            ? $employee->gradeLevel
            : $employee->gradeLevel()->first();

        if (!$gradeLevel) {
            // Handle case where employee has no grade level
            return [
                'basic_salary'     => 0,
                'total_deductions' => 0,
                'total_additions'  => 0,
                'net_salary'       => 0,
                'deductions'       => [],
                'additions'        => [],
            ];
        }

        $step = $gradeLevel->steps()->first();

        if (!$step) {
            // Handle case where grade level has no steps
            return [
                'basic_salary'     => 0,
                'total_deductions' => 0,
                'total_additions'  => 0,
                'net_salary'       => 0,
                'deductions'       => [],
                'additions'        => [],
            ];
        }

        $basicSalary = $step->basic_salary;
        $payrollDate = Carbon::parse($month . '-01');

        $totalDeductions = 0;
        $totalAdditions = 0;
        $deductionRecords = [];
        $additionRecords = [];

        // Statutory deductions from grade level
        foreach ($gradeLevel->deductionTypes as $deductionType) {
            if ($deductionType->is_statutory) {
                $amount = ($deductionType->pivot->percentage / 100) * $basicSalary;
                $totalDeductions += $amount;
                $deductionRecords[] = [
                    'type' => 'deduction',
                    'name_type' => $deductionType->name,
                    'amount' => $amount,
                    'frequency' => 'Monthly', // Assuming statutory deductions are monthly
                ];
            }
        }

        // Statutory additions from grade level
        foreach ($gradeLevel->additionTypes as $additionType) {
            if ($additionType->is_statutory) {
                $amount = ($additionType->pivot->percentage / 100) * $basicSalary;
                $totalAdditions += $amount;
                $additionRecords[] = [
                    'type' => 'addition',
                    'name_type' => $additionType->name,
                    'amount' => $amount,
                    'frequency' => 'Monthly', // Assuming statutory additions are monthly
                ];
            }
        }

        // Non-statutory deductions for the employee
        $nonStatutoryDeductions = Deduction::where('employee_id', $employee->employee_id)
            ->where('start_date', '<=', $payrollDate)
            ->where(function ($query) use ($payrollDate) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $payrollDate);
            })->get();

        foreach ($nonStatutoryDeductions as $deduction) {
            $deductionAmount = 0;
            if ($deduction->amount_type === 'percentage') {
                $deductionAmount = ($deduction->amount / 100) * $basicSalary;
            } else {
                $deductionAmount = $deduction->amount;
            }
            $totalDeductions += $deductionAmount;
            $deductionRecords[] = [
                'type' => 'deduction',
                'name_type' => $deduction->deduction_type,
                'amount' => $deductionAmount,
                'frequency' => $deduction->deduction_period,
            ];
        }

        // Non-statutory additions for the employee
        $nonStatutoryAdditions = Addition::where('employee_id', $employee->employee_id)
            ->where('start_date', '<=', $payrollDate)
            ->where(function ($query) use ($payrollDate) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $payrollDate);
            })->get();

        foreach ($nonStatutoryAdditions as $addition) {
            $additionAmount = 0;
            if ($addition->amount_type === 'percentage') {
                $additionAmount = ($addition->amount / 100) * $basicSalary;
            } else {
                $additionAmount = $addition->amount;
            }
            $totalAdditions += $additionAmount;
            $additionRecords[] = [
                'type' => 'addition',
                'name_type' => $addition->addition_type,
                'amount' => $additionAmount,
                'frequency' => $addition->addition_period,
            ];
        }

        $netSalary = $basicSalary - $totalDeductions + $totalAdditions;

        return [
            'basic_salary'     => $basicSalary,
            'total_deductions' => $totalDeductions,
            'total_additions'  => $totalAdditions,
            'net_salary'       => $netSalary,
            'deductions'       => $deductionRecords,
            'additions'        => $additionRecords,
        ];
    }
}