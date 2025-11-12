<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\AppointmentType;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class EmployeeImport implements ToModel, WithValidation, WithHeadingRow
{
    public function model(array $row)
    {
        // Skip empty rows
        if (!isset($row['first_name']) || trim($row['first_name']) === '') {
            return null;
        }
        
        return new Employee([
            'first_name' => $row['first_name'] ?? null,
            'surname' => $row['surname'] ?? null,
            'middle_name' => $row['middle_name'] ?? null,
            'gender' => $row['gender'] ?? null,
            'date_of_birth' => $this->transformDate($row['date_of_birth'] ?? null),
            'state_of_origin' => $row['state_of_origin'] ?? null,
            'lga' => $row['lga'] ?? null,
            'nationality' => $row['nationality'] ?? null,
            'nin' => $row['nin'] ?? null,
            'mobile_no' => $this->transformPhone($row['mobile_no'] ?? null),
            'email' => $row['email'] ?? null,
            'address' => $row['address'] ?? null,
            'date_of_first_appointment' => $this->transformDate($row['date_of_first_appointment'] ?? null),
            'cadre_id' => $row['cadre_id'] ?? null,
            'staff_no' => $row['staff_no'] ?? null,
            'scale_id' => $row['scale_id'] ?? null,
            'department_id' => $row['department_id'] ?? null,
            'expected_next_promotion' => $this->transformDate($row['expected_next_promotion'] ?? null),
            'expected_retirement_date' => $this->transformDate($row['expected_retirement_date'] ?? null),
            'status' => $row['status'] ?? null,
            'highest_certificate' => $row['highest_certificate'] ?? null,
            'grade_level_limit' => $row['grade_level_limit'] ?? null,
            'appointment_type_id' => $row['appointment_type_id'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            // Define validation rules if needed
        ];
    }

    private function transformDate($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            } else {
                return Carbon::parse($value)->format('Y-m-d');
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    private function transformPhone($value)
    {
        if ($value === null) {
            return null;
        }
        
        // If it's numeric, format it as Nigerian phone number
        return preg_replace('/[^0-9]/', '', $value);
    }
}
