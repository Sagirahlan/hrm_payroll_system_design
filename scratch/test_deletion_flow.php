<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pensioner;
use App\Models\PendingPensionerChange;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

// Mock login as admin (user ID 1 usually)
$user = User::find(1);
Auth::login($user);

echo "--- Testing Deletion Request Flow ---\n";

// Find a pensioner to test deletion (avoiding 154 for now to let user do it)
$p = Pensioner::first();
if (!$p) {
    die("No pensioners found to test.\n");
}

echo "Requesting deletion for Pensioner: " . $p->full_name . " (ID: " . $p->id . ")\n";

// Emulate calling PensionerController@destroy
$controller = app(\App\Http\Controllers\PensionerController::class);
$request = new \Illuminate\Http\Request([
    'reason' => 'Unit Test Deletion Request'
]);

// Call destroy
$response = $controller->destroy($request, $p->id);

echo "Response received: " . ($response->getStatusCode() == 302 ? "Redirect (Success)" : $response->getStatusCode()) . "\n";

// Verify check
$pending = PendingPensionerChange::where('pensioner_id', $p->id)
    ->where('change_type', 'delete')
    ->where('status', 'pending')
    ->first();

if ($pending) {
    echo "SUCCESS: Pending deletion request created! ID: " . $pending->id . "\n";
    echo "Reason: " . $pending->reason . "\n";
    
    // Now test approval
    echo "\n--- Testing Approval Flow ---\n";
    $pendingController = app(\App\Http\Controllers\PendingPensionerChangeController::class);
    $approvalRequest = new \Illuminate\Http\Request([
        'approval_notes' => 'Approved for test removal'
    ]);
    
    $approvalResponse = $pendingController->approve($pending, $approvalRequest);
    echo "Approval Response: " . ($approvalResponse->getStatusCode() == 302 ? "Redirect (Success)" : $approvalResponse->getStatusCode()) . "\n";
    
    // Check if soft deleted
    $pDeleted = Pensioner::withTrashed()->find($p->id);
    if ($pDeleted && $pDeleted->trashed()) {
        echo "SUCCESS: Pensioner record is now soft-deleted!\n";
    } else {
        echo "FAILURE: Pensioner record was not soft-deleted.\n";
    }
} else {
    echo "FAILURE: Pending deletion request not found in database.\n";
}
