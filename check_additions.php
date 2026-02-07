<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check for employee 901003
$employee = \App\Models\Employee::where('staff_no', '901003')->first();

if (!$employee) {
    echo "Employee 901003 not found\n";
    exit;
}

echo "Employee: {$employee->first_name} {$employee->surname}\n";
echo "Employee ID: {$employee->employee_id}\n";
echo "Status: {$employee->status}\n\n";

// Check additions
$additions = \App\Models\Addition::where('employee_id', $employee->employee_id)->get();
echo "Total Additions: {$additions->count()}\n";

foreach ($additions as $add) {
    echo "  - Type: {$add->addition_type}\n";
    echo "    Amount: ₦{$add->amount}\n";
    echo "    Period: {$add->addition_period}\n";
    echo "    Start: {$add->start_date}\n";
    echo "    End: " . ($add->end_date ?? 'NULL') . "\n\n";
}

// Test the query logic for Feb 2026
$month = '2026-02';
echo "\nTesting query for month: {$month}\n";

$testAdditions = \App\Models\Addition::where('employee_id', $employee->employee_id)
    ->where(function($query) use ($month) {
        $query->where(function($q) use ($month) {
            // Monthly or Perpetual additions that are active in this month
            $q->whereIn('addition_period', ['Monthly', 'Perpetual'])
              ->where('start_date', '<=', $month . '-01')
              ->where(function($dateQuery) use ($month) {
                  $dateQuery->whereNull('end_date')
                           ->orWhere('end_date', '>=', $month . '-01');
              });
        })->orWhere(function($q) use ($month) {
            // OneTime additions for this specific month
            $q->where('addition_period', 'OneTime')
              ->whereYear('start_date', '=', explode('-', $month)[0])
              ->whereMonth('start_date', '=', explode('-', $month)[1]);
        });
    })
    ->get();

echo "Additions found for {$month}: {$testAdditions->count()}\n";
foreach ($testAdditions as $add) {
    echo "  - {$add->addition_type}: ₦{$add->amount}\n";
}
