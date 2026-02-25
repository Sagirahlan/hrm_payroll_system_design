<?php
/**
 * Fix the 21 mismatched bank records + employees with no bank.
 * 
 * The pattern is clear: employees 802-822 have bank data that belongs to 
 * employees 788-808 respectively. The bank data is offset by 14 positions.
 * 
 * This script swaps the bank data to the correct employees.
 */
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Employee;
use App\Models\Bank;

echo "=== BANK DATA OFFSET FIX ===\n\n";

// First, verify the pattern by showing current state
echo "--- Current state of mismatched records ---\n\n";

// The mismatched employees (have bank data belonging to someone else)
$mismatchedIds = [];
$banks = Bank::all();
foreach ($banks as $bank) {
    $emp = Employee::find($bank->employee_id);
    if (!$emp) continue;
    
    $accName = strtolower($bank->account_name ?? '');
    if (empty($accName)) continue;
    
    $firstMatch = stripos($accName, strtolower($emp->first_name)) !== false;
    $lastMatch = stripos($accName, strtolower($emp->surname)) !== false;
    
    if (!$firstMatch && !$lastMatch) {
        $mismatchedIds[] = $bank->employee_id;
        echo "WRONG: Employee {$emp->employee_id} ({$emp->first_name} {$emp->surname}) has bank for: {$bank->account_name}\n";
    }
}

echo "\n--- Employees with no bank who should have one ---\n\n";

// For each mismatched bank record, find the employee whose name matches the account_name
// and who has no bank record
$swaps = []; // [wrong_emp_id => correct_emp_id]

foreach ($mismatchedIds as $wrongEmpId) {
    $bank = Bank::where('employee_id', $wrongEmpId)->first();
    if (!$bank) continue;
    
    $accName = $bank->account_name;
    $nameParts = preg_split('/[\s,]+/', $accName);
    $nameParts = array_filter($nameParts, function($p) { return strlen(trim($p)) >= 2; });
    
    // Find employees with no bank whose name matches
    $candidates = Employee::whereDoesntHave('bank')
        ->where(function($query) use ($nameParts) {
            foreach ($nameParts as $part) {
                $part = trim($part);
                $query->where(function($q) use ($part) {
                    $q->where('first_name', 'like', "%{$part}%")
                      ->orWhere('surname', 'like', "%{$part}%")
                      ->orWhere('middle_name', 'like', "%{$part}%");
                });
            }
        })->get();
    
    if ($candidates->count() == 1) {
        $correct = $candidates->first();
        echo "Bank '{$accName}' on employee {$wrongEmpId} -> should be on employee {$correct->employee_id} ({$correct->first_name} {$correct->surname})\n";
        $swaps[$wrongEmpId] = $correct->employee_id;
    } elseif ($candidates->count() > 1) {
        echo "Bank '{$accName}' on employee {$wrongEmpId} -> MULTIPLE candidates:\n";
        foreach ($candidates as $c) {
            echo "  - ID {$c->employee_id}: {$c->first_name} {$c->surname} ({$c->staff_no})\n";
        }
    } else {
        echo "Bank '{$accName}' on employee {$wrongEmpId} -> NO candidate found with no bank\n";
    }
}

echo "\n--- Performing swaps (" . count($swaps) . " records) ---\n\n";

// Collect all the bank data before modifying
$bankDataToMove = [];
foreach ($swaps as $wrongEmpId => $correctEmpId) {
    $bank = Bank::where('employee_id', $wrongEmpId)->first();
    $bankDataToMove[$wrongEmpId] = [
        'correct_emp_id' => $correctEmpId,
        'bank_name' => $bank->bank_name,
        'bank_code' => $bank->bank_code,
        'account_name' => $bank->account_name,
        'account_no' => $bank->account_no,
    ];
}

// Now perform the moves
// Step 1: Create correct bank records for the target employees
foreach ($bankDataToMove as $wrongEmpId => $data) {
    $correctEmpId = $data['correct_emp_id'];
    $wrongEmp = Employee::find($wrongEmpId);
    $correctEmp = Employee::find($correctEmpId);
    
    Bank::create([
        'employee_id' => $correctEmpId,
        'bank_name' => $data['bank_name'],
        'bank_code' => $data['bank_code'],
        'account_name' => $data['account_name'],
        'account_no' => $data['account_no'],
    ]);
    
    echo "MOVED: '{$data['account_name']}' bank -> Employee {$correctEmpId} ({$correctEmp->first_name} {$correctEmp->surname})\n";
}

// Step 2: Delete the wrong bank records from mismatched employees  
// (they'll need to get their correct data from the Excel file later, 
//  or they may now have their correct data from another swap)
foreach ($bankDataToMove as $wrongEmpId => $data) {
    // Check if this employee now has the correct bank (from another swap)
    $currentBank = Bank::where('employee_id', $wrongEmpId)->first();
    $emp = Employee::find($wrongEmpId);
    
    if ($currentBank) {
        $accName = strtolower($currentBank->account_name ?? '');
        $firstMatch = stripos($accName, strtolower($emp->first_name)) !== false;
        $lastMatch = stripos($accName, strtolower($emp->surname)) !== false;
        
        if (!$firstMatch && !$lastMatch) {
            // Still wrong - delete it (it was already copied to the correct employee)
            // But only the one that matches the wrong data
            Bank::where('employee_id', $wrongEmpId)
                ->where('account_name', $data['account_name'])
                ->delete();
            echo "REMOVED wrong bank from Employee {$wrongEmpId} ({$emp->first_name} {$emp->surname})\n";
        }
    }
}

echo "\n--- Verification ---\n\n";

// Re-check mismatches
$remainingMismatches = 0;
$banks = Bank::all();
foreach ($banks as $bank) {
    $emp = Employee::find($bank->employee_id);
    if (!$emp) continue;
    
    $accName = strtolower($bank->account_name ?? '');
    if (empty($accName)) continue;
    
    $firstMatch = stripos($accName, strtolower($emp->first_name)) !== false;
    $lastMatch = stripos($accName, strtolower($emp->surname)) !== false;
    
    if (!$firstMatch && !$lastMatch) {
        $remainingMismatches++;
        echo "STILL MISMATCHED: Employee {$emp->employee_id} ({$emp->first_name} {$emp->surname}) has bank for: {$bank->account_name}\n";
    }
}

echo "\nRemaining mismatches: {$remainingMismatches}\n";
echo "Total bank records: " . Bank::count() . "\n";
echo "Employees without bank: " . Employee::whereDoesntHave('bank')->count() . "\n";
