<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Employee;
use App\Models\Pensioner;
use App\Models\Retirement;
use App\Models\Bank;

$dupeStaffs = ['9012517', '9012453', '9012486', '9012539', '902214', '9012514', '904019', '9012477'];

echo "=== DELETING DUPLICATE EMPLOYEES ===\n\n";
$deleted = 0;
foreach ($dupeStaffs as $staffNo) {
    $dupe = Employee::where('staff_no', $staffNo)->first();
    if (!$dupe) {
        echo "SKIP: Staff {$staffNo} not found\n";
        continue;
    }
    
    echo "DELETE: {$dupe->first_name} {$dupe->surname} (Staff: {$staffNo}, ID: {$dupe->employee_id})\n";
    
    // Delete pensioner record
    $pen = Pensioner::where('employee_id', $dupe->employee_id)->first();
    if ($pen) {
        $pen->delete();
        echo "  Deleted pensioner (ID {$pen->id})\n";
    }
    
    // Delete retirement record
    $ret = Retirement::where('employee_id', $dupe->employee_id)->first();
    if ($ret) {
        $ret->delete();
        echo "  Deleted retirement\n";
    }
    
    // Delete bank record
    $bank = Bank::where('employee_id', $dupe->employee_id)->first();
    if ($bank) {
        $bank->delete();
        echo "  Deleted bank record\n";
    }
    
    // Delete the employee
    $dupe->delete();
    echo "  Deleted employee\n";
    $deleted++;
}

echo "\nDeleted {$deleted} duplicate employees\n";
echo "Total employees: " . Employee::count() . "\n";
echo "Total pensioners: " . Pensioner::count() . "\n";

echo "\n=== VERIFICATION ===\n";
$realStaffs = ['800306', '800304', '800301', '800357', '800353', '800351', '800350', '800276'];
foreach ($realStaffs as $staffNo) {
    $emp = Employee::where('staff_no', $staffNo)->first();
    $pen = Pensioner::where('employee_id', $emp->employee_id)->first();
    echo "  Staff {$staffNo}: {$emp->first_name} {$emp->surname}, Status: {$emp->status}, Pensioner: " . ($pen ? "YES" : "NO") . "\n";
}
