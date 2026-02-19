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

        fputcsv($file, [
            'Staff No', 'Full Name', 'Department', 'Grade Level',
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
            'Staff No', 'Employee Name', 'Department', 'Deduction Type',
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
            'Staff No', 'Employee Name', 'Department', 'Addition Type',
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
            'Staff No', 'Employee Name', 'Department', 'Previous Grade',
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

    public function download($id)
    {
        $report = Report::findOrFail($id);

        if ($report->file_path && Storage::exists($report->file_path)) {
            return Storage::download($report->file_path);
        }

        return redirect()->back()->with('error', 'Report file not found');
    }
}