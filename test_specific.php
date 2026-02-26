<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$payroll = \App\Models\PayrollRecord::where('payroll_id', 2042)->first();
if (!$payroll) die("Payroll not found");

echo "Testing Payroll_id: {$payroll->payroll_id}\n";
$employee = $payroll->employee;

$bankCode = $employee?->bank?->bank_code ?? null;
$accountName = $employee?->bank?->account_name ?? ($employee ? $employee->first_name . ' ' . $employee->surname : 'Unknown');
$accountNumber = $employee?->bank?->account_no ?? '0000000000';

echo "Default Employee Bank Info:\n";
echo "Bank Code: " . var_export($bankCode, true) . "\n";
echo "Account Name: " . var_export($accountName, true) . "\n";
echo "Account No: " . var_export($accountNumber, true) . "\n\n";

if ($payroll->payment_type === 'Pension' && $employee) {
    echo "This is a Pension payroll\n";
    $pensioner = \App\Models\Pensioner::where('employee_id', $employee->employee_id)->first();
    if ($pensioner) {
        echo "Found Pensioner: {$pensioner->full_name}\n";
        echo "Pensioner Model Account No: " . var_export($pensioner->account_number, true) . "\n";
        echo "Pensioner Model Account Name: " . var_export($pensioner->account_name, true) . "\n";
        echo "Pensioner Model Bank ID: " . var_export($pensioner->bank_id, true) . "\n";
        
        $accountNumber = $pensioner->account_number ?? $accountNumber;
        $accountName = $pensioner->account_name ?? $accountName;
        
        if ($pensioner->bank_id) {
            $pensionerBank = $pensioner->bank;
            echo "Loaded Pensioner Bank rel: " . ($pensionerBank ? "YES" : "NO") . "\n";
            if ($pensionerBank) {
                echo "Pensioner Bank Code: " . var_export($pensionerBank->bank_code, true) . "\n";
                $bankCode = $pensionerBank->bank_code ?? $bankCode;
            }
        }
    }
}

echo "\nFinal Resolved Data for Observer:\n";
echo "Bank Code: " . var_export($bankCode, true) . "\n";
echo "Account Name: " . var_export($accountName, true) . "\n";
echo "Account No: " . var_export($accountNumber, true) . "\n";

