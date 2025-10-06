<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanDeduction extends Model
{
    protected $table = 'loan_deductions';
    protected $primaryKey = 'loan_deduction_id';

    protected $fillable = [
        'loan_id',
        'employee_id',
        'payroll_id',
        'amount_deducted',
        'remaining_balance',
        'month_number',
        'payroll_month',
        'deduction_date',
        'status',
    ];

    protected $casts = [
        'amount_deducted' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'deduction_date' => 'date',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class, 'loan_id', 'loan_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function payroll()
    {
        return $this->belongsTo(PayrollRecord::class, 'payroll_id', 'payroll_id');
    }
}