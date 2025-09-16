<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Addition extends Model
{
    protected $table = 'additions';
    protected $primaryKey = 'addition_id';
    protected $fillable = [
        'addition_type',
        'amount',
        'amount_type',
        'addition_period',
        'start_date',
        'end_date',
        'employee_id',
        'addition_type_id',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
    
    public function additionType()
    {
        return $this->belongsTo(AdditionType::class, 'addition_type_id');
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

        if ($this->additionType) {
            // If it's a statutory addition, calculate based on rate_or_amount
            if ($this->additionType->is_statutory) {
                if ($this->additionType->calculation_type === 'percentage') {
                    // Calculate percentage of basic salary (rate_or_amount is already a decimal percentage)
                    return $this->additionType->rate_or_amount * $basicSalary;
                } else {
                    // Fixed amount
                    return $this->additionType->rate_or_amount;
                }
            } else {
                // For non-statutory additions, check if we have amount_type
                if ($this->amount_type === 'percentage') {
                    // Calculate percentage of basic salary (rate_or_amount is a percentage value, not decimal)
                    // For example, if rate_or_amount is 2, it means 2%
                    return ($this->additionType->rate_or_amount / 100) * $basicSalary;
                } else {
                    // Fixed amount
                    return $this->additionType->rate_or_amount;
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
        if ($this->additionType) {
            if ($this->additionType->is_statutory) {
                // Convert decimal percentage to percentage for display
                $percentage = $this->additionType->calculation_type === 'percentage' ? 
                    ($this->additionType->rate_or_amount * 100) : 
                    $this->additionType->rate_or_amount;
                return $this->additionType->calculation_type === 'percentage' ? 
                    "{$percentage}%" : 
                    "₦" . number_format($this->additionType->rate_or_amount, 2);
            } else {
                // For non-statutory, display the rate_or_amount as is
                if ($this->amount_type === 'percentage') {
                    // Format the percentage value to remove unnecessary decimals
                    $percentage = rtrim(rtrim(sprintf('%.4f', $this->additionType->rate_or_amount), '0'), '.');
                    return "{$percentage}%";
                } else {
                    return "₦" . number_format($this->additionType->rate_or_amount, 2);
                }
            }
        }
        return 'N/A';
    }
}
