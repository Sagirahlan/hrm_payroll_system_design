<?php
// Script to recalculate payroll for employees 211 and 214 to test our fix
require_once __DIR__.'/vendor/autoload.php';

// Create a Laravel application instance
$app = require_once __DIR__.'/bootstrap/app.php';

use Illuminate\Http\Request;
use App\Services\PayrollCalculationService;
use App\Models\Employee;
use App\Models\PayrollRecord;

// Initialize the application
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get the service instance
$service = new PayrollCalculationService();

// Get employees
$employee211 = Employee::find(211);
$employee214 = Employee::find(214);

echo "Recalculating payroll for employee 211 (Suspended):\n";
if ($employee211) {
    $calculation211 = $service->calculatePayroll($employee211, '2025-09', true); // Suspended
    echo "  Basic Salary: {$calculation211['basic_salary']}\n";
    echo "  Total Deductions: {$calculation211['total_deductions']}\n";
    echo "  Total Additions: {$calculation211['total_additions']}\n";
    echo "  Net Salary: {$calculation211['net_salary']}\n";
    echo "  Deductions Detail:\n";
    foreach ($calculation211['deductions'] as $ded) {
        echo "    - {$ded['name_type']}: {$ded['amount']}\n";
    }
} else {
    echo "  Employee 211 not found\n";
}

echo "\nRecalculating payroll for employee 214 (Active):\n";
if ($employee214) {
    $calculation214 = $service->calculatePayroll($employee214, '2025-09', false); // Active
    echo "  Basic Salary: {$calculation214['basic_salary']}\n";
    echo "  Total Deductions: {$calculation214['total_deductions']}\n";
    echo "  Total Additions: {$calculation214['total_additions']}\n";
    echo "  Net Salary: {$calculation214['net_salary']}\n";
    echo "  Deductions Detail:\n";
    foreach ($calculation214['deductions'] as $ded) {
        echo "    - {$ded['name_type']}: {$ded['amount']}\n";
    }
} else {
    echo "  Employee 214 not found\n";
}
?>