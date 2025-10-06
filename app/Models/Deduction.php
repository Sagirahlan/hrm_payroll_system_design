<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Models\Loan;

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
}
