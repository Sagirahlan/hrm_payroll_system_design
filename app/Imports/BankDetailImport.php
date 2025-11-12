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
            if (isset($row['employee_id']) && \App\Models\Employee::where('employee_id', $row['employee_id'])->exists()) {
                Bank::updateOrCreate(
                    [
                        'employee_id' => $row['employee_id']
                    ],
                    [
                        'bank_name' => $row['bank_name'] ?? null,
                        'bank_code' => $row['bank_code'] ?? null,
                        'account_name' => $row['account_name'] ?? null,
                        'account_no' => $row['account_no'] ?? null,
                    ]
                );
            }
        }
    }
}
