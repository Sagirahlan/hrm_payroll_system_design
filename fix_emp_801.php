<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Employee;

$e = Employee::find(801);
if ($e) {
    echo "Current Info: Mobile: '{$e->mobile_no}', Email: '{$e->email}'\n";
    
    // Fix mobile (add leading 0 if starting with 8)
    if (strpos($e->mobile_no, '8') === 0 && strlen($e->mobile_no) <= 11) {
        $e->mobile_no = '0' . $e->mobile_no;
    }
    
    // Fix email (make it unique but valid format)
    if (strpos($e->email, '@') !== false) {
        $clean = str_replace('@', '.', $e->email);
        $e->email = "unlinked.801." . $clean . "@example.com";
    }
    
    echo "Fixed Info: Mobile: '{$e->mobile_no}', Email: '{$e->email}'\n";
    $e->save();
} else {
    echo "Employee 801 not found";
}
