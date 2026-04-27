<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Employee;
use App\Models\AppointmentType;
use Illuminate\Http\Request;
use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Auth;

// Pick a casual employee
$employee = Employee::whereHas('appointmentType', function($q){ 
    $q->where('name', 'Casual'); 
})->first();

if (!$employee) {
    echo "No casual employee found.\n";
    exit;
}

echo "Testing update for Employee: " . $employee->getFullNameAttribute() . " (ID: " . $employee->employee_id . ")\n";
echo "Current Status: " . $employee->status . "\n";

// Mock user login
$user = \App\Models\User::first();
Auth::login($user);

// Create mock request
$data = $employee->toArray();
$data['kin_name'] = $employee->nextOfKin->name ?? 'Next of Kin';
$data['kin_relationship'] = $employee->nextOfKin->relationship ?? 'Child';
$data['kin_mobile_no'] = $employee->nextOfKin->mobile_no ?? '08000000000';
$data['kin_address'] = $employee->nextOfKin->address ?? 'Address';
$data['bank_name'] = $employee->bank->bank_name ?? 'Bank';
$data['bank_code'] = $employee->bank->bank_code ?? '000';
$data['account_name'] = $employee->bank->account_name ?? 'Account';
$data['account_no'] = $employee->bank->account_no ?? '1234567890';

// Change status
$data['status'] = 'Suspended';
$data['change_reason'] = 'Test status update';

$request = new Request($data);
$request->setMethod('PUT');

$controller = new EmployeeController();

try {
    echo "Submitting update request...\n";
    $response = $controller->update($request, $employee);
    
    if ($response->isRedirect()) {
        $msg = session('success') ?: session('error') ?: session('info') ?: 'No session message';
        echo "Response: Redirect to " . $response->headers->get('Location') . " with message: $msg\n";
        
        // Check if pending change was created
        $pending = \App\Models\PendingEmployeeChange::where('employee_id', $employee->employee_id)->latest()->first();
        if ($pending) {
            echo "Pending record created. ID: " . $pending->id . "\n";
            echo "Data in pending record:\n";
            print_r($pending->data);
        } else {
            echo "FAILED: No pending record found.\n";
        }
    } else {
        echo "Response: Not a redirect. Status: " . $response->getStatusCode() . "\n";
    }
} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    if (method_exists($e, 'errors')) {
        echo "Validation Errors:\n";
        print_r($e->errors());
    }
}
