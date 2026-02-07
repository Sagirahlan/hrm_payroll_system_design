<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check loan 8
$loan = \App\Models\Loan::find(8);

if (!$loan) {
    echo "Loan 8 not found\n";
    exit;
}

echo "=== LOAN DETAILS ===\n";
echo "Loan ID: {$loan->loan_id}\n";
echo "Employee ID: {$loan->employee_id}\n";
echo "Status: {$loan->status}\n";
echo "Principal: ₦" . number_format($loan->principal_amount, 2) . "\n";
echo "Monthly Deduction: ₦" . number_format($loan->monthly_deduction, 2) . "\n";
echo "Total Months: {$loan->total_months}\n";
echo "Remaining Months: {$loan->remaining_months}\n";
echo "Total Repaid: ₦" . number_format($loan->total_repaid, 2) . "\n";
echo "Remaining Balance: ₦" . number_format($loan->remaining_balance, 2) . "\n";
echo "Start Date: {$loan->start_date}\n";
echo "End Date: {$loan->end_date}\n\n";

// Check for deduction record linked to this loan
$deduction = \App\Models\Deduction::where('loan_id', $loan->loan_id)->first();
if ($deduction) {
    echo "=== LINKED DEDUCTION ===\n";
    echo "Deduction ID: {$deduction->deduction_id}\n";
    echo "Employee ID: {$deduction->employee_id}\n";
    echo "Amount: ₦" . number_format($deduction->amount, 2) . "\n";
    echo "Period: {$deduction->deduction_period}\n";
    echo "Start Date: {$deduction->start_date}\n";
    echo "End Date: {$deduction->end_date}\n";
} else {
    echo "=== NO LINKED DEDUCTION FOUND ===\n";
}

// Check for loan deduction tracking records
$loanDeductions = \App\Models\LoanDeduction::where('loan_id', $loan->loan_id)->get();
echo "\n=== LOAN DEDUCTION TRACKING RECORDS ===\n";
echo "Total Records: {$loanDeductions->count()}\n";
foreach ($loanDeductions as $ld) {
    echo "  - Month: {$ld->payroll_month}, Amount: ₦" . number_format($ld->amount_deducted, 2) . ", Status: {$ld->status}\n";
}
