<?php

namespace App\Services;

use App\Models\Pensioner;
use App\Models\PayrollRecord;
use App\Models\PaymentTransaction;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PensionPayrollService
{
    /**
     * Generate pension payroll for all active pensioners for a given month
     */
    public function generatePensionPayroll(string $month)
    {
        $pensioners = Pensioner::where('status', 'Active')
            ->with(['employee', 'employee.gradeLevel', 'employee.step', 'employee.bank'])
            ->get();

        $processedPensioners = [];
        
        foreach ($pensioners as $pensioner) {
            $payrollRecord = $this->generatePensionPayrollForPensioner($pensioner, $month);
            if ($payrollRecord) {
                $processedPensioners[] = $payrollRecord;
            }
        }
        
        return $processedPensioners;
    }

    /**
     * Generate pension payroll for a specific pensioner for a given month
     */
    public function generatePensionPayrollForPensioner(Pensioner $pensioner, string $month)
    {
        try {
            DB::beginTransaction();

            // Calculate the payroll month date
            $payrollMonth = Carbon::parse($month . '-01');

            // Check if payroll already exists for this pensioner for this month
            $existingPayroll = PayrollRecord::where('employee_id', $pensioner->employee_id)
                ->whereYear('payroll_month', $payrollMonth->year)
                ->whereMonth('payroll_month', $payrollMonth->month)
                ->first();

            if ($existingPayroll) {
                DB::rollback();
                \Log::warning('Pension payroll already exists for pensioner: ' . $pensioner->employee_id . ' for month: ' . $month);
                return $existingPayroll;
            }

            // Create the payroll record for the pensioner
            $payroll = PayrollRecord::create([
                'employee_id' => $pensioner->employee_id,
                'grade_level_id' => $pensioner->employee->gradeLevel->id ?? null,
                'payroll_month' => $payrollMonth->format('Y-m-d'),
                'basic_salary' => 0, // No basic salary for pensioners, only pension amount
                'total_additions' => 0, // No additions for pensioners (could be enhanced to support pension adjustments)
                'total_deductions' => 0, // No deductions typically for pensioners (could be enhanced to support pension deductions)
                'net_salary' => $pensioner->pension_amount,
                'status' => 'Pending Review', // Set to pending review initially
                'payment_date' => null,
                'remarks' => 'Pension payment for ' . $payrollMonth->format('F Y'),
            ]);

            // Create the payment transaction
            PaymentTransaction::create([
                'payroll_id' => $payroll->payroll_id,
                'employee_id' => $pensioner->employee_id,
                'amount' => $pensioner->pension_amount,
                'payment_date' => null,
                'bank_code' => $pensioner->employee->bank->bank_code ?? null,
                'account_name' => $pensioner->employee->bank->account_name ?? ($pensioner->employee->first_name . ' ' . $pensioner->employee->surname),
                'account_number' => $pensioner->employee->bank->account_no ?? '0000000000',
            ]);

            DB::commit();

            return $payroll;
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error generating pension payroll for pensioner: ' . $pensioner->employee_id, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }

    /**
     * Get pensioners with their monthly pension amount
     */
    public function getActivePensioners()
    {
        return Pensioner::where('status', 'Active')
            ->with(['employee', 'employee.bank'])
            ->get();
    }

    /**
     * Get pensioners for a specific month with their payment status
     */
    public function getMonthlyPensionersWithStatus($month)
    {
        $payrollMonth = Carbon::parse($month . '-01');
        
        $pensioners = $this->getActivePensioners();
        
        foreach ($pensioners as $pensioner) {
            $payrollRecord = PayrollRecord::where('employee_id', $pensioner->employee_id)
                ->whereYear('payroll_month', $payrollMonth->year)
                ->whereMonth('payroll_month', $payrollMonth->month)
                ->first();
                
            $pensioner->payroll_record = $payrollRecord;
            $pensioner->payment_status = $payrollRecord ? $payrollRecord->status : 'Not Generated';
        }
        
        return $pensioners;
    }

    /**
     * Generate pension payroll for a specific pensioner with custom pension amount
     * This method is useful when pension amount needs to be adjusted due to special circumstances
     */
    public function generatePensionPayrollWithCustomAmount(Pensioner $pensioner, string $month, float $customAmount)
    {
        try {
            DB::beginTransaction();

            // Calculate the payroll month date
            $payrollMonth = Carbon::parse($month . '-01');

            // Check if payroll already exists for this pensioner for this month
            $existingPayroll = PayrollRecord::where('employee_id', $pensioner->employee_id)
                ->whereYear('payroll_month', $payrollMonth->year)
                ->whereMonth('payroll_month', $payrollMonth->month)
                ->first();

            if ($existingPayroll) {
                DB::rollback();
                \Log::warning('Pension payroll already exists for pensioner: ' . $pensioner->employee_id . ' for month: ' . $month);
                return $existingPayroll;
            }

            // Create the payroll record for the pensioner with custom amount
            $payroll = PayrollRecord::create([
                'employee_id' => $pensioner->employee_id,
                'grade_level_id' => $pensioner->employee->gradeLevel->id ?? null,
                'payroll_month' => $payrollMonth->format('Y-m-d'),
                'basic_salary' => 0,
                'total_additions' => 0,
                'total_deductions' => 0,
                'net_salary' => $customAmount,
                'status' => 'Pending Review',
                'payment_date' => null,
                'remarks' => 'Pension payment (custom amount) for ' . $payrollMonth->format('F Y'),
            ]);

            // Create the payment transaction with custom amount
            PaymentTransaction::create([
                'payroll_id' => $payroll->payroll_id,
                'employee_id' => $pensioner->employee_id,
                'amount' => $customAmount,
                'payment_date' => null,
                'bank_code' => $pensioner->employee->bank->bank_code ?? null,
                'account_name' => $pensioner->employee->bank->account_name ?? ($pensioner->employee->first_name . ' ' . $pensioner->employee->surname),
                'account_number' => $pensioner->employee->bank->account_no ?? '0000000000',
            ]);

            DB::commit();

            return $payroll;
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error generating pension payroll with custom amount for pensioner: ' . $pensioner->employee_id, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }

    /**
     * Process gratuity payment for an employee
     * This creates a special payroll record for gratuity payment
     */
    public function processGratuityPayment(Employee $employee, float $gratuityAmount, string $paymentDate = null)
    {
        try {
            DB::beginTransaction();

            $paymentDate = $paymentDate ? Carbon::parse($paymentDate) : now();
            $month = $paymentDate->format('Y-m');

            // Create the payroll record for the gratuity payment
            $payroll = PayrollRecord::create([
                'employee_id' => $employee->employee_id,
                'grade_level_id' => $employee->gradeLevel->id ?? null,
                'payroll_month' => $paymentDate->format('Y-m-d'),
                'basic_salary' => 0, // Gratuity is not salary
                'total_additions' => $gratuityAmount, // Store gratuity as addition
                'total_deductions' => 0,
                'net_salary' => $gratuityAmount, // Gratuity becomes net payment
                'status' => 'Paid', // Gratuity payments are typically marked as paid immediately
                'payment_date' => $paymentDate,
                'remarks' => 'Gratuity payment processed on ' . $paymentDate->format('Y-m-d'),
            ]);

            // Create the payment transaction for gratuity
            PaymentTransaction::create([
                'payroll_id' => $payroll->payroll_id,
                'employee_id' => $employee->employee_id,
                'amount' => $gratuityAmount,
                'payment_date' => $paymentDate,
                'bank_code' => $employee->bank->bank_code ?? null,
                'account_name' => $employee->bank->account_name ?? ($employee->first_name . ' ' . $employee->surname),
                'account_number' => $employee->bank->account_no ?? '0000000000',
                'transaction_type' => 'gratuity', // Mark as gratuity transaction
            ]);

            DB::commit();

            return $payroll;
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error processing gratuity payment for employee: ' . $employee->employee_id, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }

    /**
     * Calculate and process gratuity for a retiring employee
     */
    public function calculateAndProcessGratuityForRetirement(Employee $employee, GratuityCalculationService $gratuityService, string $retirementDate = null)
    {
        $retirementDate = $retirementDate ?: now()->format('Y-m-d');
        
        // Calculate gratuity using the service
        $gratuityResult = $gratuityService->calculateGratuity($employee, $retirementDate);
        
        if ($gratuityResult['gratuity_amount'] > 0) {
            return $this->processGratuityPayment(
                $employee,
                $gratuityResult['gratuity_amount'],
                $retirementDate
            );
        }
        
        return null;
    }

    /**
     * Get total pension amount for all active pensioners
     */
    public function getTotalPensionLiability()
    {
        $activePensioners = $this->getActivePensioners();
        $totalLiability = 0;

        foreach ($activePensioners as $pensioner) {
            $totalLiability += $pensioner->pension_amount;
        }

        return [
            'total_pensioners' => $activePensioners->count(),
            'total_monthly_liability' => $totalLiability,
            'total_annual_liability' => $totalLiability * 12,
        ];
    }

    /**
     * Generate pension payroll for all active pensioners with special conditions
     * This method supports additional features like pension adjustments, deductions, etc.
     */
    public function generatePensionPayrollWithAdjustments(string $month, array $adjustments = [])
    {
        $pensioners = Pensioner::where('status', 'Active')
            ->with(['employee', 'employee.gradeLevel', 'employee.step', 'employee.bank'])
            ->get();

        $processedPensioners = [];
        
        foreach ($pensioners as $pensioner) {
            $pensionAmount = $pensioner->pension_amount;

            // Apply any adjustments if specified for this pensioner
            if (isset($adjustments[$pensioner->employee_id])) {
                $adjustment = $adjustments[$pensioner->employee_id];
                
                if (isset($adjustment['amount'])) {
                    $pensionAmount = $adjustment['amount'];
                } elseif (isset($adjustment['percentage_change'])) {
                    $pensionAmount = $pensionAmount * (1 + ($adjustment['percentage_change'] / 100));
                }
            }

            $payrollRecord = $this->generatePensionPayrollWithCustomAmount($pensioner, $month, $pensionAmount);
            if ($payrollRecord) {
                $processedPensioners[] = $payrollRecord;
            }
        }
        
        return $processedPensioners;
    }
}