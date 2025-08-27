<?php
namespace App\Imports;

use App\Models\Bank;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BankDetailImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Check if the employee_id exists in the employees table before creating Bank
            if (\App\Models\Employee::where('employee_id', $row['employee_id'])->exists()) {
                Bank::create([
                    'employee_id' => $row['employee_id'],
                    'bank_name' => $row['bank_name'],
                                        'bank_code' => $row['bank_code'],
                    'account_name' => $row['account_name'],
                    'account_no' => $row['account_no'],
                ]);
            }
        }
    }
}
