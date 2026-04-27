<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$employee = App\Models\Employee::whereHas('appointmentType', function($q){ 
    $q->where('name', 'Casual'); 
})->first();

if (!$employee) {
    echo "No casual employee found.\n";
    exit;
}

echo "Current status: " . $employee->status . "\n";

$currentData = $employee->toArray();
$validated = ['status' => 'Suspended']; // Simulate user changing status

$changedData = [];
$previousData = [];

foreach ($validated as $key => $value) {
    if ($key === 'status' && $employee->status === 'Hold' && $value !== 'Retired-Active') {
        continue; 
    }

    if (array_key_exists($key, $currentData) && $currentData[$key] != $value) {
        $changedData[$key] = $value;
        $previousData[$key] = $currentData[$key];
    } elseif (!array_key_exists($key, $currentData) && !is_null($value)) {
        $changedData[$key] = $value;
        $previousData[$key] = null;
    }
}

echo "Changed data:\n";
print_r($changedData);
