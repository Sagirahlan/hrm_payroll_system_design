<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaySlip extends Model
{
    protected $table = 'pay_slips';
    protected $primaryKey = 'payslip_id';
    protected $fillable = ['payroll_id', 'employee_id', 'generated_date', 'pdf_path'];

    public function payroll()
    {
        return $this->belongsTo(PayrollRecord::class, 'payroll_id', 'payroll_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
}