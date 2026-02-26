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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
            ->with(['generatedBy:user_id,username', 'employee:employee_id,first_name,surname,staff_no'])
            ->orderBy('generated_date', 'desc')
            ->paginate(20);

        // Process report types to show actual names instead of ID formats
        foreach ($reports as $report) {
            $report->display_type = $this->getReportTypeName($report->report_type);
        }

        $departments = Department::all();
        $deductionTypes = DeductionType::all();
        $additionTypes = AdditionType::all();
        // Get deduction types that are associated with loans
        $loanDeductionTypes = \App\Models\DeductionType::whereHas('loans')->get();

        // If no loan-associated deduction types found, return all deduction types
        if ($loanDeductionTypes->isEmpty()) {
            $loanDeductionTypes = \App\Models\DeductionType::all();
        }

        $appointmentTypes = \App\Models\AppointmentType::all();

        return view('reports.new.index', compact('reports', 'departments', 'deductionTypes', 'additionTypes', 'loanDeductionTypes', 'appointmentTypes'));
    }

    public function create()
    {
        $departments = \App\Models\Department::all();
        $deductionTypes = \App\Models\DeductionType::all();
        $additionTypes = \App\Models\AdditionType::all();
        $employees = \App\Models\Employee::select('employee_id', 'first_name', 'surname')->get();
        $users = \App\Models\User::select('user_id', 'username')->get();
        $appointmentTypes = \App\Models\AppointmentType::all();
        // Get deduction types that are associated with loans
        $loanDeductionTypes = \App\Models\DeductionType::whereHas('loans')->get();

        // If no loan-associated deduction types found, return all deduction types
        if ($loanDeductionTypes->isEmpty()) {
            $loanDeductionTypes = \App\Models\DeductionType::all();
        }

        return view('reports.new.create', [
            'departments_json' => json_encode($departments),
            'deduction_types_json' => json_encode($deductionTypes),
            'addition_types_json' => json_encode($additionTypes),
            'employees_json' => json_encode($employees),
            'users_json' => json_encode($users),
            'appointmentTypes' => $appointmentTypes,
            'appointmentTypes_json' => json_encode($appointmentTypes),
            'loanDeductionTypes_json' => json_encode($loanDeductionTypes)
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
                    'redirect' => route('reports.index')
                ]);
            }

            return redirect()->route('reports.index')->with('success', 'Report generated successfully.');
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
                return $this->reportService->generateEmployeeMasterReport(null, $filters);

            case 'employee_directory':
                return $this->reportService->generateEmployeeDirectoryReport($filters);

            case 'employee_status':
                return $this->reportService->generateEmployeeStatusReport();

            case 'payroll_summary':
                return $this->reportService->generatePayrollSummaryReport(
                    $filters['year'] ?? null,
                    $filters['month'] ?? null,
                    $filters
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
                return $this->reportService->generateRetirementPlanningReport($filters);

            case 'retirement_6months':
                return $this->reportService->generateRetirementPlanningReportWithin6Months();

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

            case 'retirement':
                return $this->reportService->generateRetirementReport($filters);
            case 'historical_retirement':
                return $this->reportService->generateHistoricalRetirementReport($filters);

            case 'payroll_journal':
                return $this->reportService->generatePayrollJournalReport($filters);

            case 'payroll_detailed':
                return $this->reportService->generatePayrollDetailedReport($filters);

            case 'full_payroll_report':
                return $this->reportService->generateFullPayrollReport($filters);

        case 'pensioner':
            return $this->reportService->generatePensionerReport($filters);

        case 'duplicate_beneficiary':
            return $this->reportService->generateDuplicateBeneficiaryReport();

        case 'full_payroll':
            return $this->reportService->generateFullPayrollReport($filters);

        case 'employee_export':
            return $this->reportService->generateEmployeeExportReport($filters);

        case 'pension_export':
            return $this->reportService->generatePensionExportReport($filters);

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

        if ($reportType === 'full_payroll' || $reportType === 'payroll_journal') {
            $pdf->setOption('orientation', 'Landscape');
            $pdf->setOption('page-size', 'A4');
        }

        $fileName = str_replace('_', '-', $reportType) . "_report_" . now()->format('Y_m_d_H_i_s') . '.pdf';
        $filePath = "reports/pdf/{$fileName}";

        // Save PDF to storage
        Storage::put($filePath, $pdf->output());

        // Update report with file path
        $report->update(['file_path' => $filePath]);
    }

    private function generateExcel($report, $reportData, $reportType)
    {
        // Use PhpSpreadsheet for multi-sheet exports
        if (in_array($reportType, ['employee_export', 'pension_export'])) {
            $this->generateXlsx($report, $reportData, $reportType);
            return;
        }

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

    private function generateXlsx($report, $reportData, $reportType)
    {
        $spreadsheet = new Spreadsheet();

        if ($reportType === 'employee_export') {
            $this->writeEmployeeExportXlsx($spreadsheet, $reportData);
        } elseif ($reportType === 'pension_export') {
            $this->writePensionExportXlsx($spreadsheet, $reportData);
        }

        $fileName = str_replace('_', '-', $reportType) . "_report_" . now()->format('Y_m_d_H_i_s') . '.xlsx';
        $filePath = "reports/excel/{$fileName}";

        // Save to temp file, then move to storage
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        Storage::put($filePath, file_get_contents($tempFile));
        unlink($tempFile);

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

            case 'payroll_journal':
            $this->writePayrollJournalExcel($file, $reportData);
            break;

        case 'pensioner':
            $this->writePensionerExcel($file, $reportData);
            break;

        case 'duplicate_beneficiary':
            $this->writeDuplicateBeneficiaryExcel($file, $reportData);
            break;

        case 'full_payroll':
            $this->writeFullPayrollExcel($file, $reportData);
            break;

        case 'full_payroll_report':
            $this->writeFullPayrollExcel($file, $reportData);
            break;

        case 'employee_export':
            $this->writeEmployeeExportExcel($file, $reportData);
            break;

        case 'pension_export':
            $this->writePensionExportExcel($file, $reportData);
            break;

        default:
            fputcsv($file, ['No data available for this report type']);
        }
    }

    private function writeEmployeeMasterExcel($file, $reportData)
    {
        fputcsv($file, [
            'Staff No', 'Full Name', 'Department', 'Cadre', 'Grade Level', 'Step',
            'Status', 'Appointment Type', 'Date of Appointment', 'Years of Service', 'Basic Salary',
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
                $employee['appointment_type'],
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
            'Staff No', 'Full Name', 'Department', 'Grade Level', 'Step',
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
            'Staff No', 'Full Name', 'Department', 'Grade Level', 'Step',
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

        // 1. Collect all unique addition types
        $allAdditionTypes = [];
        foreach ($reportData['payroll_records'] as $record) {
            if (!empty($record['addition_breakdown'])) {
                foreach (array_keys($record['addition_breakdown']) as $type) {
                    $allAdditionTypes[$type] = true;
                }
            }
        }
        $sortedAdditionTypes = array_keys($allAdditionTypes);
        sort($sortedAdditionTypes);

        // Build Header Row
        $header = [
            'Staff No', 'Full Name', 'Department', 'Grade Level',
            'Basic Salary'
        ];
        
        // Add Addition Headers
        foreach ($sortedAdditionTypes as $type) {
            $header[] = $type;
        }

        // Add remaining headers
        $header = array_merge($header, [
            'Total Additions', 'Total Deductions', 'Net Salary', 'Payment Date', 'Status'
        ]);

        fputcsv($file, $header);

        foreach ($reportData['payroll_records'] as $record) {
            $row = [
                $record['employee_id'],
                $record['full_name'],
                $record['department'],
                $record['grade_level'],
                '₦' . number_format($record['basic_salary'], 2),
            ];

            // Add Addition Values
            foreach ($sortedAdditionTypes as $type) {
                $amount = $record['addition_breakdown'][$type] ?? 0;
                $row[] = '₦' . number_format($amount, 2);
            }

            // Add remaining values
            $row[] = '₦' . number_format($record['total_additions'], 2);
            $row[] = '₦' . number_format($record['total_deductions'], 2);
            $row[] = '₦' . number_format($record['net_salary'], 2);
            $row[] = $record['payment_date'];
            $row[] = $record['status'];

            fputcsv($file, $row);
        }
    }

    private function writeDeductionSummaryExcel($file, $reportData)
    {
        fputcsv($file, ['Total Deductions', $reportData['total_deductions']]);
        fputcsv($file, ['Total Amount', '₦' . number_format($reportData['total_amount'], 2)]);
        fputcsv($file, []);

        // Dynamic Headers
        $header = [
            'Staff No', 'Employee Name', 'Department'
        ];

        foreach ($reportData['deduction_types'] as $type) {
            $header[] = $type;
        }

        $header[] = 'Total Deductions';

        fputcsv($file, $header);

        foreach ($reportData['employees'] as $employee) {
            $row = [
                $employee['employee_id'],
                $employee['employee_name'],
                $employee['department']
            ];

            foreach ($reportData['deduction_types'] as $type) {
                 $amount = $employee['deductions'][$type] ?? 0;
                 $row[] = '₦' . number_format($amount, 2);
            }

            $row[] = '₦' . number_format($employee['total_deductions'], 2);

            fputcsv($file, $row);
        }
    }

    private function writeAdditionSummaryExcel($file, $reportData)
    {
        fputcsv($file, ['Total Additions', $reportData['total_additions']]);
        fputcsv($file, ['Total Amount', '₦' . number_format($reportData['total_amount'], 2)]);
        fputcsv($file, []);

        // Dynamic Headers
        $header = [
            'Staff No', 'Employee Name', 'Department'
        ];

        foreach ($reportData['addition_types'] as $type) {
            $header[] = $type;
        }

        $header[] = 'Total Additions';

        fputcsv($file, $header);

        foreach ($reportData['employees'] as $employee) {
            $row = [
                $employee['employee_id'],
                $employee['employee_name'],
                $employee['department']
            ];

            foreach ($reportData['addition_types'] as $type) {
                 $amount = $employee['additions'][$type] ?? 0;
                 $row[] = '₦' . number_format($amount, 2);
            }

            $row[] = '₦' . number_format($employee['total_additions'], 2);

            fputcsv($file, $row);
        }
    }

    private function writePromotionHistoryExcel($file, $reportData)
    {
        fputcsv($file, ['Total Promotions', $reportData['total_promotions']]);
        fputcsv($file, []);

        fputcsv($file, [
            'Staff No', 'Employee Name', 'Department', 'Previous Grade', 'Previous Step',
            'New Grade', 'New Step', 'Promotion Date', 'Promotion Type', 'Reason', 'Status'
        ]);

        foreach ($reportData['promotions'] as $promotion) {
            fputcsv($file, [
                $promotion['employee_id'],
                $promotion['employee_name'],
                $promotion['department'],
                $promotion['previous_grade'],
                $promotion['previous_step'],
                $promotion['new_grade'],
                $promotion['new_step'],
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
            'Staff No', 'Employee Name', 'Department', 'Action Type',
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
            'Staff No', 'Name', 'Calculated Retirement Date', 'Expected Date of Retirement', 'Years of Service', 'Age', 'Retirement Reason', 'Status'
        ]);

        foreach ($reportData['employees_approaching_retirement'] as $employee) {
            fputcsv($file, [
                $employee['employee_id'],
                $employee['full_name'],
                $employee['calculated_retirement_date'] ?? $employee['expected_retirement_date'],
                $employee['expected_retirement_date'],
                $employee['years_of_service'],
                $employee['age'],
                $employee['retirement_reason'] ?? 'N/A',
                $employee['status']
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
            'Staff No', 'Employee Name', 'Department', 'Loan Type',
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

    private function writeAuditTrailExcel($file, $reportData)
    {
        fputcsv($file, ['Total Activities', $reportData['total_activities']]);
        fputcsv($file, []);

        fputcsv($file, [
            'User Name', 'Action', 'Description', 'Timestamp', 'Entity Type', 'Entity ID'
        ]);

        foreach ($reportData['activities'] as $activity) {
            fputcsv($file, [
                $activity['user_name'],
                $activity['action'],
                $activity['description'],
                $activity['timestamp'],
                $activity['entity_type'],
                $activity['entity_id']
            ]);
        }
    }

    private function writePensionerExcel($file, $reportData)
    {
        // Header row
        fputcsv($file, [
            'S/N', 'Full Name', 'Staff No', 'Department', 'Rank', 'Grade Level', 'Step',
            'Date of Retirement', 'Retirement Type', 'Years of Service',
            'Pension Amount', 'Gratuity Amount', 'Gratuity Paid',
            'Bank Name', 'Account Number', 'Account Name', 'Phone Number', 'Status'
        ]);

        $sn = 1;
        foreach ($reportData['pensioners'] ?? [] as $pensioner) {
            fputcsv($file, [
                $sn++,
                $pensioner['full_name'],
                $pensioner['staff_no'],
                $pensioner['department'],
                $pensioner['rank'],
                $pensioner['grade_level'],
                $pensioner['step'],
                $pensioner['date_of_retirement'],
                $pensioner['retirement_type'],
                $pensioner['years_of_service'],
                number_format($pensioner['pension_amount'], 2),
                number_format($pensioner['gratuity_amount'], 2),
                $pensioner['is_gratuity_paid'],
                $pensioner['bank_name'],
                $pensioner['account_number'],
                $pensioner['account_name'],
                $pensioner['phone_number'],
                $pensioner['status'],
            ]);
        }
    }

    private function writePayrollJournalExcel($file, $reportData)
    {
        fputcsv($file, ['Period', $reportData['period']]);
        fputcsv($file, ['Grand Total', '₦' . number_format($reportData['grand_total'], 2)]);
        fputcsv($file, []);

        fputcsv($file, [
            'Code', 'Description', 'Count', 'Amount', 'Type'
        ]);

        foreach ($reportData['journal_items'] as $item) {
            fputcsv($file, [
                $item['code'],
                $item['description'],
                $item['count'],
                '₦' . number_format($item['amount'], 2),
                $item['type']
            ]);
        }
    }

    private function writeDuplicateBeneficiaryExcel($file, $reportData)
    {
        // Section 1: Duplicate Accounts
        fputcsv($file, ['DUPLICATE BANK ACCOUNTS']);
        fputcsv($file, ['Total Groups', $reportData['total_duplicate_account_groups']]);
        fputcsv($file, []);
        
        fputcsv($file, [
            'Account Number', 'Bank', 'Beneficiary Name', 'Type', 'ID/Staff No', 'Department', 'Status'
        ]);

        foreach ($reportData['duplicate_accounts'] as $group) {
            foreach ($group as $beneficiary) {
                fputcsv($file, [
                    $beneficiary['account_number'],
                    $beneficiary['bank_name'],
                    $beneficiary['name'],
                    $beneficiary['type'],
                    $beneficiary['id'],
                    $beneficiary['department'],
                    $beneficiary['status']
                ]);
            }
            // Add a spacer row between groups
            fputcsv($file, []);
        }

        fputcsv($file, []);
        fputcsv($file, []);

        // Section 2: Duplicate NINs
        fputcsv($file, ['DUPLICATE NINs']);
        fputcsv($file, ['Total Groups', $reportData['total_duplicate_nin_groups']]);
        fputcsv($file, []);
        
        fputcsv($file, [
            'NIN', 'Beneficiary Name', 'Type', 'ID/Staff No', 'Department', 'Status'
        ]);

        foreach ($reportData['duplicate_nins'] as $group) {
            foreach ($group as $beneficiary) {
                fputcsv($file, [
                    $beneficiary['nin'],
                    $beneficiary['name'],
                    $beneficiary['type'],
                    $beneficiary['id'],
                    $beneficiary['department'],
                    $beneficiary['status']
                ]);
            }
            // Add a spacer row between groups
            fputcsv($file, []);
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
            'retirement_6months' => 'Retirement Planning Report (6 Months)',
            'loan_status' => 'Loan Status Report',
            'department_summary' => 'Department Summary Report',
            'grade_level_summary' => 'Grade Level Summary Report',
            'audit_trail' => 'Audit Trail Report',
            'payroll_analysis' => 'Payroll Analysis Report',
            'retirement' => 'Employees Approaching Retirement Report',
            'historical_retirement' => 'Historical Retirement Report',
            'payroll_journal' => 'Payroll Journal Report',
        'payroll_detailed' => 'Detailed Payroll Report (Bank Grouped)',
        'pensioner' => 'Pensioner Report with Bank Details',
        'duplicate_beneficiary' => 'Duplicate Beneficiary Report',
        'full_payroll' => 'Full Payroll Report',
        'full_payroll_report' => 'Full Payroll Report',
        'employee_export' => 'Employee Export Report',
        'pension_export' => 'Pension Export Report',
    ];

        return $reportNames[$reportType] ?? ucfirst(str_replace('_', ' ', $reportType)) . ' Report';
    }

    public function show($id)
    {
        $report = Report::with(['generatedBy:user_id,username', 'employee:employee_id,first_name,surname,staff_no'])->findOrFail($id);

        // Decode report_data JSON to array if it's a string
        if (is_string($report->report_data)) {
            $report->report_data = json_decode($report->report_data, true);
        }

        return view('reports.new.show', compact('report'));
    }

    private function writeFullPayrollExcel($file, $reportData)
    {
        $additionTypes = $reportData['addition_types'] ?? [];
        $deductionTypes = $reportData['deduction_types'] ?? [];

        // header
        $header = [
            'Staff No', 'Name', 'Department', 'Rank', 'Basic Salary'
        ];

        // Add headers for addition types
        foreach ($additionTypes as $type) {
            $header[] = $type;
        }

        $header[] = 'Total Additions';
        $header[] = 'Gross Salary';

        // Add headers for deduction types
        foreach ($deductionTypes as $type) {
            $header[] = $type;
        }

        $header[] = 'Total Deductions';
        $header[] = 'Net Salary';

        fputcsv($file, $header);

        foreach ($reportData['payroll_records'] as $record) {
            $row = [
                $record['staff_no'],
                $record['name'],
                $record['department'],
                $record['rank'],
                '₦' . number_format($record['basic_salary'], 2)
            ];

            // Add values for each addition type
            foreach ($additionTypes as $type) {
                // Determine layout of additions in record. 
                // Service returns: 'additions' => ['Type1' => amount, 'Type2' => amount]
                $amount = $record['additions'][$type] ?? 0;
                $row[] = '₦' . number_format($amount, 2);
            }

            $currentTotalAdditions = $record['total_additions'];
            // Recalculate gross if needed, but service provides it?
            // Service provides: 'gross_salary' => $record->basic_salary + $record->total_additions
            $currentGross = $record['gross_salary'];

            $row[] = '₦' . number_format($currentTotalAdditions, 2);
            $row[] = '₦' . number_format($currentGross, 2);

            // Add values for each deduction type
            foreach ($deductionTypes as $type) {
                $amount = $record['deductions'][$type] ?? 0;
                $row[] = '₦' . number_format($amount, 2);
            }

            $row[] = '₦' . number_format($record['total_deductions'], 2);
            $row[] = '₦' . number_format($record['net_salary'], 2);

            fputcsv($file, $row);
        }
    }

    private function writeEmployeeExportExcel($file, $reportData)
    {
        // Fallback CSV - not used when xlsx is generated
        fputcsv($file, ['Please generate this report in Excel format for multi-sheet support.']);
    }

    private function writePensionExportExcel($file, $reportData)
    {
        // Fallback CSV - not used when xlsx is generated
        fputcsv($file, ['Please generate this report in Excel format for multi-sheet support.']);
    }

    private function writeEmployeeExportXlsx($spreadsheet, $reportData)
    {
        // === Sheet 1: employees ===
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('employees');

        $headers = [
            'employee_id', 'staff_no', 'first_name', 'surname', 'middle_name',
            'gender', 'date_of_birth', 'state_id', 'lga_id', 'ward_id',
            'nationality', 'nin', 'mobile_no', 'email', 'address',
            'date_of_first_appointment', 'department_id', 'status',
            'appointment_type_id', 'photo_path', 'pay_point',
            'grade_level_id', 'step_id', 'rank_id', 'cadre_id',
            'expected_next_promotion', 'expected_retirement_date',
            'highest_certificate', 'amount', 'casual_Start_ Date ', 'casual_End_Date'
        ];

        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col++, 1, $header);
        }

        $row = 2;
        foreach ($reportData['employees'] as $emp) {
            $col = 1;
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['employee_id']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['staff_no']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['first_name']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['surname']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['middle_name']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['gender']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['date_of_birth']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['state_id']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['lga_id']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['ward_id']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['nationality']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['nin']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['mobile_no']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['email']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['address']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['date_of_first_appointment']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['department_id']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['status']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['appointment_type_id']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['photo_path']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['pay_point']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['grade_level_id']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['step_id']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['rank_id']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['cadre_id']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['expected_next_promotion']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['expected_retirement_date']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['highest_certificate']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['amount']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['casual_start_date']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $emp['casual_end_date']);
            $row++;
        }

        // === Sheet 2: next_of_kin ===
        $kinSheet = $spreadsheet->createSheet();
        $kinSheet->setTitle('next_of_kin');

        $kinHeaders = ['employee_id', 'name', 'relationship', 'mobile_no', 'address', 'occupation', 'place_of_work'];
        $col = 1;
        foreach ($kinHeaders as $header) {
            $kinSheet->setCellValueByColumnAndRow($col++, 1, $header);
        }

        $row = 2;
        foreach ($reportData['next_of_kin'] as $kin) {
            $col = 1;
            $kinSheet->setCellValueByColumnAndRow($col++, $row, $kin['employee_id']);
            $kinSheet->setCellValueByColumnAndRow($col++, $row, $kin['name']);
            $kinSheet->setCellValueByColumnAndRow($col++, $row, $kin['relationship']);
            $kinSheet->setCellValueByColumnAndRow($col++, $row, $kin['mobile_no']);
            $kinSheet->setCellValueByColumnAndRow($col++, $row, $kin['address']);
            $kinSheet->setCellValueByColumnAndRow($col++, $row, $kin['occupation']);
            $kinSheet->setCellValueByColumnAndRow($col++, $row, $kin['place_of_work']);
            $row++;
        }

        // === Sheet 3: banks ===
        $bankSheet = $spreadsheet->createSheet();
        $bankSheet->setTitle('banks');

        $bankHeaders = ['employee_id', 'bank_name', 'bank_code', 'account_name', 'account_no'];
        $col = 1;
        foreach ($bankHeaders as $header) {
            $bankSheet->setCellValueByColumnAndRow($col++, 1, $header);
        }

        $row = 2;
        foreach ($reportData['banks'] as $bank) {
            $col = 1;
            $bankSheet->setCellValueByColumnAndRow($col++, $row, $bank['employee_id']);
            $bankSheet->setCellValueByColumnAndRow($col++, $row, $bank['bank_name']);
            $bankSheet->setCellValueByColumnAndRow($col++, $row, $bank['bank_code']);
            $bankSheet->setCellValueByColumnAndRow($col++, $row, $bank['account_name']);
            $bankSheet->setCellValueByColumnAndRow($col++, $row, $bank['account_no']);
            $row++;
        }

        // Set active sheet back to first
        $spreadsheet->setActiveSheetIndex(0);
    }

    private function writePensionExportXlsx($spreadsheet, $reportData)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pensioners');

        $headers = [
            'employee_id', 'Staff Number', 'First Name', 'Middle Name', 'Surname',
            'Department', 'Retired Grade Level', 'New Pension',
            'Bank Name', 'Bank Code', 'Account Number', 'Account Name'
        ];

        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col++, 1, $header);
        }

        $row = 2;
        foreach ($reportData['pensioners'] ?? [] as $p) {
            $col = 1;
            $sheet->setCellValueByColumnAndRow($col++, $row, $p['employee_id']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $p['staff_number']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $p['first_name']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $p['middle_name']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $p['surname']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $p['department']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $p['retired_grade_level']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $p['new_pension']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $p['bank_name']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $p['bank_code']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $p['account_number']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $p['account_name']);
            $row++;
        }
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