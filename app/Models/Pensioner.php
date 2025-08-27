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
    ];

    protected $casts = [
        'pension_start_date' => 'date',
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