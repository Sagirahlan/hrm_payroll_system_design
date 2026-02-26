<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$transactions = \App\Models\PaymentTransaction::whereHas('payroll', function($q) {
        $q->where('payment_type', 'Pension');
    })
    ->get();

$updated = 0;
foreach ($transactions as $t) {
    if ($t->account_number === '0000000000' || empty($t->bank_code)) {
        $pensioner = \App\Models\Pensioner::where('employee_id', $t->employee_id)->first();
        if ($pensioner) {
            $t->account_number = $pensioner->account_number ?: $t->account_number;
            $t->account_name = $pensioner->account_name ?: $t->account_name;
            if ($pensioner->bank_id) {
                $bank = $pensioner->bank;
                if ($bank) {
                    $t->bank_code = $bank->bank_code;
                }
            }
            $t->save();
            $updated++;
        }
    }
}

echo "Updated $updated PaymentTransactions with Pensioner bank data.\n";
