<?php
// Simple script to check employee status and deductions
require_once __DIR__.'/vendor/autoload.php';

// Create a Laravel application instance
$app = require_once __DIR__.'/bootstrap/app.php';

// Create a request to initialize the application properly
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Employee;
use App\Models\Deduction;
use App\Models\PayrollRecord;

// Check employees 211 and 214
$employee211 = Employee::find(211);
$employee214 = Employee::find(214);

echo "Employee 211:\n";
if ($employee211) {
    echo "  ID: " . $employee211->employee_id . "\n";
    echo "  Name: " . $employee211->first_name . " " . $employee211->surname . "\n";
    echo "  Status: " . $employee211->status . "\n";
    echo "  Grade Level: " . ($employee211->gradeLevel ? $employee211->gradeLevel->name : 'None') . "\n";
    
    // Check deductions
    $deductions211 = Deduction::where('employee_id', 211)->get();
    echo "  Deductions count: " . $deductions211->count() . "\n";
    foreach ($deductions211 as $ded) {
        echo "    - {$ded->deduction_type}: {$ded->amount} ({$ded->deduction_period})\n";
    }
    
    // Check payroll records
    $payrolls211 = PayrollRecord::where('employee_id', 211)->orderBy('payroll_month', 'desc')->limit(5)->get();
    echo "  Recent Payroll Records:\n";
    foreach ($payrolls211 as $payroll) {
        echo "    - Month: {$payroll->payroll_month}, Basic: {$payroll->basic_salary}, Deductions: {$payroll->total_deductions}, Additions: {$payroll->total_additions}, Net: {$payroll->net_salary}\n";
    }
} else {
    echo "  Not found\n";
}

echo "\nEmployee 214:\n";
if ($employee214) {
    echo "  ID: " . $employee214->employee_id . "\n";
    echo "  Name: " . $employee214->first_name . " " . $employee214->surname . "\n";
    echo "  Status: " . $employee214->status . "\n";
    echo "  Grade Level: " . ($employee214->gradeLevel ? $employee214->gradeLevel->name : 'None') . "\n";
    
    // Check deductions
    $deductions214 = Deduction::where('employee_id', 214)->get();
    echo "  Deductions count: " . $deductions214->count() . "\n";
    foreach ($deductions214 as $ded) {
        echo "    - {$ded->deduction_type}: {$ded->amount} ({$ded->deduction_period})\n";
    }
    
    // Check payroll records
    $payrolls214 = PayrollRecord::where('employee_id', 214)->orderBy('payroll_month', 'desc')->limit(5)->get();
    echo "  Recent Payroll Records:\n";
    foreach ($payrolls214 as $payroll) {
        echo "    - Month: {$payroll->payroll_month}, Basic: {$payroll->basic_salary}, Deductions: {$payroll->total_deductions}, Additions: {$payroll->total_additions}, Net: {$payroll->net_salary}\n";
    }
} else {
    echo "  Not found\n";
}
?>