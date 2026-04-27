<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- Appointment Types ---\n";
foreach(DB::table('appointment_types')->get() as $t) {
    echo $t->id . ': ' . $t->name . "\n";
}

echo "\n--- Pensioner 66 Bank Details ---\n";
$p = DB::table('pensioners')->where('id', 66)->first();
print_r($p);
