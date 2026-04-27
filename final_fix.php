<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Employee;

$e = Employee::find(801);
if ($e) {
    $e->mobile_no = '08000000000';
    $e->save();
    echo "Employee 801 phone fixed to 08000000000";
}
