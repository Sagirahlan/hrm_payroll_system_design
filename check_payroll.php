<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check existing payroll record for employee 533 (Abdullahi Rabo)
$rec = \App\Models\PayrollRecord::where('employee_id', 533)
    ->where('payroll_month', '2026-02-01')
    ->where('payment_type', 'Pension')
    ->first();

if ($rec) {
    echo "Found Payroll Record ID: {$rec->payroll_id}\n";
    echo "Basic Salary: ₦" . number_format($rec->basic_salary, 2) . "\n";
    echo "Total Additions: ₦" . number_format($rec->total_additions, 2) . "\n";
    echo "Total Deductions: ₦" . number_format($rec->total_deductions, 2) . "\n";
    echo "Net Salary: ₦" . number_format($rec->net_salary, 2) . "\n";
    echo "Created At: {$rec->created_at}\n";
    echo "Updated At: {$rec->updated_at}\n";
} else {
    echo "No payroll record found for employee 533 in Feb 2026\n";
}
