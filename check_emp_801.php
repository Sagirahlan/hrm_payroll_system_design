<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Employee;

$e = Employee::find(801);
if ($e) {
    echo json_encode($e->toArray());
} else {
    echo "Employee 801 not found";
}
