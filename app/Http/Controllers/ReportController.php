<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Employee;
use App\Models\Department;
use App\Services\EmployeeReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDF;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(EmployeeReportService $reportService)
    {
       $this->middleware(['auth', 'permission:manage_employees']);

        $this->reportService = $reportService;
    }

    public function index()
    {
        $reports = Report::with(['generatedBy', 'employee'])
            ->orderBy('generated_date', 'desc') 
            ->paginate(20);

        return view('reports.index', compact('reports'));
    }

    public function create(Request $request)
    {
        $query = Employee::with('department');
        
        // Apply search filter
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('employee_id', 'like', "%{$searchTerm}%")
                  ->orWhere(DB::raw("CONCAT(first_name, ' ', middle_name, ' ', surname)"), 'like', "%{$searchTerm}%")
                  ->orWhere(DB::raw("CONCAT(first_name, ' ', surname)"), 'like', "%{$searchTerm}%");
            });
        }
        
        // Apply department filter
        if ($request->has('department') && $request->department) {
            $query->where('department_id', $request->department);
        }
        
        // Apply status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Order and paginate
        $employees = $query->orderBy('first_name')->paginate(20);
        
        // Get all departments for the filter dropdown
        $departments = Department::all();
        
        return view('reports.create', compact('employees', 'departments'));
    }

    public function generateEmployeeReport(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'report_type' => 'required|in:comprehensive,basic,disciplinary,payroll,retirement',
            'export_format' => 'required|in:PDF,Excel'
        ]);

        $employee = Employee::with([
            'disciplinaryRecords',
            'payrollRecords',
            'deductions',
            'additions'
                
            
        ])->findOrFail($request->employee_id);

        // Generate report data
        $reportData = $this->reportService->generateEmployeeReportData($employee, $request->report_type);

        // Create report record
        $report = Report::create([
            'report_type' => $request->report_type,
            'generated_by' => Auth::id(),
            'generated_date' => now(),
            'report_data' => json_encode($reportData), // 👈 Encode array as JSON
            'export_format' => $request->export_format,
            'employee_id' => $employee->employee_id
        ]);

        // Generate file based on format
        if ($request->export_format === 'PDF') {
            $this->generatePDF($report, $employee, $reportData);
        } else {
            $this->generateExcel($report, $employee, $reportData);
        }

        return redirect()->route('reports.index')->with('success', 'Report generated successfully.');
    }

    public function bulkGenerate(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|string',
            'report_type' => 'required|in:comprehensive,basic,disciplinary,payroll,retirement',
            'export_format' => 'required|in:PDF,Excel',
        ]);

        $employeeIds = explode(',', $request->employee_ids);

        foreach ($employeeIds as $employeeId) {
            $employee = Employee::with([
                'disciplinaryRecords',
                'payrollRecords',
                'deductions',
                'additions'
            ])->find($employeeId);

            if ($employee) {
                $reportData = $this->reportService->generateEmployeeReportData($employee, $request->report_type);

                $report = Report::create([
                    'report_type' => $request->report_type,
                    'generated_by' => Auth::id(),
                    'generated_date' => now(),
                    'report_data' => json_encode($reportData),
                    'export_format' => $request->export_format,
                    'employee_id' => $employee->employee_id,
                ]);

                if ($request->export_format === 'PDF') {
                    $this->generatePDF($report, $employee, $reportData);
                } else {
                    $this->generateExcel($report, $employee, $reportData);
                }
            }
        }

        return redirect()->route('reports.index')->with('success', 'Bulk reports generated successfully.');
    }


    private function generatePDF($report, $employee, $reportData)
    {
        $pdf = PDF::loadView('reports.pdf.employee-report', [
            'employee' => $employee,
            'data' => $reportData,
            'report' => $report
        ]);

        $fileName = "employee_report_{$employee->employee_id}_" . now()->format('Y_m_d_H_i_s') . '.pdf';
        $filePath = "reports/pdf/{$fileName}";

        // Save PDF to storage
        Storage::put($filePath, $pdf->output());

        // Update report with file path
        $report->update(['file_path' => $filePath]);
    }

    private function generateExcel($report, $employee, $reportData)
    {
        $fileName = "employee_report_{$employee->employee_id}_" . now()->format('Y_m_d_H_i_s') . '.csv';
        $filePath = "reports/excel/{$fileName}";

        $file = fopen('php://temp', 'w+');

        // Employee Info
        fputcsv($file, ['Employee Information']);
        foreach ($reportData['employee_info'] as $key => $value) {
            fputcsv($file, [$key, $value]);
        }
        fputcsv($file, []); // Add a blank line

        // Disciplinary Actions
        if (!empty($reportData['disciplinary_actions'])) {
            fputcsv($file, ['Disciplinary Actions']);
            fputcsv($file, array_keys($reportData['disciplinary_actions'][0]));
            foreach ($reportData['disciplinary_actions'] as $action) {
                fputcsv($file, $action);
            }
            fputcsv($file, []); // Add a blank line
        }

        // Payroll Records
        if (!empty($reportData['payroll_records'])) {
            fputcsv($file, ['Payroll Records']);
            fputcsv($file, array_keys($reportData['payroll_records'][0]));
            foreach ($reportData['payroll_records'] as $record) {
                fputcsv($file, $record);
            }
            fputcsv($file, []); // Add a blank line
        }

        // Deductions
        if (!empty($reportData['deductions'])) {
            fputcsv($file, ['Deductions']);
            fputcsv($file, array_keys($reportData['deductions'][0]));
            foreach ($reportData['deductions'] as $deduction) {
                fputcsv($file, $deduction);
            }
            fputcsv($file, []); // Add a blank line
        }

        // Additions
        if (!empty($reportData['additions'])) {
            fputcsv($file, ['Additions']);
            fputcsv($file, array_keys($reportData['additions'][0]));
            foreach ($reportData['additions'] as $addition) {
                fputcsv($file, $addition);
            }
            fputcsv($file, []); // Add a blank line
        }

        // Retirement Info
        if (!empty($reportData['retirement_info'])) {
            fputcsv($file, ['Retirement Information']);
            foreach ($reportData['retirement_info'] as $key => $value) {
                fputcsv($file, [$key, $value]);
            }
            fputcsv($file, []); // Add a blank line
        }

        rewind($file);
        $csvData = stream_get_contents($file);
        fclose($file);

        // Save CSV to storage
        Storage::put($filePath, $csvData);

        // Update report with file path
        $report->update(['file_path' => $filePath]);
    }

    public function show($id)
    {
        $report = Report::with(['generatedBy', 'employee'])->findOrFail($id);
    
        // Decode report_data JSON to array if it's a string
        if (is_string($report->report_data)) {
            $report->report_data = json_decode($report->report_data, true);
        }
    
        return view('reports.show', compact('report'));
    }
    

    public function download($id)
    {
        $report = Report::findOrFail($id);
        
        if ($report->file_path && Storage::exists($report->file_path)) {
            return Storage::download($report->file_path);
        }
        
        return redirect()->back()->with('error', 'Report file not found');
    }
}
