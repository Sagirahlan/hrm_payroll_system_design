<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Employee;
use App\Models\PendingEmployeeChange;
use App\Http\Controllers\PendingEmployeeChangeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Pick a casual employee
$employee = Employee::whereHas('appointmentType', function($q){ 
    $q->where('name', 'Casual'); 
})->first();

if (!$employee) {
    echo "No casual employee found.\n";
    exit;
}

echo "Testing approval for Employee: " . $employee->getFullNameAttribute() . " (ID: " . $employee->employee_id . ")\n";
echo "Current Status: " . $employee->status . "\n";

// Create a pending change
$pending = new PendingEmployeeChange();
$pending->employee_id = $employee->employee_id;
$pending->data = ['status' => 'Suspended'];
$pending->status = 'pending';
$pending->requested_by = \App\Models\User::first()->id;
$pending->save();

echo "Created pending change ID: " . $pending->id . "\n";

// Mock user login for approval
$admin = \App\Models\User::first();
Auth::login($admin);

$controller = new PendingEmployeeChangeController();

try {
    echo "Approving change...\n";
    // The approve method usually takes the ID
    $request = new Request(['approval_note' => 'Test approval']);
    $response = $controller->approve($request, $pending->id);
    
    $employee->refresh();
    echo "New Status in DB: " . $employee->status . "\n";
    
    if ($employee->status === 'Suspended') {
        echo "SUCCESS: Status updated in database.\n";
    } else {
        echo "FAILED: Status did not change.\n";
    }
} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
