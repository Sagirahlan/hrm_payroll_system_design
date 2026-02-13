<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;
use App\Models\PayrollRecord;

use App\Models\User;

class PaymentTransaction extends Model
{
    protected $primaryKey = 'transaction_id';

    protected $fillable = [
        'payroll_id',
        'employee_id',
        'amount',
        'bank_code',
        'account_name',
        'account_number',
        'payment_date',
        'status',
        'approver_id'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function payroll()
    {
        return $this->belongsTo(PayrollRecord::class, 'payroll_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
