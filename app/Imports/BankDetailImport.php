<?php
namespace App\Imports;

use App\Models\Bank;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BankDetailImport implements ToCollection, WithHeadingRow
{
    /**
     * Mapping of Excel employee_id => DB employee_id.
     * When provided, the importer translates Excel IDs to actual DB IDs,
     * preventing data from being assigned to the wrong employee.
     */
    private ?array $idMapping;

    public function __construct(?array $idMapping = null)
    {
        $this->idMapping = $idMapping;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $excelEmployeeId = $row['employee_id'] ?? null;

            // Skip rows without employee_id
            if (empty($excelEmployeeId) || !is_numeric($excelEmployeeId)) {
                continue;
            }

            // Translate Excel employee_id to DB employee_id if mapping exists
            $dbEmployeeId = $excelEmployeeId;
            if ($this->idMapping !== null) {
                if (!isset($this->idMapping[$excelEmployeeId])) {
                    \Illuminate\Support\Facades\Log::warning("BankDetailImport: Skipping row {$index} - Excel employee_id {$excelEmployeeId} not found in ID mapping.");
                    continue;
                }
                $dbEmployeeId = $this->idMapping[$excelEmployeeId];
            }

            // Check if the employee_id exists in the employees table
            if (!\App\Models\Employee::where('employee_id', $dbEmployeeId)->exists()) {
                \Illuminate\Support\Facades\Log::warning("BankDetailImport: Skipping row {$index} - DB employee_id {$dbEmployeeId} (Excel: {$excelEmployeeId}) not found in employees table.");
                continue;
            }

            \Illuminate\Support\Facades\Log::info("BankDetailImport: Updating/creating bank for DB employee_id {$dbEmployeeId} (Excel: {$excelEmployeeId})", [
                'bank_name' => $row['bank_name'] ?? null,
                'account_no' => $row['account_no'] ?? null,
            ]);

            Bank::updateOrCreate(
                ['employee_id' => $dbEmployeeId],
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
