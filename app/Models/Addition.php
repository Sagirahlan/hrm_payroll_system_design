<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Addition extends Model
{
    protected $table = 'additions';
    protected $primaryKey = 'addition_id';
    protected $fillable = [
        'addition_type_id',
        'amount',
        'amount_type',
        'addition_period',
        'start_date',
        'end_date',
        'employee_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
    
    public function additionType()
    {
        return $this->belongsTo(AdditionType::class, 'addition_type_id');
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
            return 'Percentage';
        } elseif ($this->amount_type === 'fixed') {
            return 'Fixed';
        }

        // Fallback for statutory or other types
        if ($this->additionType && $this->additionType->is_statutory) {
            if ($this->additionType->calculation_type === 'percentage') {
                return ($this->additionType->rate_or_amount * 100) . '%';
            } else {
                return 'Fixed (Statutory)';
            }
        }

        return 'N/A';
    }
}
