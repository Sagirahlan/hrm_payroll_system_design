<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Verify HARUNA BISHIR
$emp = App\Models\Employee::where('staff_no', '80322')->first();
echo "=== HARUNA BISHIR ===\n";
echo "Employee ID: {$emp->employee_id}\n";
echo "Staff No: {$emp->staff_no}\n";
echo "Name: {$emp->first_name} {$emp->surname}\n\n";

$bank = App\Models\Bank::where('employee_id', $emp->employee_id)->first();
if ($bank) {
    echo "Bank: {$bank->bank_name}\n";
    echo "Bank Code: {$bank->bank_code}\n";
    echo "Account Name: {$bank->account_name}\n";
    echo "Account No: {$bank->account_no}\n";
} else {
    echo "NO BANK RECORD!\n";
}

// Check remaining mismatches
echo "\n=== REMAINING MISMATCHES ===\n";
$mismatchCount = 0;
$banks = App\Models\Bank::all();
foreach ($banks as $b) {
    $e = App\Models\Employee::find($b->employee_id);
    if (!$e) continue;
    $accName = strtolower($b->account_name ?? '');
    if (empty($accName)) continue;
    $f = stripos($accName, strtolower($e->first_name)) !== false;
    $l = stripos($accName, strtolower($e->surname)) !== false;
    if (!$f && !$l) {
        $mismatchCount++;
        echo "  ID {$e->employee_id} ({$e->first_name} {$e->surname}) -> Account: {$b->account_name}\n";
    }
}
echo "\nTotal remaining mismatches: {$mismatchCount}\n";
