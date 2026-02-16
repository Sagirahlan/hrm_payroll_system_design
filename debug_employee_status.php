<?php

use App\Models\PayrollRecord;
use App\Models\PaymentTransaction;
use App\Models\Employee;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Checking Specific Employee (ID: 488)...\n";

$employee = Employee::where('employee_id', 488)->first(); // Or maybe ID: 488 refers to staff_no? or id?
// Screenshot says "ID: 488". Let's assume it's the id column or employee_id.

if (!$employee) {
    echo "Employee not found by ID. Trying staff no?\n";
    // Screenshot shows "Staff No: 900097"
    $employee = Employee::where('staff_no', '900097')->first();
}

if ($employee) {
    echo "Employee Found: " . $employee->first_name . " " . $employee->surname . "\n";
    
    $payrolls = PayrollRecord::where('employee_id', $employee->employee_id)->get();
    foreach ($payrolls as $p) {
        echo "Payroll Month: " . $p->payroll_month . " | Status: " . $p->status . " | PayDate: " . $p->payment_date . "\n";
        
        $txn = PaymentTransaction::where('payroll_id', $p->payroll_id)->first();
        if ($txn) {
            echo "MATCHING TXN -> Amount: " . $txn->amount . " | Status: " . $txn->status . " | PayDate: " . $txn->payment_date . "\n";
        } else {
            echo "NO MATCHING TXN found.\n";
        }
    }
} else {
    echo "Employee not found.\n";
}
