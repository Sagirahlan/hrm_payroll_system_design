<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Loan extends Model
{
    protected $table = 'loans';
    protected $primaryKey = 'loan_id';

    protected $fillable = [
        'employee_id',
        'loan_type',
        'principal_amount',
        'monthly_deduction',
        'total_months',
        'remaining_months',
        'monthly_percentage',
        'start_date',
        'end_date',
        'total_repaid',
        'remaining_balance',
        'status',
        'description',
        'deduction_type_id'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'principal_amount' => 'decimal:2',
        'monthly_deduction' => 'decimal:2',
        'total_repaid' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'monthly_percentage' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function deductionType()
    {
        return $this->belongsTo(DeductionType::class, 'deduction_type_id');
    }

    /**
     * Relationship to loan deductions
     */
    public function loanDeductions()
    {
        return $this->hasMany(\App\Models\LoanDeduction::class, 'loan_id', 'loan_id');
    }

    /**
     * Calculate remaining months based on remaining balance and monthly deduction
     */
    public function calculateRemainingMonths()
    {
        if ($this->monthly_deduction > 0) {
            return ceil($this->remaining_balance / $this->monthly_deduction);
        }
        return 0;
    }

    /**
     * Calculate total months for loan repayment based on principal and monthly deduction
     */
    public function calculateTotalMonths()
    {
        if ($this->monthly_deduction > 0) {
            return ceil($this->principal_amount / $this->monthly_deduction);
        }
        return 0;
    }

    /**
     * Calculate months completed based on total months and remaining months
     */
    public function getMonthsCompletedAttribute()
    {
        return $this->total_months - $this->remaining_months;
    }

    /**
     * Check if loan is completed
     */
    public function isCompleted()
    {
        return $this->status === 'completed' || $this->remaining_balance <= 0;
    }

    /**
     * Calculate the loan end date based on start date and total months
     */
    public function calculateEndDate()
    {
        return Carbon::parse($this->start_date)->addMonths($this->total_months);
    }

    /**
     * Accessor for remaining months
     */
    public function getRemainingMonthsAttribute()
    {
        if ($this->monthly_deduction > 0 && $this->attributes['monthly_deduction'] > 0) {
            return ceil($this->attributes['remaining_balance'] / $this->attributes['monthly_deduction']);
        }
        return 0;
    }
    
    /**
     * Scope to get active loans for a specific type
     */
    public function scopeForType($query, $loanType)
    {
        return $query->where('loan_type', $loanType);
    }
}