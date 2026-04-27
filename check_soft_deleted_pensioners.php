<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Employee;
use App\Models\Pensioner;
use Illuminate\Support\Facades\DB;

echo "--- Identifying Retired Employees with Soft-Deleted Pensioner Records --- \n";

// Find all retired employees
$retiredEmployees = Employee::where('status', 'Retired')->get();

$count = 0;
foreach ($retiredEmployees as $employee) {
    // Check if they have a non-deleted pensioner record
    $activePensioner = Pensioner::where('employee_id', $employee->employee_id)->first();
    
    if (!$activePensioner) {
        // Check if they have a soft-deleted pensioner record
        $deletedPensioner = Pensioner::onlyTrashed()->where('employee_id', $employee->employee_id)->first();
        
        if ($deletedPensioner) {
            echo "Employee ID: {$employee->employee_id}, Name: {$employee->full_name}\n";
            echo "   -> Soft-Deleted Pensioner Found (ID: {$deletedPensioner->id}, Deleted At: {$deletedPensioner->deleted_at})\n";
            $count++;
        }
    }
}

echo "\nTotal detected: $count\n";
