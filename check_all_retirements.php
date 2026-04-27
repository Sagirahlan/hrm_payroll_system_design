<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$employeeId = 18;
$retirements = DB::table('retirements')->where('employee_id', $employeeId)->get();

echo "--- All Retirement Records for Employee ID: $employeeId ---\n";
foreach ($retirements as $r) {
    echo "ID: " . $r->id . "\n";
    echo "   Date: " . $r->retirement_date . "\n";
    echo "   Status: " . ($r->status ?? 'N/A') . "\n";
    
    // Check if linked to any pensioner
    $p = DB::table('pensioners')->where('retirement_id', $r->id)->first();
    if ($p) {
        echo "   -> Linked to Pensioner ID: " . $p->id . " (Employee ID in Pensioner: " . $p->employee_id . ")\n";
    } else {
        echo "   -> NOT LINKED to any Pensioner\n";
    }
}

// Check if there are other pensioners for this employee
$otherP = DB::table('pensioners')->where('employee_id', $employeeId)->get();
echo "\n--- Pensioner Records for Employee ID: $employeeId ---\n";
foreach ($otherP as $p) {
    echo "ID: " . $p->id . "\n";
    echo "   Retirement ID: " . ($p->retirement_id ?? 'NULL') . "\n";
}
