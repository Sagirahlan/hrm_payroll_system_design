<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Employee;
use App\Models\Pensioner;
use App\Models\PayrollRecord;

echo "--- Checking Staff No 800339 ---\n";
$e = Employee::where('staff_no', '800339')->first();

if ($e) {
    echo "Employee ID: " . $e->employee_id . "\n";
    echo "Staff No: " . $e->staff_no . "\n";
    echo "Name: " . $e->first_name . " " . $e->surname . "\n";
    echo "Status: " . $e->status . "\n";
    echo "Appointment Type ID: " . $e->appointment_type_id . "\n";
    if ($e->appointmentType) {
        echo "Appointment Type: " . $e->appointmentType->name . "\n";
    }

    echo "\n--- Pensioner Table ---\n";
    $p = Pensioner::where('employee_id', $e->employee_id)->first();
    if ($p) {
        echo "Pensioner found!\n";
        echo "Pensioner ID: " . $p->pensioner_id . "\n";
        echo "Status: " . $p->status . "\n";
        echo "Pension Amount: " . $p->pension_amount . "\n";
        echo "Retirement Date: " . $p->date_of_retirement . "\n";
    } else {
        echo "No Pensioner record found for this employee_id.\n";
    }

    echo "\n--- Payroll Records for Mar 2026 ---\n";
    $records = PayrollRecord::where('employee_id', $e->employee_id)
        ->whereYear('payroll_month', 2026)
        ->whereMonth('payroll_month', 3)
        ->get();

    foreach ($records as $record) {
        echo "ID: " . $record->payroll_id . ", Type: " . $record->payment_type . ", Basic: " . $record->basic_salary . ", Net: " . $record->net_salary . ", Status: " . $record->status . "\n";
    }
} else {
    echo "Employee 800339 not found.\n";
}
