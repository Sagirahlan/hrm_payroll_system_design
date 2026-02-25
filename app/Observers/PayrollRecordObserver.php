<?php

namespace App\Observers;

use App\Models\PayrollRecord;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Log;

class PayrollRecordObserver
{
    public function created(PayrollRecord $payroll): void
    {
        $employee = $payroll->employee;
        
        PaymentTransaction::updateOrCreate(
            ['payroll_id' => $payroll->payroll_id],
            [
                'employee_id' => $payroll->employee_id,
                'amount' => $payroll->net_salary,
                'status' => $payroll->status,
                'payment_date' => $payroll->payment_date,
                'bank_code' => $employee?->bank?->bank_code ?? null,
                'account_name' => $employee?->bank?->account_name ?? 
                    ($employee ? $employee->first_name . ' ' . $employee->surname : 'Unknown'),
                'account_number' => $employee?->bank?->account_no ?? '0000000000',
            ]
        );
        
        Log::info("PayrollRecordObserver: Created/Updated PaymentTransaction for payroll_id: {$payroll->payroll_id} with status: {$payroll->status}");
    }

    public function updated(PayrollRecord $payroll): void
    {
        if ($payroll->isDirty(['status', 'net_salary', 'payment_date'])) {
            $updateData = [];
            
            if ($payroll->isDirty('status')) {
                $updateData['status'] = $payroll->status;
                Log::info("PayrollRecordObserver: Syncing status change from '{$payroll->getOriginal('status')}' to '{$payroll->status}' for payroll_id: {$payroll->payroll_id}");
            }
            
            if ($payroll->isDirty('net_salary')) {
                $updateData['amount'] = $payroll->net_salary;
            }
            
            if ($payroll->isDirty('payment_date')) {
                $updateData['payment_date'] = $payroll->payment_date;
            }
            
            if (!empty($updateData)) {
                PaymentTransaction::where('payroll_id', $payroll->payroll_id)
                    ->update($updateData);
            }
        }
    }

    public function deleted(PayrollRecord $payroll): void
    {
        PaymentTransaction::where('payroll_id', $payroll->payroll_id)->delete();
        
        Log::info("PayrollRecordObserver: Deleted PaymentTransaction for payroll_id: {$payroll->payroll_id}");
    }
}
