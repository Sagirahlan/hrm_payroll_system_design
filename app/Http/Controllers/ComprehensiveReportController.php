<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Employee;
use App\Models\Department;
use App\Models\DeductionType;
use App\Models\AdditionType;
use App\Services\ComprehensiveReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Illuminate\Validation\ValidationException;

class ComprehensiveReportController extends Controller
{
    protected $reportService;

    public function __construct(ComprehensiveReportService $reportService)
    {
        $this->middleware(['auth', 'permission:manage_employees']);
        $this->reportService = $reportService;
    }

    public function index()
    {
        $reports = Report::select([
                'report_id', 
                'report_type', 
                'generated_by', 
                'generated_date', 
                'export_format', 
                'file_path', 
                'employee_id', 
                'created_at', 
                'updated_at'
            ])
            ->with(['generatedBy:user_id,username', 'employee:employee_id,first_name,surname'])
            ->orderBy('generated_date', 'desc') 
            ->paginate(20);

        // Process report types to show actual names instead of ID formats
        foreach ($reports as $report) {
            $report->display_type = $this->getReportTypeName($report->report_type);
        }

        $departments = Department::all();
        $deductionTypes = DeductionType::all();
        $additionTypes = AdditionType::all();

        return view('reports.new.index', compact('reports', 'departments', 'deductionTypes', 'additionTypes'));
    }

    public function create()
    {
        $departments = \App\Models\Department::all();
        $deductionTypes = \App\Models\DeductionType::all();
        $additionTypes = \App\Models\AdditionType::all();
        $employees = \App\Models\Employee::select('employee_id', 'first_name', 'surname')->get();
        $users = \App\Models\User::select('user_id', 'username')->get();

        return view('reports.new.create', [
            'departments_json' => json_encode($departments),
            'deduction_types_json' => json_encode($deductionTypes),
            'addition_types_json' => json_encode($additionTypes),
            'employees_json' => json_encode($employees),
            'users_json' => json_encode($users)
        ]);
    }

    public function generateReport(Request $request)
    {
        try {
            $request->validate([
                'report_type' => 'required|string',
                'export_format' => 'required|in:PDF,Excel',
                'filters' => 'array'
            ]);

            $filters = $request->filters ?? [];

            // Generate report data based on report type
            $reportData = $this->generateReportData($request->report_type, $filters);

            // Check if there was an error in report generation
            if (isset($reportData['error'])) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $reportData['error']
                    ], 422);
                }
                return redirect()->back()->with('error', $reportData['error']);
            }

            // Create report record
            $report = Report::create([
                'report_type' => $request->report_type,
                'generated_by' => Auth::id(),
                'generated_date' => now(),
                'report_data' => json_encode($reportData),
                'export_format' => $request->export_format,
                'description' => $this->getReportTypeName($request->report_type) . ' generated on ' . now()->format('Y-m-d H:i:s')
            ]);

            // Generate file based on format
            if ($request->export_format === 'PDF') {
                $this->generatePDF($report, $reportData, $request->report_type);
            } else {
                $this->generateExcel($report, $reportData, $request->report_type);
            }

            // Always return JSON for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Report generated successfully.',
                    'redirect' => route('reports.comprehensive.index')
                ]);
            }

            return redirect()->route('reports.comprehensive.index')->with('success', 'Report generated successfully.');
        } catch (ValidationException $e) {
            // Always return JSON for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error occurred.',
                    'errors' => $e->errors()
                ], 422);
            }
            return response()->json([
                'success' => false,
                'message' => 'Validation error occurred.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Log the full error for debugging
            \Log::error('Comprehensive Report Generation Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' line ' . $e->getLine());
            
            // Always return JSON for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while generating the report: ' . $e->getMessage()
                ], 500);
            }
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating the report: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateReportData($reportType, $filters)
    {
        switch ($reportType) {
            case 'employee_master':
                return $this->reportService->generateEmployeeMasterReport();
                
            case 'employee_directory':
                return $this->reportService->generateEmployeeDirectoryReport($filters);
                
            case 'employee_status':
                return $this->reportService->generateEmployeeStatusReport();
                
            case 'payroll_summary':
                return $this->reportService->generatePayrollSummaryReport(
                    $filters['year'] ?? null, 
                    $filters['month'] ?? null
                );
                
            case 'deduction_summary':
                return $this->reportService->generateDeductionSummaryReport($filters);
                
            case 'addition_summary':
                return $this->reportService->generateAdditionSummaryReport($filters);
                
            case 'promotion_history':
                return $this->reportService->generatePromotionHistoryReport($filters);
                
            case 'disciplinary':
                return $this->reportService->generateDisciplinaryReport($filters);
                
            case 'retirement_planning':
                return $this->reportService->generateRetirementPlanningReport();
                
            case 'loan_status':
                return $this->reportService->generateLoanStatusReport($filters);
                
            case 'department_summary':
                return $this->reportService->generateDepartmentSummaryReport();
                
            case 'grade_level_summary':
                return $this->reportService->generateGradeLevelSummaryReport();
                
            case 'audit_trail':
                return $this->reportService->generateAuditTrailReport($filters);
                
            case 'payroll_analysis':
                return $this->reportService->generatePayrollAnalysisReport(
                    $filters['year'] ?? null, 
                    $filters['month'] ?? null
                );
                
            default:
                return ['error' => 'Invalid report type'];
        }
    }

    private function generatePDF($report, $reportData, $reportType)
    {
        $viewPath = 'reports.new.pdf.' . str_replace('_', '-', $reportType) . '-report';
        
        // Check if the specific view exists, if not use a generic one
        if (!view()->exists($viewPath)) {
            $viewPath = 'reports.new.pdf.generic-report';
        }
        
        // Generate PDF using Snappy
        $pdf = PDF::loadView($viewPath, [
            'data' => $reportData,
            'report' => $report,
            'reportType' => $this->getReportTypeName($reportType)
        ]);
        
        $fileName = str_replace('_', '-', $reportType) . "_report_" . now()->format('Y_m_d_H_i_s') . '.pdf';
        $filePath = "reports/pdf/{$fileName}";

        // Save PDF to storage
        Storage::put($filePath, $pdf->output());

        // Update report with file path
        $report->update(['file_path' => $filePath]);
    }

    private function generateExcel($report, $reportData, $reportType)
    {
        $fileName = str_replace('_', '-', $reportType) . "_report_" . now()->format('Y_m_d_H_i_s') . '.csv';
        $filePath = "reports/excel/{$fileName}";

        $file = fopen('php://temp', 'w+');

        // Header
        fputcsv($file, [$this->getReportTypeName($reportType)]);
        fputcsv($file, ['Generated on: ' . now()->format('F j, Y')]);
        fputcsv($file, []);

        // Process data based on report type
        $this->writeExcelData($file, $reportData, $reportType);

        rewind($file);
        $csvData = stream_get_contents($file);
        fclose($file);

        // Save CSV to storage
        Storage::put($filePath, $csvData);

        // Update report with file path
        $report->update(['file_path' => $filePath]);
    }

    private function writeExcelData($file, $reportData, $reportType)
    {
        switch ($reportType) {
            case 'employee_master':
                $this->writeEmployeeMasterExcel($file, $reportData);
                break;
                
            case 'employee_directory':
                $this->writeEmployeeDirectoryExcel($file, $reportData);
                break;
                
            case 'employee_status':
                $this->writeEmployeeStatusExcel($file, $reportData);
                break;
                
            case 'payroll_summary':
                $this->writePayrollSummaryExcel($file, $reportData);
                break;
                
            case 'deduction_summary':
                $this->writeDeductionSummaryExcel($file, $reportData);
                break;
                
            case 'addition_summary':
                $this->writeAdditionSummaryExcel($file, $reportData);
                break;
                
            case 'promotion_history':
                $this->writePromotionHistoryExcel($file, $reportData);
                break;
                
            case 'disciplinary':
                $this->writeDisciplinaryExcel($file, $reportData);
                break;
                
            case 'retirement_planning':
                $this->writeRetirementPlanningExcel($file, $reportData);
                break;
                
            case 'loan_status':
                $this->writeLoanStatusExcel($file, $reportData);
                break;
                
            default:
                fputcsv($file, ['No data available for this report type']);
        }
    }

    private function writeEmployeeMasterExcel($file, $reportData)
    {
        fputcsv($file, [
            'Employee ID', 'Full Name', 'Department', 'Cadre', 'Grade Level', 'Step', 
            'Status', 'Date of Appointment', 'Years of Service', 'Basic Salary', 
            'Email', 'Mobile', 'Address', 'Disciplinary Count', 'Total Deductions', 
            'Total Additions', 'Loan Count', 'Promotion Count', 'Last Payroll Date'
        ]);

        foreach ($reportData['employees'] as $employee) {
            fputcsv($file, [
                $employee['employee_id'],
                $employee['full_name'],
                $employee['department'],
                $employee['cadre'],
                $employee['grade_level'],
                $employee['step'],
                $employee['status'],
                $employee['date_of_first_appointment'],
                $employee['years_of_service'],
                '₦' . number_format($employee['basic_salary'], 2),
                $employee['email'],
                $employee['mobile_no'],
                $employee['address'],
                $employee['disciplinary_count'],
                '₦' . number_format($employee['total_deductions'], 2),
                '₦' . number_format($employee['total_additions'], 2),
                $employee['loan_count'],
                $employee['promotion_count'],
                $employee['last_payroll_date']
            ]);
        }
    }

    private function writeEmployeeDirectoryExcel($file, $reportData)
    {
        fputcsv($file, [
            'Employee ID', 'Full Name', 'Department', 'Grade Level', 'Step', 
            'Status', 'Email', 'Mobile No', 'Extension'
        ]);

        foreach ($reportData['employees'] as $employee) {
            fputcsv($file, [
                $employee['employee_id'],
                $employee['full_name'],
                $employee['department'],
                $employee['grade_level'],
                $employee['step'],
                $employee['status'],
                $employee['email'],
                $employee['mobile_no'],
                $employee['extension']
            ]);
        }
    }

    private function writeEmployeeStatusExcel($file, $reportData)
    {
        fputcsv($file, ['Status', 'Count']);
        foreach ($reportData['status_summary'] as $status) {
            fputcsv($file, [$status['status'], $status['count']]);
        }
        
        fputcsv($file, []);
        fputcsv($file, [
            'Employee ID', 'Full Name', 'Department', 'Grade Level', 'Step', 
            'Status', 'Date of Appointment', 'Years of Service'
        ]);

        foreach ($reportData['employees_by_status'] as $status => $employees) {
            foreach ($employees as $employee) {
                fputcsv($file, [
                    $employee['employee_id'],
                    $employee['full_name'],
                    $employee['department'],
                    $employee['grade_level'],
                    $employee['step'],
                    $employee['status'],
                    $employee['date_of_first_appointment'],
                    $employee['years_of_service']
                ]);
            }
        }
    }

    private function writePayrollSummaryExcel($file, $reportData)
    {
        fputcsv($file, ['Period', $reportData['period']]);
        fputcsv($file, ['Total Records', $reportData['total_records']]);
        fputcsv($file, ['Total Basic Salary', '₦' . number_format($reportData['total_basic_salary'], 2)]);
        fputcsv($file, ['Total Deductions', '₦' . number_format($reportData['total_deductions'], 2)]);
        fputcsv($file, ['Total Additions', '₦' . number_format($reportData['total_additions'], 2)]);
        fputcsv($file, ['Total Net Salary', '₦' . number_format($reportData['total_net_salary'], 2)]);
        fputcsv($file, []);

        fputcsv($file, [
            'Employee ID', 'Full Name', 'Department', 'Grade Level', 
            'Basic Salary', 'Total Deductions', 'Total Additions', 
            'Net Salary', 'Payment Date', 'Status'
        ]);

        foreach ($reportData['payroll_records'] as $record) {
            fputcsv($file, [
                $record['employee_id'],
                $record['full_name'],
                $record['department'],
                $record['grade_level'],
                '₦' . number_format($record['basic_salary'], 2),
                '₦' . number_format($record['total_deductions'], 2),
                '₦' . number_format($record['total_additions'], 2),
                '₦' . number_format($record['net_salary'], 2),
                $record['payment_date'],
                $record['status']
            ]);
        }
    }

    private function writeDeductionSummaryExcel($file, $reportData)
    {
        fputcsv($file, ['Total Deductions', $reportData['total_deductions']]);
        fputcsv($file, ['Total Amount', '₦' . number_format($reportData['total_amount'], 2)]);
        fputcsv($file, []);

        fputcsv($file, [
            'Employee ID', 'Employee Name', 'Department', 'Deduction Type', 
            'Amount', 'Start Date', 'End Date', 'Frequency'
        ]);

        foreach ($reportData['deductions'] as $deduction) {
            fputcsv($file, [
                $deduction['employee_id'],
                $deduction['employee_name'],
                $deduction['department'],
                $deduction['deduction_type'],
                '₦' . number_format($deduction['amount'], 2),
                $deduction['start_date'],
                $deduction['end_date'],
                $deduction['frequency']
            ]);
        }
    }

    private function writeAdditionSummaryExcel($file, $reportData)
    {
        fputcsv($file, ['Total Additions', $reportData['total_additions']]);
        fputcsv($file, ['Total Amount', '₦' . number_format($reportData['total_amount'], 2)]);
        fputcsv($file, []);

        fputcsv($file, [
            'Employee ID', 'Employee Name', 'Department', 'Addition Type', 
            'Amount', 'Start Date', 'End Date', 'Frequency'
        ]);

        foreach ($reportData['additions'] as $addition) {
            fputcsv($file, [
                $addition['employee_id'],
                $addition['employee_name'],
                $addition['department'],
                $addition['addition_type'],
                '₦' . number_format($addition['amount'], 2),
                $addition['start_date'],
                $addition['end_date'],
                $addition['frequency']
            ]);
        }
    }

    private function writePromotionHistoryExcel($file, $reportData)
    {
        fputcsv($file, ['Total Promotions', $reportData['total_promotions']]);
        fputcsv($file, []);

        fputcsv($file, [
            'Employee ID', 'Employee Name', 'Department', 'Previous Grade', 
            'New Grade', 'Promotion Date', 'Promotion Type', 'Reason', 'Status'
        ]);

        foreach ($reportData['promotions'] as $promotion) {
            fputcsv($file, [
                $promotion['employee_id'],
                $promotion['employee_name'],
                $promotion['department'],
                $promotion['previous_grade'],
                $promotion['new_grade'],
                $promotion['promotion_date'],
                $promotion['promotion_type'],
                $promotion['reason'],
                $promotion['status']
            ]);
        }
    }

    private function writeDisciplinaryExcel($file, $reportData)
    {
        fputcsv($file, ['Total Actions', $reportData['total_actions']]);
        fputcsv($file, []);

        fputcsv($file, [
            'Employee ID', 'Employee Name', 'Department', 'Action Type', 
            'Action Date', 'Description', 'Status', 'Resolution'
        ]);

        foreach ($reportData['actions'] as $action) {
            fputcsv($file, [
                $action['employee_id'],
                $action['employee_name'],
                $action['department'],
                $action['action_type'],
                $action['action_date'],
                $action['description'],
                $action['status'],
                $action['resolution']
            ]);
        }
    }

    private function writeRetirementPlanningExcel($file, $reportData)
    {
        fputcsv($file, ['Total Approaching Retirement', $reportData['total_approaching_retirement']]);
        fputcsv($file, []);

        fputcsv($file, [
            'Employee ID', 'Full Name', 'Department', 'Grade Level', 
            'Date of Birth', 'Age', 'Date of Appointment', 'Years of Service', 
            'Expected Retirement Date', 'Months to Retirement'
        ]);

        foreach ($reportData['employees_approaching_retirement'] as $employee) {
            fputcsv($file, [
                $employee['employee_id'],
                $employee['full_name'],
                $employee['department'],
                $employee['grade_level'],
                $employee['date_of_birth'],
                $employee['age'],
                $employee['date_of_first_appointment'],
                $employee['years_of_service'],
                $employee['expected_retirement_date'],
                $employee['months_to_retirement']
            ]);
        }
    }

    private function writeLoanStatusExcel($file, $reportData)
    {
        fputcsv($file, ['Total Loans', $reportData['total_loans']]);
        fputcsv($file, ['Total Principal', '₦' . number_format($reportData['total_principal'], 2)]);
        fputcsv($file, ['Total Repaid', '₦' . number_format($reportData['total_repaid'], 2)]);
        fputcsv($file, ['Total Remaining', '₦' . number_format($reportData['total_remaining'], 2)]);
        fputcsv($file, []);

        fputcsv($file, [
            'Employee ID', 'Employee Name', 'Department', 'Loan Type', 
            'Principal Amount', 'Monthly Deduction', 'Total Months', 
            'Total Repaid', 'Remaining Balance', 'Status', 'Application Date'
        ]);

        foreach ($reportData['loans'] as $loan) {
            fputcsv($file, [
                $loan['employee_id'],
                $loan['employee_name'],
                $loan['department'],
                $loan['loan_type'],
                '₦' . number_format($loan['principal_amount'], 2),
                '₦' . number_format($loan['monthly_deduction'], 2),
                $loan['total_months'],
                '₦' . number_format($loan['total_repaid'], 2),
                '₦' . number_format($loan['remaining_balance'], 2),
                $loan['status'],
                $loan['application_date']
            ]);
        }
    }

    private function getReportTypeName($reportType)
    {
        $reportNames = [
            'employee_master' => 'Employee Master Report',
            'employee_directory' => 'Employee Directory Report',
            'employee_status' => 'Employee Status Report',
            'payroll_summary' => 'Payroll Summary Report',
            'deduction_summary' => 'Deduction Summary Report',
            'addition_summary' => 'Addition Summary Report',
            'promotion_history' => 'Promotion History Report',
            'disciplinary' => 'Disciplinary Action Report',
            'retirement_planning' => 'Retirement Planning Report',
            'loan_status' => 'Loan Status Report',
            'department_summary' => 'Department Summary Report',
            'grade_level_summary' => 'Grade Level Summary Report',
            'audit_trail' => 'Audit Trail Report',
            'payroll_analysis' => 'Payroll Analysis Report'
        ];

        return $reportNames[$reportType] ?? ucfirst(str_replace('_', ' ', $reportType)) . ' Report';
    }

    public function show($id)
    {
        $report = Report::with(['generatedBy:user_id,username', 'employee:employee_id,first_name,surname'])->findOrFail($id);

        // Decode report_data JSON to array if it's a string
        if (is_string($report->report_data)) {
            $report->report_data = json_decode($report->report_data, true);
        }

        return view('reports.new.show', compact('report'));
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