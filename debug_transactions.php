<?php

use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$latest = PaymentTransaction::with('payroll')->latest('created_at')->take(5)->get();

echo "Latest 5 Transactions with Payroll Month:\n";
foreach ($latest as $t) {
    echo "ID: " . $t->transaction_id . 
         " | PayDate: " . ($t->payment_date ?? 'NULL') . 
         " | PayrollMonth: " . ($t->payroll ? $t->payroll->payroll_month->format('Y-m-d') : 'NULL') . 
         " | CreatedAt: " . $t->created_at . "\n";
}
