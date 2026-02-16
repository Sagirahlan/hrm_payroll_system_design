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

echo "Starting Update of Approved Payroll Payments...\n";

// Find all Approved payroll records
$approvedPayrollIds = PayrollRecord::where('status', 'Approved')->pluck('payroll_id');

echo "Found " . $approvedPayrollIds->count() . " Approved Payroll Records.\n";

if ($approvedPayrollIds->count() > 0) {
    // Update corresponding Payment Transactions
    $updated = PaymentTransaction::whereIn('payroll_id', $approvedPayrollIds)
        ->where('status', '!=', 'successful')
        ->update([
            'status' => 'successful',
            'payment_date' => DB::raw('created_at'), // Set payment date to creation date if null, or use now()? use created_at for now to verify
            // Actually, let's just set payment_date to updated_at or now() if null.
            // Better: 'payment_date' => now()
        ]);
        
    // Re-run with specific correct date logic if needed. 
    // For now, let's just mark them successful.
    // If payment_date is null, set it to now.
    $updatedCount = PaymentTransaction::whereIn('payroll_id', $approvedPayrollIds)
        ->where('status', '!=', 'successful') // Pending or Failed
        ->update([
            'status' => 'successful',
            'payment_date' => now()
        ]);
        
    // Also update Payroll Records themselves to have payment_date
    $updatedPayrolls = PayrollRecord::whereIn('payroll_id', $approvedPayrollIds)
        ->whereNull('payment_date')
        ->update([
            'payment_date' => now() // Or created_at if we want to be retroactive-ish, but now() is safer for "when it was marked paid"
        ]);

    echo "Updated $updatedCount Payment Transactions to 'successful'.\n";
    echo "Updated $updatedPayrolls Payroll Records to have a payment date.\n";
} else {
    echo "No Approved Payroll records found.\n";
}

echo "Done.\n";
