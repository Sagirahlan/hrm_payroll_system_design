<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Employee;

$counts = Employee::whereHas('appointmentType', function($q) {
    $q->where('name', 'Casual');
})->select('status', \DB::raw('count(*) as count'))
  ->groupBy('status')
  ->get();

foreach ($counts as $row) {
    echo "Status: " . $row->status . " - Count: " . $row->count . "\n";
}
