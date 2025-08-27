<?php

namespace App\Exports;

use App\Models\PayrollRecord;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class PayrollRecordsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $payrolls;
    protected $title;

    public function __construct($payrolls = null, $title = 'Payroll Records')
    {
        $this->payrolls = $payrolls;
        $this->title = $title;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        if ($this->payrolls) {
            return $this->payrolls;
        }

        return PayrollRecord::with(['employee', 'salaryScale', 'transaction'])
                           ->orderBy('created_at', 'desc')
                           ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Payroll ID',
            'Employee ID',
            'First Name',
            'Last Name',
            'Full Name',
            'Email',
            'Payroll Month',
            'Basic Salary (₦)',
            'Total Additions (₦)',
            'Total Deductions (₦)',
            'Net Salary (₦)',
            'Status',
            'Payment Date',
            'Bank Name',
            'Account Number',
            'Account Name',
            'Transaction Status',
            'Remarks',
            'Created Date',
            'Updated Date'
        ];
    }

    /**
     * @param mixed $payroll
     * @return array
     */
    public function map($payroll): array
    {
        return [
            $payroll->payroll_id,
            $payroll->employee->employee_id ?? 'N/A',
            $payroll->employee->first_name ?? 'N/A',
            $payroll->employee->surname ?? 'N/A',
            ($payroll->employee->first_name ?? '') . ' ' . ($payroll->employee->surname ?? ''),
            $payroll->employee->email ?? 'N/A',
            $payroll->payroll_month ? Carbon::parse($payroll->payroll_month)->format('M Y') : 'N/A',
            number_format($payroll->basic_salary, 2),
            number_format($payroll->total_additions, 2),
            number_format($payroll->total_deductions, 2),
            number_format($payroll->net_salary, 2),
            $payroll->status,
            $payroll->payment_date ? $payroll->payment_date->format('Y-m-d') : 'Pending',
            $payroll->employee->bank->bank_name ?? 'N/A',
            $payroll->employee->bank->account_no ?? 'N/A',
            $payroll->employee->bank->account_name ?? 'N/A',
            optional($payroll->transaction)->status ?? 'No Transaction',
            $payroll->remarks ?? 'N/A',
            $payroll->created_at ? $payroll->created_at->format('Y-m-d H:i:s') : 'N/A',
            $payroll->updated_at ? $payroll->updated_at->format('Y-m-d H:i:s') : 'N/A',
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15,  // Payroll ID
            'B' => 15,  // Employee ID
            'C' => 15,  // First Name
            'D' => 15,  // Last Name
            'E' => 25,  // Full Name
            'F' => 25,  // Email
            'G' => 15,  // Payroll Month
            'H' => 18,  // Basic Salary
            'I' => 18,  // Total Additions
            'J' => 18,  // Total Deductions
            'K' => 18,  // Net Salary
            'L' => 12,  // Status
            'M' => 15,  // Payment Date
            'N' => 20,  // Bank Name
            'O' => 18,  // Account Number
            'P' => 25,  // Account Name
            'Q' => 18,  // Transaction Status
            'R' => 30,  // Remarks
            'S' => 20,  // Created Date
            'T' => 20,  // Updated Date
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();

        return [
            // Header row styling
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            
            // All data styling
            "A1:{$lastColumn}{$lastRow}" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],

            // Currency columns alignment
            "H2:K{$lastRow}" => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
                'numberFormat' => [
                    'formatCode' => '#,##0.00',
                ],
            ],

            // Status column styling
            "L2:L{$lastRow}" => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'font' => [
                    'bold' => true,
                ],
            ],

            // Date columns styling
            "M2:M{$lastRow}" => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'numberFormat' => [
                    'formatCode' => 'yyyy-mm-dd',
                ],
            ],

            "S2:T{$lastRow}" => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'numberFormat' => [
                    'formatCode' => 'yyyy-mm-dd hh:mm:ss',
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }
}