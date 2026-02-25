<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;
use App\Models\PayrollRecord;
use App\Models\User;
use Illuminate\Support\Facades\Log;

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

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($transaction) {
            if ($transaction->exists && $transaction->isDirty('status')) {
                $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 8);
                $allowedCallers = [
                    'Illuminate\Database\Eloquent\Builder',
                    'Illuminate\Database\Query\Builder',
                ];
                $fromAllowedCaller = false;

                foreach ($caller as $frame) {
                    $class = $frame['class'] ?? '';
                    if (in_array($class, $allowedCallers)) {
                        $fromAllowedCaller = true;
                        break;
                    }
                    if (str_contains($class, 'Observer')) {
                        $fromAllowedCaller = true;
                        break;
                    }
                }

                if (!$fromAllowedCaller) {
                    Log::warning("PaymentTransaction: Blocked direct status update attempt for transaction_id: {$transaction->transaction_id}");
                    throw new \Exception(
                        'PaymentTransaction status cannot be changed directly. ' .
                        'Update the corresponding PayrollRecord status instead.'
                    );
                }
            }
        });
    }

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
