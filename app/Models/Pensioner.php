<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pensioner extends Model
{
    protected $primaryKey = 'pensioner_id';
    protected $fillable = [
        'employee_id',
        'pension_start_date',
        'pension_amount',
        'status',
        'rsa_balance_at_retirement',
        'lump_sum_amount',
        'pension_type',
        'expected_lifespan_months',
    ];

    protected $casts = [
        'pension_start_date' => 'date',
        'pension_amount' => 'decimal:2',
        'rsa_balance_at_retirement' => 'decimal:2',
        'lump_sum_amount' => 'decimal:2',
        'expected_lifespan_months' => 'integer',
        'status' => 'string',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function retirement()
    {
        return $this->hasOne(Retirement::class, 'employee_id', 'employee_id');
    }
}