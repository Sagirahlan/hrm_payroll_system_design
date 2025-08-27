<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EmployeesMultiSheetImport implements WithMultipleSheets
{
    protected $employeeMap = [];

    public function sheets(): array
    {
        $employeeImport = new EmployeeImport();
        $nextOfKinImport = new NextOfKinImport($employeeImport);
        $bankDetailImport = new BankDetailImport($employeeImport);

        return [
            'Employees' => $employeeImport,
            'NextOfKin' => $nextOfKinImport,
            'BankDetails' => $bankDetailImport,
        ];
    }
}
