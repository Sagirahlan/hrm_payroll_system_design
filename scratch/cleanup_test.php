<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pensioner;
use App\Models\PendingPensionerChange;

$p = Pensioner::withTrashed()->find(1);
if ($p) {
    $p->restore();
    echo "Restored pensioner ID 1\n";
}

// Clean up test pending change
PendingPensionerChange::where('reason', 'Unit Test Deletion Request')->delete();
echo "Cleaned up test pending change records.\n";
