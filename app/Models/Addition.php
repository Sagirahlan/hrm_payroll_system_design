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
        'addition_period',
        'start_date',
        'end_date',
        'employee_id',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
}