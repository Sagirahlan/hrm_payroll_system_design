<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\Bank;
use App\Models\BankList; // Assuming you have a BankList model for valid banks
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class BankDetailsByStaffIdImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $employee = null;

        // 1. Try to find by Employee ID first (if provided)
        $employeeId = $row['employee_id'] ?? $row['id'] ?? null;
        if ($employeeId) {
            $employee = Employee::find($employeeId);
        }

        // 2. If not found by ID, try Staff Number
        if (!$employee) {
            $staffNo = $row['staff_no'] ?? $row['staff_number'] ?? $row['ippis_no'] ?? null;
            if ($staffNo) {
                $employee = Employee::where('staff_no', $staffNo)->first();
            }
        }

        if (!$employee) {
            Log::warning('Bank Update Import: Employee not found', $row);
            return null;
        }

        // 3. Extract Bank Details
        $bankName = $row['bank_name'] ?? $row['bank'] ?? null;
        $accountNo = $row['account_no'] ?? $row['account_number'] ?? $row['account_num'] ?? null;
        $accountName = $row['account_name'] ?? $row['account_holder'] ?? null;
        
        // Optional: Bank Code lookup if not provided
        $bankCode = $row['bank_code'] ?? $row['sort_code'] ?? null;
        
        if (!$bankCode && $bankName) {
             $bankList = BankList::whereRaw('LOWER(bank_name) LIKE ?', ['%' . strtolower(trim($bankName)) . '%'])->first();
             $bankCode = $bankList ? $bankList->bank_code : null;
        }

        // 4. Update or Create Bank Record
        $updateData = [];
        if (!empty($bankName)) $updateData['bank_name'] = $bankName;
        if (!empty($bankCode)) $updateData['bank_code'] = $bankCode;
        if (!empty($accountName)) $updateData['account_name'] = $accountName;
        if (!empty($accountNo)) $updateData['account_no'] = $accountNo;

        // Check for existing bank records to avoid duplicates
        $existingBank = Bank::where('employee_id', $employee->employee_id)->first();

        if ($existingBank) {
            if (!empty($updateData)) {
                $existingBank->update($updateData);
                Log::info("Bank Update Import: Updated existing bank details for Employee ID: {$employee->employee_id}");
            }
        } else {
            if (!empty($updateData)) {
                Bank::create(array_merge([
                    'employee_id' => $employee->employee_id,
                    'bank_name' => 'Unknown',
                    'bank_code' => '000',
                    'account_name' => 'Unknown',
                    'account_no' => '0000000000',
                ], $updateData));
                Log::info("Bank Update Import: Created new bank details for Employee ID: {$employee->employee_id}");
            }
        }
        
        return null;
    }
}
