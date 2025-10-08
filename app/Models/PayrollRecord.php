<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\GradeLevel;

class PayrollRecord extends Model
{
    protected $table = 'payroll_records';
    protected $primaryKey = 'payroll_id';
    protected $fillable = [
        'employee_id',
        'grade_level_id',
        'basic_salary',
        'status',
        'total_additions',
        'total_deductions',
        'net_salary',
        'payment_date',
        'payroll_month', // Added payroll_month
        'remarks',
    ];

    protected $casts = [
        'payroll_month' => 'date', // Cast as date
        'payment_date' => 'date', // Cast as date (already a date field)
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function gradeLevel()
{
    return $this->belongsTo(GradeLevel::class, 'grade_level_id', 'id');
}

    public function deductions()
    {
        return $this->hasMany(Deduction::class, 'payroll_id', 'payroll_id');
    }
    public function transaction()
    {
    return $this->hasOne(PaymentTransaction::class, 'payroll_id', 'payroll_id');
    }
    
    /**
     * Set the net salary attribute, ensuring it's not negative
     *
     * @param  float  $value
     * @return void
     */
    public function setNetSalaryAttribute($value)
    {
        // Ensure net salary is never negative
        $this->attributes['net_salary'] = max(0, $value);
    }
}