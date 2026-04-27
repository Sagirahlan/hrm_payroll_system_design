<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Employee;
use App\Models\Pensioner;
use App\Models\Retirement;

$employeeId = 18;
$p = Pensioner::where('employee_id', $employeeId)->first();
$r = Retirement::where('employee_id', $employeeId)->get();

echo "Employee ID: $employeeId\n";
if ($p) {
    echo "Pensioner ID: {$p->id}\n";
    echo "   Retirement ID (linked): " . ($p->retirement_id ?? 'NULL') . "\n";
} else {
    echo "Pensioner NOT FOUND for Employee $employeeId\n";
}

if ($r->count() > 0) {
    echo "Retirement Records Found: " . $r->count() . "\n";
    foreach ($r as $item) {
        echo "   - Retirement ID: {$item->id}, Date: {$item->retirement_date}, Status: {$item->status}\n";
        // Check if this retirement is linked to any (other) pensioner
        $p_linked = Pensioner::where('retirement_id', $item->id)->first();
        if ($p_linked) {
            echo "     (Linked to Pensioner ID: {$p_linked->id}, Employee ID: {$p_linked->employee_id})\n";
        } else {
            echo "     (NOT LINKED to any Pensioner)\n";
        }
    }
} else {
    echo "No Retirement records found for Employee $employeeId\n";
}
