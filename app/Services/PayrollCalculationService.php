<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Deduction;
use App\Models\Addition;
use Carbon\Carbon;

class PayrollCalculationService
{
    public function calculatePayroll(Employee $employee, string $month, bool $isSuspended = false): array
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

        $step = $gradeLevel->steps()->orderBy('name')->first();

        if (!$step || !$step->basic_salary) {
            // Handle case where grade level has no steps or basic salary
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
        
        // For suspended employees, use half of the basic salary for calculations
        if ($isSuspended) {
            $basicSalary = $basicSalary / 2;
        }
        
        $payrollDate = Carbon::parse($month . '-01');

        $totalDeductions = 0;
        $totalAdditions = 0;
        $deductionRecords = [];
        $additionRecords = [];

        // Statutory deductions from grade level
        foreach ($gradeLevel->deductionTypes as $deductionType) {
            if ($deductionType->is_statutory) {
                // Calculate statutory deduction based on the actual basic salary (already halved for suspended)
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

        // Employee-specific deductions - these amounts are already stored with the correct values
        // (halved for suspended employees when they were created)
        $employeeDeductions = Deduction::where('employee_id', $employee->employee_id)
            ->where('start_date', '<=', $payrollDate)
            ->where(function ($query) use ($payrollDate) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $payrollDate);
            })->get();

        foreach ($employeeDeductions as $deduction) {
            $deductionAmount = $deduction->amount; // Use the stored amount (already calculated correctly)
            $totalDeductions += $deductionAmount;
            $deductionRecords[] = [
                'type' => 'deduction',
                'name_type' => $deduction->deduction_type,
                'amount' => $deductionAmount,
                'frequency' => $deduction->deduction_period,
            ];
        }

        // Employee-specific additions
        $employeeAdditions = Addition::where('employee_id', $employee->employee_id)
            ->where('start_date', '<=', $payrollDate)
            ->where(function ($query) use ($payrollDate) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $payrollDate);
            })->get();

        foreach ($employeeAdditions as $addition) {
            $additionAmount = $addition->amount; // Use the calculated amount from the model
            $totalAdditions += $additionAmount;
            $additionRecords[] = [
                'type' => 'addition',
                'name_type' => $addition->addition_type,
                'amount' => $additionAmount,
                'frequency' => $addition->addition_period,
            ];
        }

        // Calculate net salary: same formula for both active and suspended
        // Net salary = Basic Salary - Total Deductions + Total Additions
        // For suspended employees, we already halved the basic salary above
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