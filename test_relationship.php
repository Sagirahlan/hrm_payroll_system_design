<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Retirement;
use App\Models\Pensioner;

$retirementId = 94;
$r = Retirement::find($retirementId);

echo "Retirement ID: $retirementId\n";
if ($r) {
    echo "Eloquent - Loading Pensioner Relationship...\n";
    $p = $r->pensioner;
    if ($p) {
        echo "Pensioner Found: ID {$p->id}, Name: {$p->full_name}\n";
    } else {
        echo "Pensioner NOT FOUND via Eloquent relationship.\n";
    }
    
    echo "\nEloquent - Querying whereDoesntHave('pensioner')...\n";
    $existsInWhereDoesntHave = Retirement::where('id', $retirementId)->whereDoesntHave('pensioner')->exists();
    echo "Is this retirement in 'whereDoesntHave(pensioner)'? " . ($existsInWhereDoesntHave ? 'YES' : 'NO') . "\n";
} else {
    echo "Retirement record NOT FOUND for ID $retirementId\n";
}
