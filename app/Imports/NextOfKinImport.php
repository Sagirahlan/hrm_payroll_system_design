<?php

namespace App\Imports;

use App\Models\NextOfKin;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;


class NextOfKinImport implements ToModel
{
    public function model(array $row)
    {
        if ($row[0] === 'employee_id' || $row[0] === null || trim($row[0]) === '') {
            return null;
        }

        // Check if the employee_id exists in the employees table before creating NextOfKin
        if (!\App\Models\Employee::where('employee_id', $row[0])->exists()) {
            // Optionally, you could log this or handle it differently
            return null;
        }

        return new NextOfKin([
            'employee_id' => $row[0] ?? null,
            'name' => $row[1] ?? null,
            'relationship' => $row[2] ?? null,
            'mobile_no' => $this->transformPhone($row[3] ?? null),
            'address' => $row[4] ?? null,
        ]);
    }

    private function transformPhone($value)
    {
        if ($value === null) {
            return null;
        }
        
        return preg_replace('/[^0-9]/', '', $value);
    }
}
