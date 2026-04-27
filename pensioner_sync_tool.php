<?php
/**
 * Pensioner Synchronization & Diagnostic Tool
 * This script identifies and fixes inconsistencies between Employees, Retirements, and Pensioners.
 * 
 * Usage: php pensioner_sync_tool.php [--fix]
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Employee;
use App\Models\Pensioner;
use App\Models\Retirement;
use Illuminate\Support\Facades\DB;

$fix = in_array('--fix', $argv);

echo "--- Pensioner Synchronization & Diagnostic Tool ---\n";
echo "Mode: " . ($fix ? "FIX (Destructive)" : "DIAGNOSTIC (Read-only)") . "\n\n";

// 1. Find employees with multiple retirement records
echo "[1] Checking for duplicate retirement records...\n";
$duplicateRetirements = DB::table('retirements')
    ->select('employee_id', DB::raw('count(*) as count'))
    ->groupBy('employee_id')
    ->having('count', '>', 1)
    ->get();

if ($duplicateRetirements->isEmpty()) {
    echo "    OK: No employees with duplicate retirement records found.\n";
} else {
    foreach ($duplicateRetirements as $dup) {
        $employee = Employee::find($dup->employee_id);
        echo "    ISSUE: Employee ID {$dup->employee_id} (" . ($employee->full_name ?? 'Unknown') . ") has {$dup->count} retirement records.\n";
        
        $records = Retirement::where('employee_id', $dup->employee_id)->get();
        foreach ($records as $r) {
            $linked = Pensioner::where('retirement_id', $r->id)->exists();
            echo "       - Retirement ID: {$r->id}, Date: {$r->retirement_date}, Linked to Pensioner: " . ($linked ? "YES" : "NO") . "\n";
            
            if ($fix && !$linked) {
                echo "         -> FIXING: Deleting unlinked duplicate retirement record ID {$r->id}...\n";
                $r->delete();
            }
        }
    }
}

echo "\n[2] Checking for unlinked pensioners (employee exists but pensioner record missing link to retirement)...\n";
$pensioners = Pensioner::with('employee')->whereNull('retirement_id')->get();

if ($pensioners->isEmpty()) {
    echo "    OK: All pensioners are linked to a retirement record.\n";
} else {
    foreach ($pensioners as $p) {
        echo "    ISSUE: Pensioner ID {$p->id} ({$p->full_name}) has NO retirement_id link.\n";
        
        // Try to find a matching retirement record for this employee
        $r = Retirement::where('employee_id', $p->employee_id)->first();
        if ($r) {
            echo "       - Matching Retirement record found: ID {$r->id} (Date: {$r->retirement_date})\n";
            if ($fix) {
                echo "         -> FIXING: Linking Pensioner ID {$p->id} to Retirement ID {$r->id}...\n";
                $p->update(['retirement_id' => $r->id]);
            }
        } else {
            echo "       - WARNING: No retirement record found for this employee ID in the database.\n";
        }
    }
}

echo "\n[3] Checking for Staff ID / Employee ID collisions...\n";
use Illuminate\Support\Facades\Schema;

if (Schema::hasColumn('pensioners', 'staff_no')) {
    // Case A: Duplicate Staff No in Pensioners
    $dupStaff = DB::table('pensioners')
        ->select('staff_no', DB::raw('count(*) as count'))
        ->whereNotNull('staff_no')
        ->groupBy('staff_no')
        ->having('count', '>', 1)
        ->get();

    foreach ($dupStaff as $ds) {
        echo "    ISSUE: Staff No {$ds->staff_no} is used by {$ds->count} different records in the pensioners table.\n";
        $records = Pensioner::where('staff_no', $ds->staff_no)->get();
        foreach ($records as $r) {
            echo "       - Pensioner ID: {$r->id}, Name: {$r->full_name}, Employee ID: {$r->employee_id}\n";
        }
    }
} else {
    echo "    NOTE: 'staff_no' column missing in pensioners table. Checking via Employee relationship...\n";
}

// Case B: Staff No in Pensioners (via Employee) doesn't match the Name in Employees (Potential Mix-up)
echo "\n[4] Checking for name mismatches / double assignments...\n";
$targetStaff = '800086';
$eTarget = Employee::where('staff_no', $targetStaff)->first();

if ($eTarget) {
    echo "    Found Employee with Staff No $targetStaff: ID {$eTarget->employee_id}, Name: {$eTarget->full_name}\n";
    $pLinked = Pensioner::where('employee_id', $eTarget->employee_id)->first();
    if ($pLinked) {
        echo "    !!! COLLISION DETECTED !!!\n";
        echo "    Pensioner record exists for this Employee ID ({$eTarget->employee_id}):\n";
        echo "    - Pensioner ID: {$pLinked->id}\n";
        echo "    - Pensioner Name in DB: {$pLinked->full_name}\n";
        echo "    - Employee Name in DB: {$eTarget->full_name}\n";
        echo "    - Retirement ID linked: " . ($pLinked->retirement_id ?? 'NULL') . "\n";
        
        if ($pLinked->full_name != $eTarget->full_name) {
            echo "    -> PROBLEM: Names don't match! The pensioner record says '{$pLinked->full_name}' but the employee record says '{$eTarget->full_name}'.\n";
            echo "       This is why 'MAMMAN I' is not showing up as 'MAMMAN I' in the pensioner list.\n";
            
            if ($fix) {
               echo "       -> FIXING: Updating Pensioner Name to match Employee record...\n";
               $pLinked->update(['full_name' => $eTarget->full_name]);
            }
        }
    } else {
        echo "    No Pensioner record directly linked to Employee ID {$eTarget->employee_id}.\n";
    }
} else {
    echo "    No Employee record found with Staff No $targetStaff.\n";
}

// Check if anyone else is using Employee ID 18
$checkId = 18;
$pId = Pensioner::where('employee_id', $checkId)->get();
foreach ($pId as $p) {
    echo "    Pensioner ID {$p->id} is using Employee ID $checkId (Name: {$p->full_name})\n";
}

echo "\n--- Sync Tool Finished ---\n";
if (!$fix) {
    echo "TIP: Run with '--fix' to apply identified fixes.\n";
}
