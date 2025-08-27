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
        'amount',
        'deduction_period',
        'start_date',
        'end_date',
        'employee_id',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
}
