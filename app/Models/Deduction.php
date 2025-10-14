<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Loan;

class Deduction extends Model
{
    use HasFactory;

    protected $primaryKey = 'deduction_id';

    protected $fillable = [
        'deduction_type',
        'amount',
        'amount_type',
        'deduction_period',
        'start_date',
        'end_date',
        'employee_id',
        'deduction_type_id',
        'loan_id', // Added for loan deductions
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function deductionType()
    {
        return $this->belongsTo(DeductionType::class, 'deduction_type_id');
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class, 'loan_id');
    }

    // Accessor for the 'amount' attribute
    public function getAmountAttribute($value)
    {
        // Simply return the stored value from the database
        return $value;
    }

    // Format the amount for display
    public function getFormattedAmountAttribute()
    {
        return number_format($this->attributes['amount'], 2);
    }

    // Get calculation type description
    public function getCalculationTypeDescriptionAttribute()
    {
        if ($this->amount_type === 'percentage') {
            // For percentage, we can't know the original percentage value from the final amount.
            // This might need to be stored separately if required.
            // For now, we'll just indicate that it's a percentage-based deduction.
            return 'Percentage'; 
        } elseif ($this->amount_type === 'fixed') {
            return 'Fixed';
        }
        
        // Fallback for statutory or other types
        if ($this->deductionType && $this->deductionType->is_statutory) {
            if ($this->deductionType->calculation_type === 'percentage') {
                return ($this->deductionType->rate_or_amount * 100) . '%';
            } else {
                return 'Fixed (Statutory)';
            }
        }

        return 'N/A';
    }
    
    /**
     * Scope to exclude completed loan deductions
     */
    public function scopeExcludeCompletedLoans($query)
    {
        return $query->whereDoesntHave('loan', function ($query) {
            $query->where('status', 'completed');
        });
    }
    
    /**
     * Check if this deduction is from a completed loan
     */
    public function isFromCompletedLoan()
    {
        if ($this->loan_id) {
            $loan = $this->loan;
            return $loan && $loan->status === 'completed';
        }
        return false;
    }
    
    /**
     * Get loan details for this deduction if it's a loan-related deduction
     */
    public function getLoanDetailsAttribute()
    {
        if ($this->loan_id && $this->loan) {
            $loan = $this->loan;
            
            // Calculate total paid from loan deductions
            $totalPaid = $loan->loanDeductions ? $loan->loanDeductions->sum('amount_deducted') : 0;
            
            return [
                'loan_id' => $loan->loan_id,
                'loan_type' => $loan->loan_type,
                'principal_amount' => $loan->principal_amount,
                'total_repaid' => $totalPaid,
                'remaining_balance' => $loan->remaining_balance,
                'monthly_deduction' => $loan->monthly_deduction,
                'total_months' => $loan->total_months,
                'remaining_months' => $loan->remaining_months,
                'status' => $loan->status
            ];
        }
        
        return null;
    }
}
