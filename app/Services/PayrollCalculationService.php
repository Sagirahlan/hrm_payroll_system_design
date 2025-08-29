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

        $basicSalary = optional($gradeLevel)->basic_salary ?? 0;

        $payrollDate = Carbon::parse($month . '-01');

        $deductions = Deduction::where('employee_id', $employee->employee_id)
            ->where('start_date', '<=', $payrollDate)
            ->where(function ($query) use ($payrollDate) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $payrollDate);
            })->get();

        $additions = Addition::where('employee_id', $employee->employee_id)
            ->where('start_date', '<=', $payrollDate)
            ->where(function ($query) use ($payrollDate) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $payrollDate);
            })->get();

        $totalDeductions = $deductions->sum('amount');
        $totalAdditions = $additions->sum('amount');
        $netSalary = $basicSalary - $totalDeductions + $totalAdditions;

        $additionRecords = $additions->map(function ($addition) {
                return [
                    'type' => 'addition',
                    'name_type' => $addition->name_type,
                    'amount' => $addition->amount,
                    'frequency' => $addition->period,
                ];
            })->toArray();

            $deductionRecords = $deductions->map(function ($deduction) {
            return [
            'type' => 'deduction',
            'name_type' => $deduction->name_type,
            'amount' => $deduction->amount,
           'frequency' => $deduction->period,
                ];
        })->toArray();

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
