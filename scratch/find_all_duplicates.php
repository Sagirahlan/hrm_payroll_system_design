<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Employee;
use App\Models\Pensioner;

echo "--- Checking for Active Employees in Pensioners Table ---\n";
$activeEmployeeIds = Employee::where('status', 'Active')->pluck('employee_id');

$duplicates = Pensioner::whereIn('employee_id', $activeEmployeeIds)->get();

echo "Found " . $duplicates->count() . " active employees who are ALSO in pensioners table.\n\n";

foreach ($duplicates as $d) {
    $e = Employee::find($d->employee_id);
    echo "Staff No: " . $e->staff_no . " | Name: " . $e->first_name . " " . $e->surname . " | Employee ID: " . $e->employee_id . "\n";
    echo "  Employee Status: " . $e->status . "\n";
    echo "  Pensioner Status: " . $d->status . " | Pension Amt: " . $d->pension_amount . "\n";
    echo "--------------------------------------------------\n";
}
