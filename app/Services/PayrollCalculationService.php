<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Deduction;
use App\Models\Addition;
use App\Models\Loan;
use App\Models\LoanDeduction;
use Carbon\Carbon;

class PayrollCalculationService
{
    public function calculatePayroll(Employee $employee, string $month, bool $isSuspended = false): array
    {
        // Check if employee is a contract employee using the new method
        $isContractEmployee = $employee->isContractEmployee();
        
        $basicSalary = 0;
        
        if ($isContractEmployee) {
            // For contract employees, use the amount field instead of grade level step
            $basicSalary = $employee->amount ?: 0;
        } else {
            // For permanent/temporary employees, use grade level step
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
        }
        
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

        // Statutory deductions from grade level (only for non-contract employees)
        if (!$isContractEmployee && $gradeLevel) {
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
        }

        // Statutory additions from grade level (only for non-contract employees)
        if (!$isContractEmployee && $gradeLevel) {
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
        }

        // We will accumulate all pension-related amounts to add to RSA balance once at the end
        $totalPensionAmount = 0;

        // Process pension amounts from statutory deductions (only for non-contract employees)
        if (!$isContractEmployee) {
            foreach ($deductionRecords as $record) {
                if (stripos($record['name_type'], 'Pension') !== false) {
                    $totalPensionAmount += $record['amount'];
                }
            }
        }

        // Handle Loan Deductions
        $activeLoans = Loan::where('employee_id', $employee->employee_id)
            ->where('status', 'active')
            ->get();

        foreach ($activeLoans as $loan) {
            // Check if loan deduction has already been processed for this payroll month
            $existingDeduction = \App\Models\LoanDeduction::where('loan_id', $loan->loan_id)
                ->where('payroll_month', $month)
                ->first();

            if ($existingDeduction) {
                // This loan deduction has already been processed for this month, skip it
                continue;
            }
            
            // Check if there's already a deduction record for this loan (indicating it should be processed through the deduction system)
            $existingDeductionRecord = \App\Models\Deduction::where('loan_id', $loan->loan_id)
                ->where('employee_id', $employee->employee_id)
                ->first();
                
            if ($existingDeductionRecord) {
                // There's already a deduction record for this loan, so it will be processed in the deduction section
                // Skip processing it here to avoid double processing
                continue;
            }

            // Verify that the loan start date is on or before the end of the current payroll month
            // If loan starts in same month as payroll, it should be processed
            $payrollMonthStart = Carbon::parse($month . '-01');
            $payrollMonthEnd = Carbon::parse($month . '-01')->endOfMonth();
            $loanStartDate = Carbon::parse($loan->start_date);
            
            if ($loanStartDate->gt($payrollMonthEnd)) {
                // Loan starts after this payroll month ends, skip it for this payroll
                continue;
            }

            // Check if the loan has been fully repaid or completed before processing
            // Calculate actual months passed since loan start to determine if term is reached
            $loanStart = Carbon::parse($loan->start_date);
            $currentMonth = Carbon::parse($month . '-01');
            
            // Calculate how many months have passed since the loan started (inclusive)
            // diffInMonths gives full months difference: Nov 2025 to Nov 2025 = 0, Nov 2025 to Dec 2025 = 1, etc.
            // So we add 1 to represent the current month as the nth month since start
            $monthsSinceStart = $loanStart->diffInMonths($currentMonth, false) + 1;
            
            if ($loan->remaining_balance <= 0 || $monthsSinceStart > $loan->total_months || $loan->status === 'completed') {
                // Loan has been fully repaid, exceeded its term, or marked as completed - update status if needed
                if ($loan->status !== 'completed') {
                    $loan->status = 'completed';
                    $loan->end_date = Carbon::now();
                    $loan->save();
                }
                continue; // Skip this loan as it's completed
            }

            if ($loan->remaining_balance > 0) {
                // Calculate deduction amount based on monthly deduction amount if set, otherwise calculate from percentage
                $deductionAmount = $loan->monthly_deduction > 0 ? $loan->monthly_deduction : ($loan->monthly_percentage / 100) * $basicSalary;
                
                // Ensure deduction doesn't exceed remaining balance
                $deductionAmount = min($deductionAmount, $loan->remaining_balance);

                // Loan deductions continue at full amount even during suspension
                // if ($isSuspended) {
                //     $deductionAmount /= 2;
                // }

                // Calculate the maximum total deductions allowed to prevent negative net pay
                $maxTotalDeductionsAllowed = $basicSalary + $totalAdditions;
                
                // Check if adding this loan deduction would exceed the max allowed deductions
                $projectedTotalDeductions = $totalDeductions + $deductionAmount;
                
                if ($projectedTotalDeductions > $maxTotalDeductionsAllowed) {
                    // Adjust the loan deduction amount to not exceed the limit
                    $availableForDeduction = max(0, $maxTotalDeductionsAllowed - $totalDeductions);
                    $deductionAmount = $availableForDeduction;
                }

                $totalDeductions += $deductionAmount;
                $deductionRecords[] = [
                    'type' => 'deduction',
                    'name_type' => $loan->loan_type,
                    'amount' => $deductionAmount,
                    'frequency' => 'Monthly',
                ];

                // Update loan details
                $loan->total_repaid += $deductionAmount;
                $loan->remaining_balance -= $deductionAmount;

                // Check again if loan is now fully repaid after deduction
                if ($loan->remaining_balance <= 0.01) { // Using small threshold to handle floating point precision
                    $loan->remaining_balance = 0;
                    $loan->status = 'completed';
                    $loan->end_date = Carbon::now();
                }
                
                // Update remaining months based on how many deductions have been processed
                // Count existing loan deductions to determine how many months have been completed
                $existingDeductionsCount = \App\Models\LoanDeduction::where('loan_id', $loan->loan_id)->count();
                $loan->remaining_months = max(0, $loan->total_months - ($existingDeductionsCount + 1));
                
                $loan->save();

                // Calculate months completed based on actual months passed
                $monthsCompleted = min($monthsSinceStart, $loan->total_months);
                
                // Additional check: if loan term has been reached, mark as completed
                if ($monthsSinceStart >= $loan->total_months) {
                    $loan->status = 'completed';
                    $loan->end_date = Carbon::now();
                    $loan->save(); // Save again after status update
                }

                // Create loan deduction record to track this deduction
                \App\Models\LoanDeduction::create([
                    'loan_id' => $loan->loan_id,
                    'employee_id' => $employee->employee_id,
                    'amount_deducted' => $deductionAmount,
                    'remaining_balance' => $loan->remaining_balance,
                    'month_number' => $monthsCompleted,
                    'payroll_month' => $month,
                    'deduction_date' => Carbon::now(),
                    'status' => 'completed',
                ]);
            }
        }

        // Employee-specific deductions (including loan-related ones that have already been created)
        $employeeDeductions = Deduction::where('employee_id', $employee->employee_id)
            ->where('start_date', '<=', $payrollEnd)
            ->where(function ($query) use ($payrollStart) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $payrollStart);
            })
            ->with('deductionType')
            ->get();

        foreach ($employeeDeductions as $deduction) {
            $deductionAmount = 0;
            $processedLoanDeduction = false; // Flag to track if this was a loan deduction that was processed
            
            // Check if this is a loan deduction
            if ($deduction->loan_id) {
                // This is a loan deduction - get the actual monthly amount from the loan record
                $loan = Loan::find($deduction->loan_id);
                if ($loan && $loan->status === 'active') {
                    // Verify that the loan start date is on or before the end of the current payroll month
                    // And that the loan end date is on or after the start of the current payroll month
                    $payrollMonthStart = Carbon::parse($month . '-01');
                    $payrollMonthEnd = Carbon::parse($month . '-01')->endOfMonth();
                    $loanStartDate = Carbon::parse($loan->start_date);
                    $loanEndDate = Carbon::parse($loan->end_date);
                    
                    // Check if the loan is active for this payroll month
                    if ($loanStartDate->lte($payrollMonthEnd) && $loanEndDate->gte($payrollMonthStart)) {
                        // Loan is active for this month - use the loan's monthly deduction amount
                        $deductionAmount = $loan->monthly_deduction;
                        
                        // Loan deductions continue at full amount even during suspension
                        // if ($isSuspended) {
                        //     $deductionAmount /= 2;
                        // }
                        
                        // Track this loan deduction and update the loan record
                        $processedLoanDeduction = true;
                    }
                    // If loan is not active for this month, $deductionAmount remains 0
                }
            } else {
                // Regular deduction processing
                if ($deduction->amount_type === 'percentage') {
                    // For contract employees, use their contract amount for percentage calculations
                    if ($isContractEmployee) {
                        $deductionAmount = ($deduction->amount / 100) * $employee->amount;
                        if ($isSuspended) {
                            $deductionAmount /= 2;
                        }
                    } else {
                        $step = $employee->relationLoaded('step') ? $employee->step : $employee->step()->first();
                        if ($step && $step->basic_salary) {
                            $deductionAmount = ($deduction->amount / 100) * $step->basic_salary;
                            if ($isSuspended) {
                                $deductionAmount /= 2;
                            }
                        }
                    }
                } else {
                    $deductionAmount = $deduction->amount;
                }
            }
            
            // Only add deduction if there's an amount to deduct
            if ($deductionAmount > 0) {
                // Calculate the maximum total deductions allowed to prevent negative net pay
                $maxTotalDeductionsAllowed = $basicSalary + $totalAdditions;
                
                // Check if adding this deduction would exceed the max allowed deductions
                $projectedTotalDeductions = $totalDeductions + $deductionAmount;
                
                if ($projectedTotalDeductions > $maxTotalDeductionsAllowed) {
                    // Adjust the deduction amount to not exceed the limit
                    $availableForDeduction = max(0, $maxTotalDeductionsAllowed - $totalDeductions);
                    $deductionAmount = $availableForDeduction;
                }
                
                $totalDeductions += $deductionAmount;
                $deductionRecords[] = [
                    'type' => 'deduction',
                    'name_type' => $deduction->deductionType ? $deduction->deductionType->name : $deduction->deduction_type,
                    'amount' => $deductionAmount,
                    'frequency' => $deduction->deduction_period,
                ];
                
                // If this is a pension-related deduction, accumulate the amount
                $deductionName = $deduction->deductionType ? $deduction->deductionType->name : $deduction->deduction_type;
                if (stripos($deductionName, 'Pension') !== false) {
                    $totalPensionAmount += $deductionAmount;
                }
                
                // If this was a processed loan deduction, update the loan record and create tracking record
                if ($processedLoanDeduction && isset($loan)) {
                    // Update loan details
                    $loan->total_repaid += $deductionAmount;
                    $loan->remaining_balance -= $deductionAmount;

                    // Check if loan is now fully repaid after deduction
                    if ($loan->remaining_balance <= 0.01) { // Using small threshold to handle floating point precision
                        $loan->remaining_balance = 0;
                        $loan->status = 'completed';
                        $loan->end_date = Carbon::now();
                    }
                    
                    // Additional check: if loan term has been reached, mark as completed
                    // Calculate actual months passed since loan start to determine if term is reached
                    $loanStart = Carbon::parse($loan->start_date);
                    $currentMonth = Carbon::parse($month . '-01');
                    $monthsSinceStart = $loanStart->diffInMonths($currentMonth, false) + 1;
                    if ($monthsSinceStart >= $loan->total_months) {
                        $loan->status = 'completed';
                        $loan->end_date = Carbon::now();
                    }
                    
                    // Update remaining months based on how many deductions have been processed
                    // Count existing loan deductions to determine how many months have been completed
                    $existingDeductionsCount = \App\Models\LoanDeduction::where('loan_id', $loan->loan_id)->count();
                    $loan->remaining_months = max(0, $loan->total_months - ($existingDeductionsCount + 1));
                    
                    $loan->save();

                    // Calculate actual months passed since loan start to determine month number for tracking
                    $monthsCompleted = min($monthsSinceStart, $loan->total_months);

                    // Create loan deduction record to track this deduction
                    \App\Models\LoanDeduction::create([
                        'loan_id' => $loan->loan_id,
                        'employee_id' => $employee->employee_id,
                        'amount_deducted' => $deductionAmount,
                        'remaining_balance' => $loan->remaining_balance,
                        'month_number' => $monthsCompleted,
                        'payroll_month' => $month,
                        'deduction_date' => Carbon::now(),
                        'status' => 'completed',
                    ]);
                }
            }
        }

        // Now update the RSA balance with the total pension amount after all deductions have been processed
        if ($totalPensionAmount > 0) {
            $currentRsaBalance = $employee->rsa_balance ?? 0;

            // Calculate government's contribution (10% of basic salary)
            $governmentContribution = $basicSalary * 0.10;

            // The $totalPensionAmount already contains the employee's contribution.
            // So, we add both to the RSA balance.
            $newRsaBalance = $currentRsaBalance + $totalPensionAmount + $governmentContribution;
            
            $employee->update(['rsa_balance' => $newRsaBalance]);
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
                    $additionStartDate = Carbon::parse($addition->start_date)->startOfDay();
                    $payrollMonthStart = Carbon::parse($month . '-01')->startOfMonth();
                    $payrollMonthEnd = Carbon::parse($month . '-01')->endOfMonth();
                    // Check if the addition's start date falls within the payroll month range
                    $shouldApply = (
                        $additionStartDate->between($payrollMonthStart, $payrollMonthEnd)
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
                    // For percentage-based additions, calculate amount based on employee type
                    if ($isContractEmployee) {
                        // For contract employees, use their contract amount for percentage calculations
                        $additionAmount = ($addition->amount / 100) * $employee->amount;
                        
                        // Additions continue at full amount even during suspension
                        // if ($isSuspended) {
                        //     $additionAmount = $additionAmount / 2;
                        // }
                    } else {
                        // For regular employees, use step's basic salary
                        $step = $employee->relationLoaded('step') ? $employee->step : $employee->step()->first();
                        if ($step && $step->basic_salary) {
                            $additionAmount = ($addition->amount / 100) * $step->basic_salary;
                            
                            // Additions continue at full amount even during suspension
                            // if ($isSuspended) {
                            //     $additionAmount = $additionAmount / 2;
                            // }
                        } else {
                            $additionAmount = 0; // If no step or basic salary, set amount to 0
                        }
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
        
        // Final safety check: ensure net salary is never negative
        // If net salary would be negative, we reduce total deductions to ensure net salary is zero
        if ($netSalary < 0) {
            // Calculate maximum allowable deductions to have net salary of zero
            $maxAllowableDeductions = max(0, $basicSalary + $totalAdditions);
            
            // Adjust total deductions to not exceed the maximum allowable
            $totalDeductions = $maxAllowableDeductions;
            
            // Recalculate net salary (should now be 0)
            $netSalary = max(0, $basicSalary - $totalDeductions + $totalAdditions);
        }

        return [
            'basic_salary'     => $basicSalary,
            'total_deductions' => $totalDeductions,
            'total_additions'  => $totalAdditions,
            'net_salary'       => max(0, $netSalary), // Ensure net salary is never negative
            'deductions'       => $deductionRecords,
            'additions'        => $additionRecords,
        ];
    }
}