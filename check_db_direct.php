<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$employeeId = 18;
$res = DB::table('pensioners')->where('employee_id', $employeeId)->get();

echo "--- Direct DB Query for Employee ID: $employeeId ---\n";
if ($res->count() > 0) {
    foreach ($res as $row) {
        echo "ID: " . $row->id . "\n";
        echo "   Full Name: " . $row->full_name . "\n";
        echo "   Deleted At: " . ($row->deleted_at ?? 'Active') . "\n";
        echo "   Retirement ID: " . ($row->retirement_id ?? 'NULL') . "\n";
    }
} else {
    echo "No records found in 'pensioners' table for Employee ID $employeeId\n";
}

$ret = DB::table('retirements')->where('employee_id', $employeeId)->get();
echo "\n--- Direct DB Query for Retirements of Employee ID: $employeeId ---\n";
foreach ($ret as $row) {
    echo "ID: " . $row->id . "\n";
    echo "   Date: " . $row->retirement_date . "\n";
}
