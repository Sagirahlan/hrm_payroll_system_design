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
        'total_interest',
        'interest_rate',
        'total_repayment',
        'monthly_deduction',
        'total_months',
        'remaining_months',
        'monthly_percentage',
        'deduction_start_month',
        'end_date',
        'total_repaid',
        'remaining_balance',
        'status',
        'description',
        'deduction_type_id'
    ];

    protected $casts = [
        'end_date' => 'date',
        'principal_amount' => 'decimal:2',
        'total_interest' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'total_repayment' => 'decimal:2',
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
     * Relationship to related deduction records
     */
    public function deductions()
    {
        return $this->hasMany(\App\Models\Deduction::class, 'loan_id', 'loan_id');
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
     * Calculate the loan end date based on deduction start month and total months
     * End date should be the last day of the final month
     * For example, if deduction start month is 2025-11 and total months is 3:
     * Month 1: November 2025, Month 2: December 2025, Month 3: January 2026
     * End date should be January 31, 2026 (last day of the 3rd month)
     */
    public function calculateEndDate()
    {
        return Carbon::parse($this->deduction_start_month . '-01')->addMonths(max(0, $this->total_months - 1))->endOfMonth();
    }

    /**
     * Accessor for remaining months
     * Calculate remaining months based on remaining balance, but ensure it doesn't exceed total months
     */
    public function getRemainingMonthsAttribute()
    {
        if ($this->monthly_deduction > 0 && $this->attributes['monthly_deduction'] > 0) {
            // Calculate remaining months based on remaining balance
            $calculatedMonths = ceil($this->attributes['remaining_balance'] / $this->attributes['monthly_deduction']);
            
            // Ensure remaining months doesn't exceed total months to prevent negative months completed
            return min($calculatedMonths, $this->attributes['total_months']);
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