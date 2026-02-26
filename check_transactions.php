<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$payrolls = \App\Models\PayrollRecord::where('payment_type', 'Pension')
    ->whereYear('payroll_month', 2026)
    ->whereMonth('payroll_month', 2)
    ->with('transaction', 'employee')
    ->take(5)
    ->get();

echo "Checking Pension Payrolls for Feb 2026:\n";
foreach ($payrolls as $pr) {
    echo "Payroll ID: {$pr->payroll_id} | Emp ID: {$pr->employee_id}\n";
    $t = $pr->transaction;
    if ($t) {
        echo "  Transaction: bank_code='{$t->bank_code}', acc_name='{$t->account_name}', acc_num='{$t->account_number}'\n";
    } else {
        echo "  Transaction: NONE\n";
    }
}
