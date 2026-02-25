<?php

namespace App\Imports;

use App\Models\NextOfKin;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class NextOfKinImport implements ToCollection, WithHeadingRow
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
        foreach ($rows as $row) {
            $excelEmployeeId = $row['employee_id'] ?? null;

            // Skip rows without employee_id or where the employee doesn't exist
            if (empty($excelEmployeeId) || !is_numeric($excelEmployeeId)) {
                continue;
            }

            // Translate Excel employee_id to DB employee_id if mapping exists
            $dbEmployeeId = $excelEmployeeId;
            if ($this->idMapping !== null) {
                if (!isset($this->idMapping[$excelEmployeeId])) {
                    \Illuminate\Support\Facades\Log::warning("NextOfKinImport: Skipping - Excel employee_id {$excelEmployeeId} not found in ID mapping.");
                    continue;
                }
                $dbEmployeeId = $this->idMapping[$excelEmployeeId];
            }

            if (!\App\Models\Employee::where('employee_id', $dbEmployeeId)->exists()) {
                \Illuminate\Support\Facades\Log::warning("NextOfKinImport: Skipping - DB employee_id {$dbEmployeeId} (Excel: {$excelEmployeeId}) not found in employees table.");
                continue;
            }

            // Use updateOrCreate to prevent duplicates — keyed on employee_id
            NextOfKin::updateOrCreate(
                ['employee_id' => $dbEmployeeId],
                [
                    'name' => $row['name'] ?? $row['next_of_kin_name'] ?? null,
                    'relationship' => $row['relationship'] ?? $row['next_of_kin_relationship'] ?? null,
                    'mobile_no' => $this->transformPhone($row['mobile_no'] ?? $row['next_of_kin_mobile'] ?? null),
                    'address' => $row['address'] ?? $row['next_of_kin_address'] ?? null,
                    'occupation' => $row['occupation'] ?? $row['next_of_kin_occupation'] ?? null,
                    'place_of_work' => $row['place_of_work'] ?? $row['next_of_kin_place_of_work'] ?? null,
                ]
            );
        }
    }

    private function transformPhone($value)
    {
        if ($value === null) {
            return null;
        }

        return preg_replace('/[^0-9]/', '', $value);
    }
}
