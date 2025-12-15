<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Retirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'retirement_date',
        'notification_date',
        'gratuity_amount',
        'status',
        'retire_reason',
        'years_of_service',
    ];

    protected $casts = [
        'retirement_date' => 'date',
        'notification_date' => 'date',
        'gratuity_amount' => 'decimal:2',
        'years_of_service' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function pensioner()
    {
        return $this->hasOne(Pensioner::class, 'retirement_id');
    }
}