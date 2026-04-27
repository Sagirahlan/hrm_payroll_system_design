<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Employee;
use Illuminate\Support\Facades\DB;

$employeeId = 18;
$employee = Employee::find($employeeId);
$pensioner = DB::table('pensioners')->where('employee_id', $employeeId)->first();

echo "Employee ID: $employeeId\n";
if ($employee) {
    echo "Employee Found: " . $employee->first_name . " " . $employee->surname . "\n";
    echo "Appointment Type ID: " . $employee->appointment_type_id . "\n";
    echo "Status: " . $employee->status . "\n";
} else {
    echo "Employee Not Found\n";
}

if ($pensioner) {
    echo "Pensioner Found: " . $pensioner->full_name . " (ID: " . $pensioner->id . ")\n";
} else {
    echo "Pensioner Not Found in database\n";
}
