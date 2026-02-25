<?php
/**
 * Bank Data Correction Script
 * 
 * This script reads the Excel import file, matches employees by staff_no,
 * and corrects any mismatched bank records in the database.
 * 
 * Usage: php fix_bank_data.php <excel_file_path>
 * Example: php fix_bank_data.php active_permanent_staff.xlsx
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Employee;
use App\Models\Bank;
use PhpOffice\PhpSpreadsheet\IOFactory;

// Get file path from argument or use default
$filePath = $argv[1] ?? null;

if (!$filePath) {
    echo "Usage: php fix_bank_data.php <excel_file_path>\n";
    echo "\nAvailable Excel files:\n";
    foreach (glob(__DIR__ . '/*.xlsx') as $file) {
        echo "  - " . basename($file) . "\n";
    }
    exit(1);
}

if (!file_exists($filePath)) {
    // Try relative to project root
    $filePath = __DIR__ . '/' . $filePath;
    if (!file_exists($filePath)) {
        echo "Error: File not found: {$filePath}\n";
        exit(1);
    }
}

echo "=== BANK DATA CORRECTION SCRIPT ===\n";
echo "Reading file: {$filePath}\n\n";

$spreadsheet = IOFactory::load($filePath);
$sheetCount = $spreadsheet->getSheetCount();

echo "Found {$sheetCount} sheet(s)\n\n";

// ===== Step 1: Read Employee Sheet (Sheet 1) to build staff_no -> Excel employee_id mapping =====
echo "--- Step 1: Reading Employee Sheet ---\n";
$employeeSheet = $spreadsheet->getSheet(0);
$employeeRows = $employeeSheet->toArray(null, true, true, true);

// Detect header row
$headers = [];
$headerRowIndex = null;
foreach ($employeeRows as $rowIndex => $row) {
    $rowValues = array_map(function($v) { return strtolower(trim($v ?? '')); }, $row);
    if (in_array('staff_no', $rowValues) || in_array('first_name', $rowValues)) {
        $headers = $rowValues;
        $headerRowIndex = $rowIndex;
        break;
    }
}

if (!$headerRowIndex) {
    echo "Error: Could not find header row in Employee sheet.\n";
    echo "First row values: " . implode(', ', array_values($employeeRows[1] ?? [])) . "\n";
    exit(1);
}

// Map column letters to header names
$colMap = [];
foreach ($headers as $col => $header) {
    if (!empty($header)) {
        $colMap[$header] = $col;
    }
}

echo "Headers found at row {$headerRowIndex}: " . implode(', ', array_keys($colMap)) . "\n";

// Build mapping: Excel employee_id -> staff_no and name
$excelEmployeeMap = []; // Excel employee_id => ['staff_no' => ..., 'name' => ...]
$staffNoCol = $colMap['staff_no'] ?? $colMap['staff_id'] ?? null;
$employeeIdCol = $colMap['employee_id'] ?? $colMap['id'] ?? null;
$firstNameCol = $colMap['first_name'] ?? null;
$surnameCol = $colMap['surname'] ?? null;

if (!$staffNoCol) {
    echo "Error: Could not find 'staff_no' column in Employee sheet.\n";
    exit(1);
}

$employeeCount = 0;
foreach ($employeeRows as $rowIndex => $row) {
    if ($rowIndex <= $headerRowIndex) continue; // Skip header

    $excelEmpId = $employeeIdCol ? trim($row[$employeeIdCol] ?? '') : null;
    $staffNo = trim($row[$staffNoCol] ?? '');
    $firstName = $firstNameCol ? trim($row[$firstNameCol] ?? '') : '';
    $surname = $surnameCol ? trim($row[$surnameCol] ?? '') : '';

    if (empty($staffNo) && empty($excelEmpId)) continue;

    if ($excelEmpId) {
        $excelEmployeeMap[$excelEmpId] = [
            'staff_no' => $staffNo,
            'name' => "{$firstName} {$surname}",
        ];
    }
    $employeeCount++;
}

echo "Mapped {$employeeCount} employees from Sheet 1\n\n";

// ===== Step 2: Read Bank Sheet (Sheet 3) if it exists =====
if ($sheetCount < 3) {
    echo "No Bank sheet (Sheet 3) found. Checking if bank data is inline in Sheet 1...\n";
    
    // Check if bank columns exist in Sheet 1
    $hasBankInSheet1 = isset($colMap['bank_name']) || isset($colMap['bank']);
    if (!$hasBankInSheet1) {
        echo "No bank data found in Excel file.\n";
        exit(0);
    }
    
    echo "Found bank data in Sheet 1 (inline). Processing...\n\n";
    
    $bankNameCol = $colMap['bank_name'] ?? $colMap['bank'] ?? $colMap['bankname'] ?? null;
    $bankCodeCol = $colMap['bank_code'] ?? $colMap['bankcode'] ?? null;
    $accountNameCol = $colMap['account_name'] ?? $colMap['accountname'] ?? null;
    $accountNoCol = $colMap['account_no'] ?? $colMap['account_number'] ?? $colMap['accountno'] ?? null;
    
    $fixed = 0;
    $matched = 0;
    $notFound = 0;
    $alreadyCorrect = 0;
    
    foreach ($employeeRows as $rowIndex => $row) {
        if ($rowIndex <= $headerRowIndex) continue;
        
        $staffNo = trim($row[$staffNoCol] ?? '');
        $bankName = $bankNameCol ? trim($row[$bankNameCol] ?? '') : '';
        $accountNo = $accountNoCol ? trim($row[$accountNoCol] ?? '') : '';
        $accountName = $accountNameCol ? trim($row[$accountNameCol] ?? '') : '';
        $bankCode = $bankCodeCol ? trim($row[$bankCodeCol] ?? '') : '';
        
        if (empty($staffNo) || empty($bankName)) continue;
        
        // Find employee by staff_no
        $employee = Employee::where('staff_no', $staffNo)->first();
        if (!$employee) {
            $notFound++;
            continue;
        }
        $matched++;
        
        // Check current bank data
        $currentBank = Bank::where('employee_id', $employee->employee_id)->first();
        
        if ($currentBank && $currentBank->account_no === $accountNo && $currentBank->bank_name === $bankName) {
            $alreadyCorrect++;
            continue;
        }
        
        // Fix the bank data
        Bank::updateOrCreate(
            ['employee_id' => $employee->employee_id],
            [
                'bank_name' => $bankName,
                'bank_code' => $bankCode,
                'account_name' => $accountName ?: ($employee->first_name . ' ' . $employee->surname),
                'account_no' => $accountNo,
            ]
        );
        
        $oldBank = $currentBank ? "{$currentBank->bank_name} / {$currentBank->account_name} / {$currentBank->account_no}" : 'NO RECORD';
        echo "FIXED: Staff {$staffNo} (Employee {$employee->employee_id} - {$employee->first_name} {$employee->surname})\n";
        echo "  OLD: {$oldBank}\n";
        echo "  NEW: {$bankName} / {$accountName} / {$accountNo}\n\n";
        $fixed++;
    }
    
    echo "=== SUMMARY ===\n";
    echo "Employees matched: {$matched}\n";
    echo "Already correct: {$alreadyCorrect}\n";
    echo "Fixed: {$fixed}\n";
    echo "Not found in DB: {$notFound}\n";
    exit(0);
}

// ===== Process Sheet 3 (Bank Details) =====
echo "--- Step 2: Reading Bank Sheet (Sheet 3) ---\n";
$bankSheet = $spreadsheet->getSheet(2);
$bankRows = $bankSheet->toArray(null, true, true, true);

// Detect header row for bank sheet
$bankHeaders = [];
$bankHeaderRowIndex = null;
foreach ($bankRows as $rowIndex => $row) {
    $rowValues = array_map(function($v) { return strtolower(trim($v ?? '')); }, $row);
    if (in_array('employee_id', $rowValues) || in_array('bank_name', $rowValues)) {
        $bankHeaders = $rowValues;
        $bankHeaderRowIndex = $rowIndex;
        break;
    }
}

if (!$bankHeaderRowIndex) {
    // No header row — assume columns are: employee_id, bank_name, bank_code, account_name, account_no
    echo "No header row detected in bank sheet. Assuming columns: A=employee_id, B=bank_name, C=bank_code, D=account_name, E=account_no\n";
    $bankColMap = [
        'employee_id' => 'A',
        'bank_name' => 'B',
        'bank_code' => 'C',
        'account_name' => 'D',
        'account_no' => 'E',
    ];
    $bankHeaderRowIndex = 0; // No header to skip
} else {
    $bankColMap = [];
    foreach ($bankHeaders as $col => $header) {
        if (!empty($header)) {
            $bankColMap[$header] = $col;
        }
    }
    echo "Bank headers found at row {$bankHeaderRowIndex}: " . implode(', ', array_keys($bankColMap)) . "\n";
}

// ===== Step 3: Process each bank row and fix mismatches =====
echo "\n--- Step 3: Correcting Bank Data ---\n\n";

$fixed = 0;
$matched = 0;
$notFound = 0;
$noMapping = 0;
$alreadyCorrect = 0;

foreach ($bankRows as $rowIndex => $row) {
    if ($rowIndex <= $bankHeaderRowIndex) continue; // Skip header

    $excelEmpId = trim($row[$bankColMap['employee_id']] ?? '');
    $bankName = trim($row[$bankColMap['bank_name']] ?? '');
    $bankCode = trim($row[$bankColMap['bank_code']] ?? '');
    $accountName = trim($row[$bankColMap['account_name']] ?? '');
    $accountNo = trim($row[$bankColMap['account_no']] ?? '');

    if (empty($excelEmpId) || empty($bankName)) continue;

    // Look up staff_no from the employee mapping
    if (!isset($excelEmployeeMap[$excelEmpId])) {
        $noMapping++;
        continue;
    }

    $staffNo = $excelEmployeeMap[$excelEmpId]['staff_no'];
    $excelName = $excelEmployeeMap[$excelEmpId]['name'];

    // Find the ACTUAL employee in DB by staff_no
    $employee = Employee::where('staff_no', $staffNo)->first();
    if (!$employee) {
        echo "NOT FOUND: Staff No {$staffNo} (Excel employee_id {$excelEmpId}, {$excelName})\n";
        $notFound++;
        continue;
    }
    $matched++;

    // Check current bank data
    $currentBank = Bank::where('employee_id', $employee->employee_id)->first();

    if ($currentBank && $currentBank->account_no === $accountNo && $currentBank->bank_name === $bankName) {
        $alreadyCorrect++;
        continue;
    }

    // Fix the bank data using the CORRECT employee_id from DB
    Bank::updateOrCreate(
        ['employee_id' => $employee->employee_id],
        [
            'bank_name' => $bankName,
            'bank_code' => $bankCode,
            'account_name' => $accountName,
            'account_no' => $accountNo,
        ]
    );

    $oldBank = $currentBank ? "{$currentBank->bank_name} / {$currentBank->account_name} / {$currentBank->account_no}" : 'NO RECORD';
    echo "FIXED: Staff {$staffNo} (DB Employee {$employee->employee_id} - {$employee->first_name} {$employee->surname})\n";
    echo "  OLD: {$oldBank}\n";
    echo "  NEW: {$bankName} / {$accountName} / {$accountNo}\n\n";
    $fixed++;
}

echo "\n=== SUMMARY ===\n";
echo "Employees matched by staff_no: {$matched}\n";
echo "Already correct: {$alreadyCorrect}\n";
echo "Fixed: {$fixed}\n";
echo "Not found in DB: {$notFound}\n";
echo "No mapping (employee_id not in Sheet 1): {$noMapping}\n";
