<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check pensioner bank data
$pensioners = \App\Models\Pensioner::where('status', 'Active')->get();
$withBank = 0;
$withoutBank = 0;
$withAccountNo = 0;
$withoutAccountNo = 0;
$sample = [];

foreach ($pensioners as $p) {
    if ($p->bank_id) {
        $withBank++;
    } else {
        $withoutBank++;
    }
    if ($p->account_number && $p->account_number !== '0000000000') {
        $withAccountNo++;
    } else {
        $withoutAccountNo++;
        if (count($sample) < 3) {
            $sample[] = "{$p->full_name} (ID:{$p->employee_id}) - bank_id:{$p->bank_id}, account_number:{$p->account_number}";
        }
    }
}

echo "=== Pensioner Bank Data Status ===\n";
echo "Total active pensioners: " . $pensioners->count() . "\n";
echo "With bank_id: $withBank\n";
echo "Without bank_id: $withoutBank\n";
echo "With valid account_number: $withAccountNo\n";
echo "Without valid account_number: $withoutAccountNo\n";
echo "\nSample without account_number:\n";
foreach ($sample as $s) {
    echo "  - $s\n";
}

// Also check employee bank records for pensioners
echo "\n=== Employee Bank Data for Pensioners ===\n";
$count = 0;
$withEmployeeBank = 0;
foreach ($pensioners->take(10) as $p) {
    $emp = $p->employee;
    if ($emp) {
        $bank = $emp->bank;
        if ($bank) {
            $withEmployeeBank++;
            echo "  {$p->full_name}: employee bank found - account_no:{$bank->account_no}, bank_code:{$bank->bank_code}\n";
        } else {
            echo "  {$p->full_name}: NO employee bank record\n";
        }
    }
    $count++;
}
echo "Of first 10: $withEmployeeBank have employee bank records\n";
