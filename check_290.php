<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$res = DB::table('retirements')->where('id', 290)->first();

echo "--- Record for Retirement ID: 290 ---\n";
if ($res) {
    echo "ID: " . $res->id . "\n";
    echo "   Employee ID: " . $res->employee_id . "\n";
    echo "   Date: " . $res->retirement_date . "\n";
} else {
    echo "Retirement record ID 290 NOT FOUND in 'retirements' table\n";
}

// Find employee with ID 18
$emp = DB::table('employees')->where('employee_id', 18)->first();
if ($emp) {
    echo "\n--- Employee ID: 18 ---\n";
    echo "   Full Name: " . $emp->first_name . " " . $emp->surname . "\n";
    echo "   Staff No: " . $emp->staff_no . "\n";
}
