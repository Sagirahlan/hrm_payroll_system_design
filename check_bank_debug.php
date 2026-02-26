<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Employee;
use App\Models\Pensioner;
use App\Models\Retirement;

echo "=== FIX CASUAL EMPLOYEES WITH RETIRED STATUS ===\n\n";

// Find all casual employees with Retired or Retired-active status
$casuals = Employee::where('appointment_type_id', 2)
    ->whereIn('status', ['Retired', 'Retired-active'])
    ->get();

echo "Found " . $casuals->count() . " casual employees with incorrect Retired status:\n";
foreach ($casuals as $c) {
    $pen = Pensioner::where('employee_id', $c->employee_id)->first();
    echo "  Staff {$c->staff_no}: {$c->first_name} {$c->surname}, Status: {$c->status}, Pensioner: " . ($pen ? "YES (ID {$pen->id})" : "NO") . "\n";
    
    // If they have a pensioner record, check if there's a non-casual employee with same name
    // who should actually have it
    if ($pen) {
        $realMatch = Employee::where('appointment_type_id', '!=', 2)
            ->where(function($q) use ($c) {
                $q->where(function($q2) use ($c) {
                    $q2->where('first_name', 'like', '%' . trim($c->first_name) . '%')
                       ->where('surname', 'like', '%' . trim($c->surname) . '%');
                })->orWhere(function($q2) use ($c) {
                    $q2->where('first_name', 'like', '%' . trim($c->surname) . '%')
                       ->where('surname', 'like', '%' . trim($c->first_name) . '%');
                });
            })
            ->first();
        
        if ($realMatch) {
            $realPen = Pensioner::where('employee_id', $realMatch->employee_id)->first();
            if (!$realPen) {
                // Move pensioner to the real employee
                $pen->update(['employee_id' => $realMatch->employee_id]);
                echo "    → Moved pensioner to {$realMatch->first_name} {$realMatch->surname} (Staff {$realMatch->staff_no})\n";
            } else {
                // Real employee already has pensioner — delete the duplicate
                $pen->delete();
                echo "    → Deleted duplicate pensioner (real employee {$realMatch->staff_no} already has one)\n";
            }
        } else {
            // No matching non-casual employee — just remove pensioner from casual
            $pen->delete();
            echo "    → Removed pensioner record from casual employee (no non-casual match found)\n";
        }
        
        // Also remove retirement record from casual
        Retirement::where('employee_id', $c->employee_id)->delete();
    }
    
    // Set status back to Active
    $c->update(['status' => 'Active']);
    echo "    → Status set to Active\n";
}

echo "\n=== VERIFICATION ===\n";
echo "Casual with Retired: " . Employee::where('appointment_type_id', 2)->whereIn('status', ['Retired', 'Retired-active'])->count() . "\n";
echo "Total employees: " . Employee::count() . "\n";
echo "Total pensioners: " . Pensioner::count() . "\n";

// Check JIBRIN MUSA specifically
echo "\n=== JIBRIN MUSA CHECK ===\n";
$jm = Employee::where('first_name', 'like', '%Jibrin%')
    ->orWhere('surname', 'like', '%Jibrin%')
    ->get();
foreach ($jm as $e) {
    $pen = Pensioner::where('employee_id', $e->employee_id)->first();
    echo "  Staff {$e->staff_no}: {$e->first_name} {$e->surname}, Type: {$e->appointment_type_id}, Status: {$e->status}, Pensioner: " . ($pen ? "YES" : "NO") . "\n";
}
