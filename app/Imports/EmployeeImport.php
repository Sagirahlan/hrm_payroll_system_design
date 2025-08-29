<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\AppointmentType;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class EmployeeImport implements ToModel
{
    public function model(array $row)
    {
        // Skip header or empty rows
        if ($row[0] === 'first_name' || $row[0] === null) {
            return null;
        }

        return new Employee([
            'first_name' => $row[0],
            'surname' => $row[1],
            'middle_name' => $row[2],
            'gender' => $row[3],

            'date_of_birth' => $this->transformDate($row[4]),
            'state_of_origin' => $row[5],
            'lga' => $row[6],
            'nationality' => $row[7],
            'nin' => $row[8],
            'mobile_no' => $this->transformPhone($row[9]),
            'email' => $row[10],
            'address' => $row[11],

            'date_of_first_appointment' => $this->transformDate($row[12]),
            'cadre_id' => $row[13],
            'reg_no' => $row[14],
            'scale_id' => $row[15],
            'department_id' => $row[16],

            'expected_next_promotion' => $this->transformDate($row[17]),
            'expected_retirement_date' => $this->transformDate($row[18]),
            'status' => $row[19],
            'highest_certificate' => $row[20],
            'grade_level_limit' => $row[21],
            'appointment_type_id' => AppointmentType::where('name', $row[22])->first()->id ?? null,
        ]);
    }

    private function transformDate($value)
    {
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
        // If it's numeric, format it as Nigerian phone number
        return preg_replace('/[^0-9]/', '', $value);
    }
}
