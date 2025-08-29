<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SingleEmployeeExport implements FromCollection, WithHeadings
{
    protected $employeeId;

    public function __construct($employeeId)
    {
        $this->employeeId = $employeeId;
    }

    public function collection()
    {
        $employee = Employee::with(['department', 'cadre', 'gradeLevel', 'payrollRecords'])
            ->where('employee_id', $this->employeeId)
            ->firstOrFail();

        $lastPayroll = $employee->payrollRecords->sortByDesc('created_at')->first();
        $salary = $lastPayroll?->gross_salary ?? 0;
        $years = now()->diffInYears($employee->date_of_first_appointment);
        $gratuity = $salary * 0.1 * $years;

        return collect([
            [
                'Employee ID' => $employee->employee_id,
                'First Name' => $employee->first_name,
                'Surname' => $employee->surname,
                'Gender' => $employee->gender,
                'Department' => $employee->department->department_name ?? 'N/A',
                'Cadre' => $employee->cadre->cadre_name ?? 'N/A',
                'Grade Level' => $employee->gradeLevel->name ?? 'N/A',
                'Years of Service' => $years,
                'Gratuity' => $gratuity,
                'Expected Retirement Date' => $employee->expected_retirement_date,
                'Status' => $employee->status,
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'Employee ID',
            'First Name',
            'Surname',
            'Gender',
            'Department',
            'Cadre',
            'Grade Level',
            'Years of Service',
            'Gratuity',
            'Expected Retirement Date',
            'Status',
        ];
    }
}
