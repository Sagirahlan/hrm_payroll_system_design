<?php

use Carbon\Carbon;
use App\Services\PensionCalculationService;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new PensionCalculationService();

$apptDate = Carbon::parse('2015-08-27');
$retirementDate = Carbon::parse('2025-12-11');

echo "Appt Date: " . $apptDate->toDateString() . "\n";
echo "Retirement Date: " . $retirementDate->toDateString() . "\n\n";

// 1. Check Date Span
$span = $service->getDateSpan($apptDate, $retirementDate);
echo "Date Span: " . json_encode($span) . "\n";

// 2. Check Elevated Status
$isElevated = ($span['months'] >= 6) ? 1 : 0;
echo "Is Elevated: $isElevated\n";

// 3. Check Computation Years
$years = $service->getComputationYears($isElevated, $span['years']);
echo "Computation Years: $years\n";

// 4. Check Percentage
$percentages = $service->getComputationPercentages($years);
echo "Percentages: " . json_encode($percentages) . "\n";

// 5. Check Database directly for 10 years
$dbRow = DB::table('compute_percentage')->where('years_of_service', 10)->first();
echo "DB Row for 10 years: " . json_encode($dbRow) . "\n";
