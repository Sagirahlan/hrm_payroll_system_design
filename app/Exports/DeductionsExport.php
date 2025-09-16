<?php

namespace App\Exports;

use App\Models\Deduction;
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

class DeductionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $payrolls;
    protected $deductionTypes;

    public function __construct($payrolls = null)
    {
        $this->payrolls = $payrolls;
        // Get all unique deduction types to create dynamic columns
        $this->deductionTypes = $this->getUniqueDeductionTypes();
    }

    private function getUniqueDeductionTypes()
    {
        $payrollRecords = $this->payrolls ?? PayrollRecord::all();
        $types = collect();
        
        foreach ($payrollRecords as $payroll) {
            $employeeDeductions = Deduction::where('employee_id', $payroll->employee_id)->get();
            foreach ($employeeDeductions as $deduction) {
                if (!$types->contains($deduction->deduction_type)) {
                    $types->push($deduction->deduction_type);
                }
            }
        }
        
        return $types->sort()->values();
    }

    public function collection()
    {
        $payrollRecords = $this->payrolls ?? PayrollRecord::with('employee')->get();
        $exportData = collect();
        
        foreach ($payrollRecords as $index => $payroll) {
            // Get all deductions for this employee
            $employeeDeductions = Deduction::where('employee_id', $payroll->employee_id)
                ->get()
                ->keyBy('deduction_type');
            
            $rowData = [
                'sn' => $index + 1,
                'employee_id' => $payroll->employee->employee_id ?? 'N/A',
                'employee_name' => trim(($payroll->employee->first_name ?? '') . ' ' . ($payroll->employee->surname ?? '')),
                'payroll_month' => $payroll->payroll_month ? Carbon::parse($payroll->payroll_month)->format('M Y') : 'N/A',
            ];
            
            // Add deduction amounts for each type
            foreach ($this->deductionTypes as $type) {
                $rowData['deduction_' . str_replace(' ', '_', strtolower($type))] = 
                    $employeeDeductions->has($type) ? $employeeDeductions[$type]->amount : 0;
            }
            
            // Calculate total deductions
            $totalDeductions = 0;
            foreach ($this->deductionTypes as $type) {
                $totalDeductions += $employeeDeductions->has($type) ? $employeeDeductions[$type]->amount : 0;
            }
            $rowData['total_deductions'] = $totalDeductions;
            
            $exportData->push($rowData);
        }
        
        return $exportData;
    }

    public function headings(): array
    {
        $headings = [
            'S/N',
            'EMP ID',
            'EMPLOYEE NAME',
            'MONTH'
        ];
        
        // Add deduction type columns
        foreach ($this->deductionTypes as $type) {
            $headings[] = strtoupper($type);
        }
        
        $headings[] = 'TOTAL DEDUCTIONS';
        
        return $headings;
    }

    public function map($row): array
    {
        $mappedRow = [
            $row['sn'],
            $row['employee_id'],
            $row['employee_name'],
            $row['payroll_month']
        ];
        
        // Add deduction amounts
        foreach ($this->deductionTypes as $type) {
            $key = 'deduction_' . str_replace(' ', '_', strtolower($type));
            $mappedRow[] = $row[$key] > 0 ? number_format($row[$key], 2) : '0.00';
        }
        
        $mappedRow[] = number_format($row['total_deductions'], 2);
        
        return $mappedRow;
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 8,   // S/N
            'B' => 12,  // EMP ID
            'C' => 25,  // Employee Name
            'D' => 12,  // Month
        ];
        
        // Dynamic widths for deduction columns
        $column = 'E';
        foreach ($this->deductionTypes as $type) {
            $widths[$column] = 15;
            $column++;
        }
        
        $widths[$column] = 18; // Total Deductions
        
        return $widths;
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = chr(ord('D') + count($this->deductionTypes) + 1);
        
        // Get the number of data rows
        $dataRows = $this->collection()->count();
        $lastRow = $dataRows + 1; // +1 for header row
        
        return [
            // Header row styling
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1F4E79'], // Blue header like in the image
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ],
            
            // Data rows styling
            'A2:' . $lastColumn . $lastRow => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            
            // Amount columns alignment (right-aligned for better readability)
            'E2:' . $lastColumn . $lastRow => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'numberFormat' => [
                    'formatCode' => '#,##0.00',
                ],
            ],
        ];
    }

    public function title(): string
    {
        $month = $this->payrolls && $this->payrolls->first() 
            ? Carbon::parse($this->payrolls->first()->payroll_month)->format('F Y') 
            : 'All Months';
            
        return "DEDUCTIONS SUMMARY - {$month}";
    }
}