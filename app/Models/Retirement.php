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
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
}