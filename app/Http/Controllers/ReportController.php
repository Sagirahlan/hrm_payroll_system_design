<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Deduction;
use App\Models\DeductionType;
use App\Models\Addition;
use App\Models\AdditionType;
use App\Models\PayrollRecord;
use App\Services\EmployeeReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDF;
use App\Models\AuditTrail;

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
            'report_type' => 'required|in:comprehensive,basic,disciplinary,payroll,retirement,deduction,addition',
            'export_format' => 'required|in:PDF,Excel'
        ]);

        // Handle special deduction/addition reports
        if ($request->report_type === 'deduction' || $request->report_type === 'addition') {
            return $this->generateSpecialReport($request);
        }

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

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'generated_report',
            'description' => "Generated {$request->report_type} report for employee: {$employee->first_name} {$employee->surname}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Report', 'entity_id' => $report->id, 'report_type' => $request->report_type, 'employee_id' => $employee->employee_id]),
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
        // Check if this is an individual deduction or addition report
        if (strpos($request->report_type, 'deduction_') === 0 || strpos($request->report_type, 'addition_') === 0) {
            $request->validate([
                'report_type' => 'required|string',
                'export_format' => 'required|in:PDF,Excel',
            ]);
            
            // Handle individual deduction/addition reports
            return $this->generateIndividualTypeReport($request);
        }
        
        // For regular employee reports, we need employee_ids
        $request->validate([
            'employee_ids' => 'required|string',
            'report_type' => 'required|in:comprehensive,basic,disciplinary,payroll,retirement,deduction,addition',
            'export_format' => 'required|in:PDF,Excel',
        ]);

        // Only process if we have employee IDs
        if (empty($request->employee_ids)) {
            return redirect()->back()->with('error', 'Please select at least one employee.');
        }

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

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'bulk_generated_reports',
            'description' => "Bulk generated {$request->report_type} reports for " . count($employeeIds) . " employees.",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Report', 'entity_id' => null, 'report_type' => $request->report_type, 'employee_count' => count($employeeIds)]),
        ]);

        return redirect()->route('reports.index')->with('success', 'Bulk reports generated successfully.');
    }

    private function generateIndividualTypeReport(Request $request)
    {
        // Extract the type and ID from the report_type (e.g., "deduction_5" or "addition_3")
        $parts = explode('_', $request->report_type);
        $type = $parts[0]; // "deduction" or "addition"
        $typeId = $parts[1]; // The ID of the deduction or addition type
        
        if ($type === 'deduction') {
            return $this->generateIndividualDeductionReport($request, $typeId);
        } elseif ($type === 'addition') {
            return $this->generateIndividualAdditionReport($request, $typeId);
        }
    }

    private function generateIndividualDeductionReport(Request $request, $deductionTypeId)
    {
        // Get the deduction type
        $deductionType = DeductionType::find($deductionTypeId);
        
        if (!$deductionType) {
            return redirect()->back()->with('error', 'Invalid deduction type.');
        }
        
        // Get all deductions of this type with their employees
        $deductions = Deduction::with(['employee', 'deductionType'])
            ->where('deduction_type_id', $deductionType->id)
            ->get();
        
        $reportData = [
            'deduction_type' => $deductionType->name,
            'deductions' => $deductions->toArray()
        ];

        // Create report record for this deduction type
        $report = Report::create([
            'report_type' => 'deduction_' . $deductionType->id,
            'generated_by' => Auth::id(),
            'generated_date' => now(),
            'report_data' => json_encode($reportData),
            'export_format' => $request->export_format,
            'description' => 'Deduction Report: ' . $deductionType->name
        ]);

        // Generate file based on format
        if ($request->export_format === 'PDF') {
            $this->generateIndividualDeductionPDF($report, $reportData, $deductionType->name);
        } else {
            $this->generateIndividualDeductionExcel($report, $reportData, $deductionType->name);
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'generated_individual_deduction_report',
            'description' => "Generated individual deduction report for: {$deductionType->name}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Report', 'entity_id' => $report->id, 'report_type' => 'deduction', 'deduction_type_id' => $deductionType->id]),
        ]);

        return redirect()->route('reports.index')->with('success', 'Individual deduction report generated successfully.');
    }

    private function generateIndividualAdditionReport(Request $request, $additionTypeId)
    {
        // Get the addition type
        $additionType = AdditionType::find($additionTypeId);
        
        if (!$additionType) {
            return redirect()->back()->with('error', 'Invalid addition type.');
        }
        
        // Get all additions of this type with their employees
        $additions = Addition::with(['employee', 'additionType'])
            ->where('addition_type_id', $additionType->id)
            ->get();
        
        $reportData = [
            'addition_type' => $additionType->name,
            'additions' => $additions->toArray()
        ];

        // Create report record for this addition type
        $report = Report::create([
            'report_type' => 'addition_' . $additionType->id,
            'generated_by' => Auth::id(),
            'generated_date' => now(),
            'report_data' => json_encode($reportData),
            'export_format' => $request->export_format,
            'description' => 'Addition Report: ' . $additionType->name
        ]);

        // Generate file based on format
        if ($request->export_format === 'PDF') {
            $this->generateIndividualAdditionPDF($report, $reportData, $additionType->name);
        } else {
            $this->generateIndividualAdditionExcel($report, $reportData, $additionType->name);
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'generated_individual_addition_report',
            'description' => "Generated individual addition report for: {$additionType->name}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Report', 'entity_id' => $report->id, 'report_type' => 'addition', 'addition_type_id' => $additionType->id]),
        ]);

        return redirect()->route('reports.index')->with('success', 'Individual addition report generated successfully.');
    }

    private function generateIndividualDeductionPDF($report, $reportData, $deductionTypeName)
    {
        $pdf = PDF::loadView('reports.pdf.individual-deduction-report', [
            'data' => $reportData,
            'report' => $report,
            'deductionTypeName' => $deductionTypeName
        ]);

        $fileName = "deduction_report_" . str_replace(' ', '_', strtolower($deductionTypeName)) . "_" . now()->format('Y_m_d_H_i_s') . '.pdf';
        $filePath = "reports/pdf/{$fileName}";

        // Save PDF to storage
        Storage::put($filePath, $pdf->output());

        // Update report with file path
        $report->update(['file_path' => $filePath]);
    }

    private function generateIndividualAdditionPDF($report, $reportData, $additionTypeName)
    {
        $pdf = PDF::loadView('reports.pdf.individual-addition-report', [
            'data' => $reportData,
            'report' => $report,
            'additionTypeName' => $additionTypeName
        ]);

        $fileName = "addition_report_" . str_replace(' ', '_', strtolower($additionTypeName)) . "_" . now()->format('Y_m_d_H_i_s') . '.pdf';
        $filePath = "reports/pdf/{$fileName}";

        // Save PDF to storage
        Storage::put($filePath, $pdf->output());

        // Update report with file path
        $report->update(['file_path' => $filePath]);
    }

    private function generateIndividualDeductionExcel($report, $reportData, $deductionTypeName)
    {
        $fileName = "deduction_report_" . str_replace(' ', '_', strtolower($deductionTypeName)) . "_" . now()->format('Y_m_d_H_i_s') . '.csv';
        $filePath = "reports/excel/{$fileName}";

        $file = fopen('php://temp', 'w+');

        // Header
        fputcsv($file, ['Deduction Report: ' . $deductionTypeName]);
        fputcsv($file, ['Generated on: ' . now()->format('F j, Y')]);
        fputcsv($file, []);

        // Column headers
        fputcsv($file, ['Employee ID', 'Employee Name', 'Amount', 'Start Date', 'End Date']);

        foreach ($reportData['deductions'] as $deduction) {
            fputcsv($file, [
                $deduction['employee']['employee_id'] ?? '',
                ($deduction['employee']['first_name'] ?? '') . ' ' . ($deduction['employee']['surname'] ?? ''),
                '₦' . number_format($deduction['amount'], 2),
                $deduction['start_date'],
                $deduction['end_date'] ?? 'N/A'
            ]);
        }

        rewind($file);
        $csvData = stream_get_contents($file);
        fclose($file);

        // Save CSV to storage
        Storage::put($filePath, $csvData);

        // Update report with file path
        $report->update(['file_path' => $filePath]);
    }

    private function generateIndividualAdditionExcel($report, $reportData, $additionTypeName)
    {
        $fileName = "addition_report_" . str_replace(' ', '_', strtolower($additionTypeName)) . "_" . now()->format('Y_m_d_H_i_s') . '.csv';
        $filePath = "reports/excel/{$fileName}";

        $file = fopen('php://temp', 'w+');

        // Header
        fputcsv($file, ['Addition Report: ' . $additionTypeName]);
        fputcsv($file, ['Generated on: ' . now()->format('F j, Y')]);
        fputcsv($file, []);

        // Column headers
        fputcsv($file, ['Employee ID', 'Employee Name', 'Amount', 'Start Date', 'End Date']);

        foreach ($reportData['additions'] as $addition) {
            fputcsv($file, [
                $addition['employee']['employee_id'] ?? '',
                ($addition['employee']['first_name'] ?? '') . ' ' . ($addition['employee']['surname'] ?? ''),
                '₦' . number_format($addition['amount'], 2),
                $addition['start_date'],
                $addition['end_date'] ?? 'N/A'
            ]);
        }

        rewind($file);
        $csvData = stream_get_contents($file);
        fclose($file);

        // Save CSV to storage
        Storage::put($filePath, $csvData);

        // Update report with file path
        $report->update(['file_path' => $filePath]);
    }

    private function generateSpecialReport(Request $request)
    {
        // For deduction/addition reports, we'll generate a special report
        if ($request->report_type === 'deduction') {
            return $this->generateDeductionReport($request);
        } elseif ($request->report_type === 'addition') {
            return $this->generateAdditionReport($request);
        }
    }

    private function generateDeductionReport(Request $request)
    {
        // Get all deduction types
        $deductionTypes = DeductionType::all();
        
        // Generate a separate report for each deduction type
        foreach ($deductionTypes as $deductionType) {
            // Get all deductions of this type with their employees
            $deductions = Deduction::with(['employee', 'deductionType'])
                ->where('deduction_type_id', $deductionType->id)
                ->get();
            
            // Skip if no deductions of this type
            if ($deductions->isEmpty()) {
                continue;
            }
            
            $reportData = [
                'deduction_type' => $deductionType->name,
                'deductions' => $deductions->toArray()
            ];

            // Create report record for this deduction type
            $report = Report::create([
                'report_type' => 'deduction_' . $deductionType->id, // Unique report type for each deduction type
                'generated_by' => Auth::id(),
                'generated_date' => now(),
                'report_data' => json_encode($reportData),
                'export_format' => $request->export_format,
                'description' => 'Deduction Report: ' . $deductionType->name // Add description
            ]);

            // Generate file based on format
            if ($request->export_format === 'PDF') {
                $this->generateIndividualDeductionPDF($report, $reportData, $deductionType->name);
            } else {
                $this->generateIndividualDeductionExcel($report, $reportData, $deductionType->name);
            }
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'generated_deduction_reports',
            'description' => "Generated individual deduction reports for all deduction types",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Report', 'entity_id' => null, 'report_type' => 'deduction']),
        ]);

        return redirect()->route('reports.index')->with('success', 'Individual deduction reports generated successfully.');
    }

    private function generateAdditionReport(Request $request)
    {
        // Get all addition types
        $additionTypes = AdditionType::all();
        
        // Generate a separate report for each addition type
        foreach ($additionTypes as $additionType) {
            // Get all additions of this type with their employees
            $additions = Addition::with(['employee', 'additionType'])
                ->where('addition_type_id', $additionType->id)
                ->get();
            
            // Skip if no additions of this type
            if ($additions->isEmpty()) {
                continue;
            }
            
            $reportData = [
                'addition_type' => $additionType->name,
                'additions' => $additions->toArray()
            ];

            // Create report record for this addition type
            $report = Report::create([
                'report_type' => 'addition_' . $additionType->id, // Unique report type for each addition type
                'generated_by' => Auth::id(),
                'generated_date' => now(),
                'report_data' => json_encode($reportData),
                'export_format' => $request->export_format,
                'description' => 'Addition Report: ' . $additionType->name // Add description
            ]);

            // Generate file based on format
            if ($request->export_format === 'PDF') {
                $this->generateIndividualAdditionPDF($report, $reportData, $additionType->name);
            } else {
                $this->generateIndividualAdditionExcel($report, $reportData, $additionType->name);
            }
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'generated_addition_reports',
            'description' => "Generated individual addition reports for all addition types",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Report', 'entity_id' => null, 'report_type' => 'addition']),
        ]);

        return redirect()->route('reports.index')->with('success', 'Individual addition reports generated successfully.');
    }

    private function generateIndividualDeductionPDF($report, $reportData, $deductionTypeName)
    {
        $pdf = PDF::loadView('reports.pdf.deduction-report', [
            'data' => $reportData,
            'report' => $report,
            'deductionTypeName' => $deductionTypeName
        ]);

        $fileName = "deduction_report_" . str_replace(' ', '_', strtolower($deductionTypeName)) . "_" . now()->format('Y_m_d_H_i_s') . '.pdf';
        $filePath = "reports/pdf/{$fileName}";

        // Save PDF to storage
        Storage::put($filePath, $pdf->output());

        // Update report with file path
        $report->update(['file_path' => $filePath]);
    }

    private function generateIndividualAdditionPDF($report, $reportData, $additionTypeName)
    {
        $pdf = PDF::loadView('reports.pdf.addition-report', [
            'data' => $reportData,
            'report' => $report,
            'additionTypeName' => $additionTypeName
        ]);

        $fileName = "addition_report_" . str_replace(' ', '_', strtolower($additionTypeName)) . "_" . now()->format('Y_m_d_H_i_s') . '.pdf';
        $filePath = "reports/pdf/{$fileName}";

        // Save PDF to storage
        Storage::put($filePath, $pdf->output());

        // Update report with file path
        $report->update(['file_path' => $filePath]);
    }

    private function generateIndividualDeductionExcel($report, $reportData, $deductionTypeName)
    {
        $fileName = "deduction_report_" . str_replace(' ', '_', strtolower($deductionTypeName)) . "_" . now()->format('Y_m_d_H_i_s') . '.csv';
        $filePath = "reports/excel/{$fileName}";

        $file = fopen('php://temp', 'w+');

        // Header
        fputcsv($file, ['Deduction Report: ' . $deductionTypeName]);
        fputcsv($file, ['Generated on: ' . now()->format('F j, Y')]);
        fputcsv($file, []);

        // Column headers
        fputcsv($file, ['Employee ID', 'Employee Name', 'Amount', 'Start Date', 'End Date']);

        foreach ($reportData['deductions'] as $deduction) {
            fputcsv($file, [
                $deduction['employee']['employee_id'] ?? '',
                ($deduction['employee']['first_name'] ?? '') . ' ' . ($deduction['employee']['surname'] ?? ''),
                '₦' . number_format($deduction['amount'], 2),
                $deduction['start_date'],
                $deduction['end_date'] ?? 'N/A'
            ]);
        }

        rewind($file);
        $csvData = stream_get_contents($file);
        fclose($file);

        // Save CSV to storage
        Storage::put($filePath, $csvData);

        // Update report with file path
        $report->update(['file_path' => $filePath]);
    }

    private function generateIndividualAdditionExcel($report, $reportData, $additionTypeName)
    {
        $fileName = "addition_report_" . str_replace(' ', '_', strtolower($additionTypeName)) . "_" . now()->format('Y_m_d_H_i_s') . '.csv';
        $filePath = "reports/excel/{$fileName}";

        $file = fopen('php://temp', 'w+');

        // Header
        fputcsv($file, ['Addition Report: ' . $additionTypeName]);
        fputcsv($file, ['Generated on: ' . now()->format('F j, Y')]);
        fputcsv($file, []);

        // Column headers
        fputcsv($file, ['Employee ID', 'Employee Name', 'Amount', 'Start Date', 'End Date']);

        foreach ($reportData['additions'] as $addition) {
            fputcsv($file, [
                $addition['employee']['employee_id'] ?? '',
                ($addition['employee']['first_name'] ?? '') . ' ' . ($addition['employee']['surname'] ?? ''),
                '₦' . number_format($addition['amount'], 2),
                $addition['start_date'],
                $addition['end_date'] ?? 'N/A'
            ]);
        }

        rewind($file);
        $csvData = stream_get_contents($file);
        fclose($file);

        // Save CSV to storage
        Storage::put($filePath, $csvData);

        // Update report with file path
        $report->update(['file_path' => $filePath]);
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
            AuditTrail::create([
                'user_id' => Auth::id(),
                'action' => 'downloaded_report',
                'description' => "Downloaded report ID: {$id}",
                'action_timestamp' => now(),
                'log_data' => json_encode(['entity_type' => 'Report', 'entity_id' => $id]),
            ]);
            return Storage::download($report->file_path);
        }
        
        return redirect()->back()->with('error', 'Report file not found');
    }
}
