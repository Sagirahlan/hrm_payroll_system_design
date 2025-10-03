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

        $step = $employee->step;

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
        $payrollStart = Carbon::parse($month . '-01');
        $payrollEnd = Carbon::parse($month . '-01')->endOfMonth();

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

        // Employee-specific deductions
        $employeeDeductions = Deduction::where('employee_id', $employee->employee_id)
            ->where('start_date', '<=', $payrollEnd)
            ->where(function ($query) use ($payrollStart) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $payrollStart);
            })->with('deductionType')->get();

        foreach ($employeeDeductions as $deduction) {
            // Calculate deduction amount based on the specific step's basic salary
            if ($deduction->amount_type === 'percentage') {
                // For percentage-based deductions, calculate amount based on step's basic salary
                $step = $employee->relationLoaded('step') ? $employee->step : $employee->step()->first();
                if ($step && $step->basic_salary) {
                    $deductionAmount = ($deduction->amount / 100) * $step->basic_salary;
                    
                    // For suspended employees, halve the percentage-based deduction
                    if ($isSuspended) {
                        $deductionAmount = $deductionAmount / 2;
                    }
                } else {
                    $deductionAmount = 0; // If no step or basic salary, set amount to 0
                }
            } else {
                // For fixed amount deductions, use the stored amount
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

        // Employee-specific additions
        $employeeAdditions = Addition::where('employee_id', $employee->employee_id)
            ->where('start_date', '<=', $payrollEnd)
            ->where(function ($query) use ($payrollStart) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $payrollStart);
            })->with('additionType')->get();

        foreach ($employeeAdditions as $addition) {
            // Check if this addition should be applied for the current payroll month based on period
            $shouldApply = false;
            
            switch ($addition->addition_period) {
                case 'Monthly':
                    // Monthly additions apply every month within the date range
                    $shouldApply = true;
                    break;
                case 'OneTime':
                    // OneTime additions should only apply in the month that includes the start_date
                    $additionStartDate = Carbon::parse($addition->start_date);
                    // Check if the addition's start date falls within the payroll month
                    $shouldApply = (
                        $additionStartDate->format('Y-m') === $payrollStart->format('Y-m')
                    );
                    break;
                case 'Perpetual':
                    // Perpetual additions apply every month while within date range
                    $shouldApply = true;
                    break;
                default:
                    $shouldApply = false;
                    break;
            }
            
            if ($shouldApply) {
                // Calculate addition amount based on the specific step's basic salary
                if ($addition->amount_type === 'percentage') {
                    // For percentage-based additions, calculate amount based on step's basic salary
                    $step = $employee->relationLoaded('step') ? $employee->step : $employee->step()->first();
                    if ($step && $step->basic_salary) {
                        $additionAmount = ($addition->amount / 100) * $step->basic_salary;
                        
                        // For suspended employees, halve the percentage-based addition
                        if ($isSuspended) {
                            $additionAmount = $additionAmount / 2;
                        }
                    } else {
                        $additionAmount = 0; // If no step or basic salary, set amount to 0
                    }
                } else {
                    // For fixed amount additions, use the stored amount
                    $additionAmount = $addition->amount;
                }
                
                $totalAdditions += $additionAmount;
                $additionRecords[] = [
                    'type' => 'addition',
                    'name_type' => $addition->additionType ? $addition->additionType->name : $addition->addition_type,
                    'amount' => $additionAmount,
                    'frequency' => $addition->addition_period,
                ];
            }
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