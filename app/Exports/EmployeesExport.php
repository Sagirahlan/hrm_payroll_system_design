<?php
namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EmployeesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Employee::with(['department', 'cadre', 'gradeLevel'])
            ->get()
            ->map(function ($employee) {
                return [
                    'Employee ID' => $employee->employee_id,
                    'First Name' => $employee->first_name,
                    'Surname' => $employee->surname,
                    'Gender' => $employee->gender,
                    'Date of Birth' => $employee->date_of_birth,
                    'Phone' => $employee->mobile_no,
                    'Email' => $employee->email,
                    'Department' => $employee->department->department_name ?? 'N/A',
                    'Cadre' => $employee->cadre->cadre_name ?? 'N/A',
                    'Grade Level' => $employee->gradeLevel->name ?? 'N/A',
                    'Date of First Appointment' => $employee->date_of_first_appointment,
                    'Expected Retirement Date' => $employee->expected_retirement_date,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Employee ID',
            'First Name',
            'Surname',
            'Gender',
            'Date of Birth',
            'Phone',
            'Email',
            'Department',
            'Cadre',
            'Grade Level',
            'Date of First Appointment',
            'Expected Retirement Date',
        ];
    }
}
