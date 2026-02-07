<?php

namespace App\Services;

use App\Models\Pensioner;
use App\Models\Retirement;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PensionPayrollService
{
    /**
     * Calculate monthly pension amount for a pensioner
     */
    public function calculateMonthlyPension(Pensioner $pensioner): float
    {
        // In a real system, this would have complex calculations based on:
        // - Years of service
        // - Final salary
        // - Pension percentage
        
        // For now, return stored pension amount as monthly amount
        return (float) $pensioner->pension_amount;
    }

    /**
     * Calculate gratuity for an employee at retirement
     */
    public function calculateGratuity(Employee $employee, Retirement $retirement): float
    {
        // Get the last payroll record for the employee
        $lastPayroll = \App\Models\PayrollRecord::where('employee_id', $employee->employee_id)
            ->orderByDesc('created_at')
            ->first();

        if (!$lastPayroll || !$employee->date_of_first_appointment) {
            return 0;
        }

        // Calculate years of service
        $dateOfRetirement = Carbon::parse($retirement->retirement_date);
        $dateOfFirstAppointment = Carbon::parse($employee->date_of_first_appointment);
        
        if ($dateOfRetirement->lessThanOrEqualTo($dateOfFirstAppointment)) {
            return 0;
        }

        $yearsOfService = $dateOfFirstAppointment->diffInYears($dateOfRetirement);

        // For Nigerian CPS: Gratuity = 100% of last gross annual emoluments
        $grossMonthlyEmoluments = $lastPayroll->basic_salary + $lastPayroll->total_additions;
        $grossAnnualEmoluments = $grossMonthlyEmoluments * 12;

        // Gratuity calculation based on years of service
        // Standard calculation: Annual salary × years of service × factor
        $gratuity = $grossAnnualEmoluments; // 100% of last annual emoluments

        // Additional gratuity based on years of service (if applicable)
        if ($yearsOfService > 0) {
            // Some systems provide additional gratuity based on years of service
            $gratuity = $grossAnnualEmoluments; // Standard system provides 100% of annual emoluments
        }

        return round($gratuity, 2);
    }

    /**
     * Generate pension payment records for a given month
     */
    public function generatePensionPayments($month, $year)
    {
        // Get all active pensioners
        $pensioners = Pensioner::where('status', 'Active')
            ->whereNotNull('pension_amount')
            ->where('pension_amount', '>', 0)
            ->get();

        $paymentRecords = [];
        
        foreach ($pensioners as $pensioner) {
            $monthlyPension = $this->calculateMonthlyPension($pensioner);
            
            $paymentRecords[] = [
                'pensioner_id' => $pensioner->id,
                'employee_id' => $pensioner->employee_id,
                'month' => $month,
                'year' => $year,
                'pension_amount' => $monthlyPension,
                'deductions' => 0, // Calculate deductions if any
                'net_amount' => $monthlyPension, // Net amount after deductions
                'payment_date' => Carbon::create($year, $month, 1)->endOfMonth(), // End of the month
                'status' => 'pending', // pending, paid, failed
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        return $paymentRecords;
    }

    /**
     * Generate pension payroll records for a given month
     * Creates PayrollRecord entries for retired employees (pensioners)
     */
    public function generatePensionPayroll($month)
    {
        // Parse the month (format: Y-m)
        list($year, $monthNum) = explode('-', $month);
        
        // Get all active pensioners with their employee relationship
        $pensioners = Pensioner::where('status', 'Active')
            ->whereNotNull('pension_amount')
            ->where('pension_amount', '>', 0)
            ->with('employee')
            ->get();

        $processedPensioners = [];
        
        DB::beginTransaction();
        
        try {
            foreach ($pensioners as $pensioner) {
                $employee = $pensioner->employee;
                
                if (!$employee) {
                    \Log::warning("Pensioner {$pensioner->id} has no associated employee record");
                    continue;
                }
                
                // Check if payroll already exists for this pensioner for this month
                $existingPayroll = \App\Models\PayrollRecord::where('employee_id', $employee->employee_id)
                    ->where('payroll_month', $month . '-01')
                    ->where('payment_type', 'Pension')
                    ->first();
                
                if ($existingPayroll) {
                    \Log::info("Payroll already exists for pensioner {$employee->employee_id} for month {$month}");
                    continue;
                }
                
                // Get the pension amount
                $pensionAmount = $this->calculateMonthlyPension($pensioner);
                
                // Calculate additions for this pensioner
                $totalAdditions = 0;
                $additions = \App\Models\Addition::where('employee_id', $employee->employee_id)
                    ->where(function($query) use ($month) {
                        $query->where(function($q) use ($month) {
                            // Monthly or Perpetual additions that are active in this month
                            $q->whereIn('addition_period', ['Monthly', 'Perpetual'])
                              ->where('start_date', '<=', $month . '-01')
                              ->where(function($dateQuery) use ($month) {
                                  $dateQuery->whereNull('end_date')
                                           ->orWhere('end_date', '>=', $month . '-01');
                              });
                        })->orWhere(function($q) use ($month) {
                            // OneTime additions for this specific month
                            $q->where('addition_period', 'OneTime')
                              ->whereYear('start_date', '=', explode('-', $month)[0])
                              ->whereMonth('start_date', '=', explode('-', $month)[1]);
                        });
                    })
                    ->get();
                
                foreach ($additions as $addition) {
                    $totalAdditions += $addition->amount;
                }
                
                // Calculate deductions for this pensioner
                $totalDeductions = 0;
                $deductions = \App\Models\Deduction::where('employee_id', $employee->employee_id)
                    ->where(function($query) use ($month) {
                        $query->where(function($q) use ($month) {
                            // Monthly or Perpetual deductions that are active in this month
                            $q->whereIn('deduction_period', ['Monthly', 'Perpetual'])
                              ->where('start_date', '<=', $month . '-01')
                              ->where(function($dateQuery) use ($month) {
                                  $dateQuery->whereNull('end_date')
                                           ->orWhere('end_date', '>=', $month . '-01');
                              });
                        })->orWhere(function($q) use ($month) {
                            // OneTime deductions for this specific month
                            $q->where('deduction_period', 'OneTime')
                              ->whereYear('start_date', '=', explode('-', $month)[0])
                              ->whereMonth('start_date', '=', explode('-', $month)[1]);
                        });
                    })
                    ->get();
                
                foreach ($deductions as $deduction) {
                    $totalDeductions += $deduction->amount;
                }
                
                // Calculate net salary
                $netSalary = $pensionAmount + $totalAdditions - $totalDeductions;
                
                // Create payroll record
                $payrollRecord = \App\Models\PayrollRecord::create([
                    'employee_id' => $employee->employee_id,
                    'payroll_month' => $month . '-01',
                    'basic_salary' => $pensionAmount,
                    'total_additions' => $totalAdditions,
                    'total_deductions' => $totalDeductions,
                    'net_salary' => $netSalary,
                    'payment_type' => 'Pension',
                    'status' => 'Pending Review',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $processedPensioners[] = [
                    'pensioner_id' => $pensioner->id,
                    'employee_id' => $employee->employee_id,
                    'pension_amount' => $pensionAmount,
                    'additions' => $totalAdditions,
                    'deductions' => $totalDeductions,
                    'net_amount' => $netSalary,
                ];
                
                \Log::info("Created pension payroll for {$employee->employee_id}", [
                    'pension' => $pensionAmount,
                    'additions' => $totalAdditions,
                    'deductions' => $totalDeductions,
                    'net' => $netSalary
                ]);
            }
            
            DB::commit();
            
            return $processedPensioners;
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error generating pension payroll: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Process pension payments
     */
    public function processPensionPayments($paymentRecords)
    {
        DB::beginTransaction();
        
        try {
            foreach ($paymentRecords as $record) {
                // In a real system, this would process actual payments
                // Here we just update the status
                
                // For now, we'll just return the records to be processed
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'processed_count' => count($paymentRecords),
                'message' => 'Pension payments processed successfully'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Error processing pension payments: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get pensioner statistics
     */
    public function getPensionerStatistics()
    {
        $totalPensioners = Pensioner::count();
        $activePensioners = Pensioner::where('status', 'Active')->count();
        $monthlyPensionCost = Pensioner::where('status', 'Active')
            ->sum('pension_amount');
        
        return [
            'total_pensioners' => $totalPensioners,
            'active_pensioners' => $activePensioners,
            'monthly_pension_cost' => $monthlyPensionCost,
            'annual_pension_cost' => $monthlyPensionCost * 12,
        ];
    }

    /**
     * Move retired employees to pensioners table
     */
    public function moveRetiredEmployeesToPensioners()
    {
        $retiredEmployees = Employee::where('status', 'Retired')
            ->whereDoesntHave('pensioner')
            ->get();

        $processedCount = 0;
        $errors = [];

        foreach ($retiredEmployees as $employee) {
            try {
                // Find the corresponding retirement record
                $retirement = Retirement::with('employee')->where('employee_id', $employee->employee_id)->first();

                if (!$retirement) {
                    $errors[] = "No retirement record found for employee: {$employee->employee_id}";
                    continue;
                }

                // Check if pensioner already exists for this retirement
                if (Pensioner::where('retirement_id', $retirement->id)->exists()) {
                    continue; // Skip if pensioner already exists
                }

                // Get the beneficiary computation record if it exists
                $beneficiaryComputation = \App\Models\ComputeBeneficiary::where('id_no', $employee->employee_id)
                    ->orWhere('id_no', $employee->staff_id ?? $employee->employee_id)
                    ->first();

                // Calculate years of service
                $dateOfFirstAppointment = Carbon::parse($employee->date_of_first_appointment);
                $retirementDate = Carbon::parse($retirement->retirement_date);
                $yearsOfService = $dateOfFirstAppointment->diffInYears($retirementDate);

                // Determine pension and gratuity amounts from computation if available
                $pensionAmount = $beneficiaryComputation ? $beneficiaryComputation->pension_per_mnth : $retirement->gratuity_amount;
                $gratuityAmount = $beneficiaryComputation ? $beneficiaryComputation->gratuity_amt : $retirement->gratuity_amount;
                $totalDeathGratuity = $beneficiaryComputation ? $beneficiaryComputation->total_death_gratuity : $retirement->gratuity_amount;

                // Create pensioner record
                Pensioner::create([
                    'employee_id' => $employee->employee_id,
                    'full_name' => $employee->full_name,
                    'surname' => $employee->surname,
                    'first_name' => $employee->first_name,
                    'middle_name' => $employee->middle_name,
                    'email' => $employee->email,
                    'phone_number' => $employee->phone,
                    'date_of_birth' => $employee->date_of_birth,
                    'place_of_birth' => $employee->place_of_birth,
                    'date_of_first_appointment' => $employee->date_of_first_appointment,
                    'date_of_retirement' => $retirement->retirement_date,
                    'retirement_reason' => $retirement->retire_reason,
                    'retirement_type' => $beneficiaryComputation ? $beneficiaryComputation->gtype : 'RB', // RB (Retirement Benefits) or DG (Death Gratuity)
                    'department_id' => $employee->department_id,
                    'rank_id' => $employee->rank_id,
                    'step_id' => $employee->step_id,
                    'grade_level_id' => $employee->grade_level_id,
                    'salary_scale_id' => $employee->salary_scale_id,
                    'local_gov_area_id' => $employee->lga_id,
                    'bank_id' => $employee->bank_id,
                    'account_number' => $employee->account_number,
                    'account_name' => $employee->account_name,
                    'pension_amount' => $pensionAmount,
                    'gratuity_amount' => $gratuityAmount,
                    'total_death_gratuity' => $totalDeathGratuity,
                    'years_of_service' => $yearsOfService,
                    'pension_percentage' => $beneficiaryComputation ? $beneficiaryComputation->pct_pension : 0,
                    'gratuity_percentage' => $beneficiaryComputation ? $beneficiaryComputation->pct_gratuity : 0,
                    'address' => $employee->address,
                    'next_of_kin_name' => $employee->next_of_kin_name,
                    'next_of_kin_phone' => $employee->next_of_kin_phone,
                    'next_of_kin_address' => $employee->next_of_kin_address,
                    'status' => 'Active',
                    'retirement_id' => $retirement->id,
                    'beneficiary_computation_id' => $beneficiaryComputation ? $beneficiaryComputation->id : null,
                    'created_by' => auth()->id() ?? 1, // Use 1 as default if no authenticated user
                ]);

                $processedCount++;
            } catch (\Exception $e) {
                $errors[] = "Error processing employee {$employee->employee_id}: " . $e->getMessage();
                \Log::error("Error moving employee to pensioner: " . $e->getMessage(), [
                    'employee_id' => $employee->employee_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return [
            'success' => true,
            'processed_count' => $processedCount,
            'errors' => $errors,
            'message' => "Successfully moved {$processedCount} retired employees to pensioners."
        ];
    }
}