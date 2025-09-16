<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deduction extends Model
{
    use HasFactory;

    protected $primaryKey = 'deduction_id';

    protected $fillable = [
        'deduction_type',
        'amount_type',
        'deduction_period',
        'start_date',
        'end_date',
        'employee_id',
        'deduction_type_id',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function deductionType()
    {
        return $this->belongsTo(DeductionType::class, 'deduction_type_id');
    }

    public function getAmountAttribute()
    {
        // Load the employee and grade level relationship if not already loaded
        $employee = $this->employee;
        if (!$employee) {
            return 0;
        }

        // Load the grade level if not already loaded
        $gradeLevel = $employee->gradeLevel;
        if (!$gradeLevel) {
            return 0;
        }

        // Get the basic salary
        $basicSalary = $gradeLevel->basic_salary ?? 0;

        if ($this->deductionType) {
            // If it's a statutory deduction, calculate based on rate_or_amount
            if ($this->deductionType->is_statutory) {
                if ($this->deductionType->calculation_type === 'percentage') {
                    // Calculate percentage of basic salary (rate_or_amount is already a decimal percentage)
                    return $this->deductionType->rate_or_amount * $basicSalary;
                } else {
                    // Fixed amount
                    return $this->deductionType->rate_or_amount;
                }
            } else {
                // For non-statutory deductions, check if we have amount_type
                if ($this->amount_type === 'percentage') {
                    // Calculate percentage of basic salary (rate_or_amount is a percentage value, not decimal)
                    // For example, if rate_or_amount is 2, it means 2%
                    return ($this->deductionType->rate_or_amount / 100) * $basicSalary;
                } else {
                    // Fixed amount
                    return $this->deductionType->rate_or_amount;
                }
            }
        }
        
        return 0;
    }

    // Format the amount for display
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2);
    }

    // Get calculation type description
    public function getCalculationTypeDescriptionAttribute()
    {
        if ($this->deductionType) {
            if ($this->deductionType->is_statutory) {
                // Convert decimal percentage to percentage for display
                $percentage = $this->deductionType->calculation_type === 'percentage' ? 
                    ($this->deductionType->rate_or_amount * 100) : 
                    $this->deductionType->rate_or_amount;
                return $this->deductionType->calculation_type === 'percentage' ? 
                    "{$percentage}%" : 
                    "₦" . number_format($this->deductionType->rate_or_amount, 2);
            } else {
                // Convert decimal percentage to percentage for display
                $percentage = $this->amount_type === 'percentage' ? 
                    ($this->deductionType->rate_or_amount) : 
                    $this->deductionType->rate_or_amount;
                return $this->amount_type === 'percentage' ? 
                    "{$percentage}%" : 
                    "₦" . number_format($this->deductionType->rate_or_amount, 2);
            }
        }
        return 'N/A';
    }
}
