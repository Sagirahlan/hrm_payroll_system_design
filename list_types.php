<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AppointmentType;

$types = AppointmentType::all();
foreach ($types as $type) {
    echo "ID: {$type->id}, Name: '{$type->name}'\n";
}
