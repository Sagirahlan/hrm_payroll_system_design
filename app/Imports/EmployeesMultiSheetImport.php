<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EmployeesMultiSheetImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        // This will now be handled dynamically in the controller
        // For now, return the default mapping but the controller will override this
        return [
            0 => new EmployeeImport(),      // First sheet for Employees
            1 => new NextOfKinImport(),     // Second sheet for Next of Kin  
            2 => new BankDetailImport(),    // Third sheet for Bank Details
        ];
    }
}
