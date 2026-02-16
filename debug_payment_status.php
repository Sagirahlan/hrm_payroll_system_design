<?php

use App\Models\PayrollRecord;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Checking Approved Payroll without Successful Payment...\n";

$approvedPayrollIds = PayrollRecord::where('status', 'Approved')->pluck('payroll_id')->toArray();

$transactions = PaymentTransaction::whereIn('payroll_id', $approvedPayrollIds)->get();

echo "Found " . count($transactions) . " Payment Transactions linked to Approved Payroll.\n";

$pending = $transactions->where('status', '!=', 'successful');
echo "Of which " . $pending->count() . " are NOT successful.\n";

if ($pending->count() > 0) {
    echo "Sample pending status: " . $pending->first()->status . "\n";
    
    // Check case sensitivity
     echo "Sample pending status (raw): '" . $pending->first()->getRawOriginal('status') . "'\n";
} else {
    echo "All seem successful? Let's check a specific one.\n";
    // Check if payroll IDs match
    $onePayroll = PayrollRecord::where('status', 'Approved')->first();
    echo "Payroll ID: " . $onePayroll->payroll_id . "\n";
    $txn = PaymentTransaction::where('payroll_id', $onePayroll->payroll_id)->first();
    if ($txn) {
        echo "Txn Status: " . $txn->status . "\n";
    } else {
        echo "No transaction found for this payroll record.\n";
    }
}
