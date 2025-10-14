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
}