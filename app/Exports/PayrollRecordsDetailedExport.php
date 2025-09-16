<?php

namespace App\Exports;

use App\Models\PayrollRecord;
use App\Models\Deduction;
use App\Models\Addition;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PayrollRecordsDetailedExport implements WithMultipleSheets
{
    protected $payrolls;

    public function __construct($payrolls = null)
    {
        $this->payrolls = $payrolls;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Summary sheet
        $sheets['Summary'] = new \App\Exports\PayrollRecordsExport($this->payrolls);

        // Deductions sheet
        $sheets['Deductions'] = new \App\Exports\DeductionsExport($this->payrolls);

        // Additions sheet
        $sheets['Additions'] = new \App\Exports\AdditionsExport($this->payrolls);

        return $sheets;
    }
}