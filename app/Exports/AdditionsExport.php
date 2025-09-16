<?php

namespace App\Exports;

use App\Models\Addition;
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

class AdditionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $payrolls;
    protected $additionTypes;

    public function __construct($payrolls = null)
    {
        $this->payrolls = $payrolls;
        // Get all unique addition types to create dynamic columns
        $this->additionTypes = $this->getUniqueAdditionTypes();
    }

    private function getUniqueAdditionTypes()
    {
        $payrollRecords = $this->payrolls ?? PayrollRecord::all();
        $types = collect();
        
        foreach ($payrollRecords as $payroll) {
            $employeeAdditions = Addition::where('employee_id', $payroll->employee_id)->get();
            foreach ($employeeAdditions as $addition) {
                if (!$types->contains($addition->addition_type)) {
                    $types->push($addition->addition_type);
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
            // Get all additions for this employee
            $employeeAdditions = Addition::where('employee_id', $payroll->employee_id)
                ->get()
                ->keyBy('addition_type');
            
            $rowData = [
                'sn' => $index + 1,
                'employee_id' => $payroll->employee->employee_id ?? 'N/A',
                'employee_name' => trim(($payroll->employee->first_name ?? '') . ' ' . ($payroll->employee->surname ?? '')),
                'payroll_month' => $payroll->payroll_month ? Carbon::parse($payroll->payroll_month)->format('M Y') : 'N/A',
            ];
            
            // Add addition amounts for each type
            foreach ($this->additionTypes as $type) {
                $rowData['addition_' . str_replace(' ', '_', strtolower($type))] = 
                    $employeeAdditions->has($type) ? $employeeAdditions[$type]->amount : 0;
            }
            
            // Calculate total additions
            $totalAdditions = 0;
            foreach ($this->additionTypes as $type) {
                $totalAdditions += $employeeAdditions->has($type) ? $employeeAdditions[$type]->amount : 0;
            }
            $rowData['total_additions'] = $totalAdditions;
            
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
        
        // Add addition type columns
        foreach ($this->additionTypes as $type) {
            $headings[] = strtoupper($type);
        }
        
        $headings[] = 'TOTAL ADDITIONS';
        
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
        
        // Add addition amounts
        foreach ($this->additionTypes as $type) {
            $key = 'addition_' . str_replace(' ', '_', strtolower($type));
            $mappedRow[] = $row[$key] > 0 ? number_format($row[$key], 2) : '0.00';
        }
        
        $mappedRow[] = number_format($row['total_additions'], 2);
        
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
        
        // Dynamic widths for addition columns
        $column = 'E';
        foreach ($this->additionTypes as $type) {
            $widths[$column] = 15;
            $column++;
        }
        
        $widths[$column] = 18; // Total Additions
        
        return $widths;
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = chr(ord('D') + count($this->additionTypes) + 1);
        
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
                    'startColor' => ['rgb' => '70AD47'], // Green header for additions
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
            
        return "ADDITIONS SUMMARY - {$month}";
    }
}