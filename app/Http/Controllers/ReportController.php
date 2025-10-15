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
use App\Models\Loan;
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
        // Use a more optimized query to avoid loading large report_data JSON field
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
            ->with(['generatedBy:user_id,username', 'employee:employee_id,first_name,surname']) // Only load necessary columns from relationships
            ->orderBy('generated_date', 'desc') 
            ->paginate(20);
        
        // Process report types to show actual names instead of ID formats
        foreach ($reports as $report) {
            if (strpos($report->report_type, 'deduction_') === 0) {
                $typeId = substr($report->report_type, 10); // Get the ID part after 'deduction_'
                $deductionType = \App\Models\DeductionType::find($typeId);
                if ($deductionType) {
                    $report->display_type = $deductionType->name;
                } else {
                    $report->display_type = 'Deduction Report';
                }
            } elseif (strpos($report->report_type, 'addition_') === 0) {
                $typeId = substr($report->report_type, 9); // Get the ID part after 'addition_'
                $additionType = \App\Models\AdditionType::find($typeId);
                if ($additionType) {
                    $report->display_type = $additionType->name;
                } else {
                    $report->display_type = 'Addition Report';
                }
            } else {
                $report->display_type = $report->report_type;
            }
        }

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
        \Log::info('generateEmployeeReport called with:', $request->all());
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'report_type' => 'required|string',
            'export_format' => 'required|in:PDF,Excel'
        ]);

        // Check if this is an individual deduction or addition report type (e.g., deduction_X or addition_X)
        if (strpos($request->report_type, 'deduction_') === 0 || strpos($request->report_type, 'addition_') === 0) {
            // Handle individual type report generation using bulk generation logic
            $request->validate([
                'report_type' => 'required|string',
                'export_format' => 'required|in:PDF,Excel',
            ]);
            
            // Create a new request object with the required fields to pass to bulk generation
            $bulkRequest = new Request();
            $bulkRequest->merge([
                'report_type' => $request->report_type,
                'export_format' => $request->export_format,
                'start_date' => $request->start_date ?? null,
                'end_date' => $request->end_date ?? null,
                'employee_id' => $request->employee_id
            ]);
            
            // Generate the individual type report
            return $this->generateIndividualTypeReport($bulkRequest);
        }

        // Handle special deduction/addition reports
        if ($request->report_type === 'deduction' || $request->report_type === 'addition') {
            return $this->generateSpecialReport($request);
        }

        $employee = Employee::with([
            'disciplinaryRecords',
            'payrollRecords',
            'deductions',
            'additions',
            'promotionHistory'
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
        // Check if this is a retired employees report
        if ($request->report_type === 'retired_employees') {
            return $this->generateRetiredEmployeesReport($request);
        }
        
        // Check if this is a retired employees summary report (tabular format)
        if ($request->report_type === 'retired_employees_summary') {
            return $this->generateRetiredEmployeesSummaryReport($request);
        }
        
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
        

        
        // Check if this deduction type is related to loans
        $isLoanRelated = Loan::where('deduction_type_id', $deductionTypeId)->exists();
        
        $deductions = collect();
        
        if ($isLoanRelated) {
            // For loan-related deduction reports, we should distinguish between:
            // 1. Report showing overall loan information (when no specific date range is provided)
            // 2. Report showing actual monthly deductions (when date range is provided)
            
            if (($request->has('start_date') && $request->start_date) || ($request->has('end_date') && $request->end_date)) {
                // When a date range is specified, show actual monthly loan deductions
                $loanIdsQuery = Loan::where('deduction_type_id', $deductionTypeId);
                if ($request->filled('employee_id')) {
                    $loanIdsQuery->where('employee_id', $request->employee_id);
                }
                $loanIds = $loanIdsQuery->pluck('loan_id');
                
                if ($loanIds->isNotEmpty()) {
                    // Get actual loan deductions (loan_deductions table) for these loans within the date range
                    $loanDeductionQuery = \App\Models\LoanDeduction::with(['loan', 'employee', 'loan.deductionType'])
                                          ->whereIn('loan_id', $loanIds);
                    
                    // Apply date range filter if provided
                    if ($request->has('start_date') && $request->start_date) {
                        $loanDeductionQuery = $loanDeductionQuery->where('deduction_date', '>=', $request->start_date);
                    }
                    if ($request->has('end_date') && $request->end_date) {
                        $loanDeductionQuery = $loanDeductionQuery->where('deduction_date', '<=', $request->end_date);
                    }
                    
                    $loanDeductions = $loanDeductionQuery->get();
                    
                    // Convert loan deductions to the same format as regular deductions for reporting
                    $deductions = $loanDeductions->map(function($loanDeduction) use ($deductionType) {
                        return [
                            'employee_id' => $loanDeduction->employee_id,
                            'employee' => $loanDeduction->employee,
                            'deduction_type_id' => $deductionType->id,
                            'deductionType' => $loanDeduction->loan->deductionType,
                            'amount' => $loanDeduction->amount_deducted,
                            'start_date' => $loanDeduction->deduction_date,
                            'end_date' => $loanDeduction->deduction_date, // Same date since it's a specific occurrence
                            'loan_id' => $loanDeduction->loan_id,
                            'loan' => $loanDeduction->loan,
                            'is_loan_deduction' => true, // Flag to identify this as a loan deduction
                            'deduction_date' => $loanDeduction->deduction_date
                        ];
                    });
                }
            } else {
                // When no date range is specified, show loan templates (overall loan information)
                $loanIdsQuery = Loan::where('deduction_type_id', $deductionTypeId);
                if ($request->filled('employee_id')) {
                    $loanIdsQuery->where('employee_id', $request->employee_id);
                }
                $loanIds = $loanIdsQuery->pluck('loan_id');
                
                if ($loanIds->isNotEmpty()) {
                    // Get the template deductions (main deductions table) for these loans
                    $deductions = Deduction::with(['employee', 'deductionType', 'loan'])
                                ->whereIn('loan_id', $loanIds)
                                ->get();
                } else {
                    // If no loans exist but it's a loan-related deduction type, just get regular deductions
                    $deductionsQuery = Deduction::with(['employee', 'deductionType'])
                                 ->where('deduction_type_id', $deductionTypeId);
                    if ($request->filled('employee_id')) {
                        $deductionsQuery->where('employee_id', $request->employee_id);
                    }
                    $deductions = $deductionsQuery->get();
                }
            }
        } else {
            // For non-loan deductions, get them directly by type
            $deductionsQuery = Deduction::with(['employee', 'deductionType'])
                         ->where('deduction_type_id', $deductionTypeId);
            if ($request->filled('employee_id')) {
                $deductionsQuery->where('employee_id', $request->employee_id);
            }
            $deductions = $deductionsQuery->get();
        }
        
        // Process deductions to include loan details when applicable
        $processedDeductions = $deductions->map(function($deduction) use ($isLoanRelated) {
            $deductionData = is_array($deduction) ? $deduction : $deduction->toArray();
            
            // Add loan details if this is a loan-related deduction and has a loan
            if ($isLoanRelated && (isset($deductionData['loan']) || isset($deduction['loan']))) {
                $loan = isset($deductionData['loan']) ? $deductionData['loan'] : $deduction['loan'];
                $deductionData['loan_details'] = [
                    'loan_type' => $loan['loan_type'] ?? $loan->loan_type ?? 'N/A',
                    'principal_amount' => $loan['principal_amount'] ?? $loan->principal_amount ?? 0,
                    'total_repaid' => $loan['total_repaid'] ?? $loan->total_repaid ?? 0,
                    'remaining_balance' => $loan['remaining_balance'] ?? $loan->remaining_balance ?? 0,
                    'monthly_deduction' => $loan['monthly_deduction'] ?? $loan->monthly_deduction ?? 0,
                    'total_months' => $loan['total_months'] ?? $loan->total_months ?? 0,
                    'remaining_months' => $loan['remaining_months'] ?? $loan->remaining_months ?? 0,
                    'status' => $loan['status'] ?? $loan->status ?? 'N/A'
                ];
                
                // Calculate totals from loan deductions if available
                if (isset($loan['loanDeductions']) || isset($loan->loanDeductions)) {
                    $loanDeductionsCollection = isset($loan['loanDeductions']) ? $loan['loanDeductions'] : $loan->loanDeductions;
                    $totalDeductions = $loanDeductionsCollection ? $loanDeductionsCollection->sum('amount_deducted') : 0;
                    $deductionData['loan_details']['total_deductions_from_records'] = $totalDeductions;
                }
            } else if ($isLoanRelated) {
                // For loan-related deduction types, even if this particular deduction doesn't have a loan,
                // we might want to check if the employee has a loan of this type
                $employeeId = $deductionData['employee_id'] ?? $deduction['employee_id'];
                $employeeLoan = Loan::where('employee_id', $employeeId)
                                  ->where('deduction_type_id', $deductionData['deduction_type_id'] ?? $deduction['deduction_type_id'])
                                  ->first();
                                  
                if ($employeeLoan) {
                    $deductionData['loan_details'] = [
                        'loan_type' => $employeeLoan->loan_type,
                        'principal_amount' => $employeeLoan->principal_amount,
                        'total_repaid' => $employeeLoan->total_repaid,
                        'remaining_balance' => $employeeLoan->remaining_balance,
                        'monthly_deduction' => $employeeLoan->monthly_deduction,
                        'total_months' => $employeeLoan->total_months,
                        'remaining_months' => $employeeLoan->remaining_months,
                        'status' => $employeeLoan->status
                    ];
                    
                    // Also include loan deduction details
                    $loanDeductionsCollection = $employeeLoan->loanDeductions()->get();
                    $totalPaid = $loanDeductionsCollection->sum('amount_deducted');
                    $remainingBalance = $employeeLoan->remaining_balance; // Use remaining balance from loan record
                    
                    $deductionData['loan_details']['total_paid'] = $totalPaid;
                    $deductionData['loan_details']['remaining_balance_calculated'] = $remainingBalance;
                }
            }
            
            return $deductionData;
        });
        
        $reportData = [
            'deduction_type' => $deductionType->name,
            'deductions' => $processedDeductions->toArray(),
            'is_loan_related' => $isLoanRelated,
            'start_date' => $request->start_date ?? null,
            'end_date' => $request->end_date ?? null
        ];

        // Create report record for this deduction type
        $report = Report::create([
            'report_type' => 'deduction_' . $deductionType->id,
            'generated_by' => Auth::id(),
            'generated_date' => now(),
            'report_data' => json_encode($reportData),
            'export_format' => $request->export_format,
            'description' => 'Deduction Report: ' . $deductionType->name . ($request->start_date ? ' (from ' . $request->start_date . ' to ' . $request->end_date . ')' : '')
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
        

        
        // Start building the query
        $query = Addition::with(['employee', 'additionType'])->where('addition_type_id', $additionTypeId);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        
        // Apply date range filter if provided
        if ($request->has('start_date') && $request->start_date) {
            $query = $query->where('start_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query = $query->where('end_date', '<=', $request->end_date);
        }
        
        // Get all additions of this type with their employees
        $additions = $query->get();
        
        $reportData = [
            'addition_type' => $additionType->name,
            'additions' => $additions->toArray(),
            'start_date' => $request->start_date ?? null,
            'end_date' => $request->end_date ?? null
        ];

        // Create report record for this addition type
        $report = Report::create([
            'report_type' => 'addition_' . $additionType->id,
            'generated_by' => Auth::id(),
            'generated_date' => now(),
            'report_data' => json_encode($reportData),
            'export_format' => $request->export_format,
            'description' => 'Addition Report: ' . $additionType->name . ($request->start_date ? ' (from ' . $request->start_date . ' to ' . $request->end_date . ')' : '')
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
        // Ensure reportData is an array if it's a JSON string
        $processedReportData = is_string($reportData) ? json_decode($reportData, true) : $reportData;
        
        $pdf = PDF::loadView('reports.pdf.deduction-report', [
            'data' => $processedReportData,
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
        // Ensure reportData is an array if it's a JSON string
        $processedReportData = is_string($reportData) ? json_decode($reportData, true) : $reportData;
        
        $pdf = PDF::loadView('reports.pdf.addition-report', [
            'data' => $processedReportData,
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
        // Ensure reportData is an array if it's a JSON string
        $processedReportData = is_string($reportData) ? json_decode($reportData, true) : $reportData;
        
        $fileName = "deduction_report_" . str_replace(' ', '_', strtolower($deductionTypeName)) . "_" . now()->format('Y_m_d_H_i_s') . '.csv';
        $filePath = "reports/excel/{$fileName}";

        $file = fopen('php://temp', 'w+');

        // Header
        fputcsv($file, ['Deduction Report: ' . $deductionTypeName]);
        fputcsv($file, ['Generated on: ' . now()->format('F j, Y')]);
        fputcsv($file, []);

        // Check if this is a loan-related deduction to include loan details
        $isLoanRelated = $processedReportData['is_loan_related'] ?? false;
        
        if ($isLoanRelated) {
            // Include loan details in column headers
            fputcsv($file, [
                'Employee ID', 
                'Employee Name', 
                'Amount', 
                'Start Date', 
                'End Date', 
                'Loan Type',
                'Principal Amount',
                'Total Repaid',
                'Remaining Balance',
                'Monthly Deduction',
                'Total Months',
                'Remaining Months',
                'Status'
            ]);

            foreach ($processedReportData['deductions'] ?? [] as $deduction) {
                $loanDetails = $deduction['loan_details'] ?? [];
                
                fputcsv($file, [
                    $deduction['employee']['employee_id'] ?? '',
                    ($deduction['employee']['first_name'] ?? '') . ' ' . ($deduction['employee']['surname'] ?? ''),
                    '₦' . number_format($deduction['amount'], 2),
                    $deduction['start_date'],
                    $deduction['end_date'] ?? 'N/A',
                    $loanDetails['loan_type'] ?? 'N/A',
                    '₦' . number_format($loanDetails['principal_amount'] ?? 0, 2),
                    '₦' . number_format($loanDetails['total_repaid'] ?? 0, 2),
                    '₦' . number_format($loanDetails['remaining_balance'] ?? 0, 2),
                    '₦' . number_format($loanDetails['monthly_deduction'] ?? 0, 2),
                    $loanDetails['total_months'] ?? 'N/A',
                    $loanDetails['remaining_months'] ?? 'N/A',
                    $loanDetails['status'] ?? 'N/A'
                ]);
            }
        } else {
            // Standard deduction report without loan details
            fputcsv($file, ['Employee ID', 'Employee Name', 'Amount', 'Start Date', 'End Date']);

            foreach ($processedReportData['deductions'] ?? [] as $deduction) {
                fputcsv($file, [
                    $deduction['employee']['employee_id'] ?? '',
                    ($deduction['employee']['first_name'] ?? '') . ' ' . ($deduction['employee']['surname'] ?? ''),
                    '₦' . number_format($deduction['amount'], 2),
                    $deduction['start_date'],
                    $deduction['end_date'] ?? 'N/A'
                ]);
            }
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
        // Ensure reportData is an array if it's a JSON string
        $processedReportData = is_string($reportData) ? json_decode($reportData, true) : $reportData;
        
        $fileName = "addition_report_" . str_replace(' ', '_', strtolower($additionTypeName)) . "_" . now()->format('Y_m_d_H_i_s') . '.csv';
        $filePath = "reports/excel/{$fileName}";

        $file = fopen('php://temp', 'w+');

        // Header
        fputcsv($file, ['Addition Report: ' . $additionTypeName]);
        fputcsv($file, ['Generated on: ' . now()->format('F j, Y')]);
        fputcsv($file, []);

        // Column headers
        fputcsv($file, ['Employee ID', 'Employee Name', 'Amount', 'Start Date', 'End Date']);

        foreach ($processedReportData['additions'] ?? [] as $addition) {
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
            // Check if this deduction type is related to loans
            $isLoanRelated = Loan::where('deduction_type_id', $deductionType->id)->exists();
            
            $deductions = collect();
            $loanDeductions = collect(); // Store loan deductions separately
            
            if ($isLoanRelated) {
                // Get loans associated with this deduction type
                $loanIds = Loan::where('deduction_type_id', $deductionType->id)
                              ->pluck('loan_id');
                
                if ($loanIds->isNotEmpty()) {
                    // Get the template deductions (main deductions table) for these loans
                    $templateDeductions = Deduction::with(['employee', 'deductionType', 'loan'])
                                        ->whereIn('loan_id', $loanIds)
                                        ->get();
                    
                    // Get actual loan deductions (loan_deductions table) for these loans
                    $loanDeductions = \App\Models\LoanDeduction::with(['loan', 'employee', 'loan.deductionType'])
                                          ->whereIn('loan_id', $loanIds)
                                          ->get();
                    
                    // Apply date range filter to loan deductions if provided
                    if ($request->has('start_date') && $request->start_date) {
                        $loanDeductions = $loanDeductions->filter(function ($loanDeduction) use ($request) {
                            return $loanDeduction->deduction_date >= $request->start_date;
                        });
                    }
                    if ($request->has('end_date') && $request->end_date) {
                        $loanDeductions = $loanDeductions->filter(function ($loanDeduction) use ($request) {
                            return $loanDeduction->deduction_date <= $request->end_date;
                        });
                    }
                    
                    // Convert loan deductions to the same format as regular deductions for reporting
                    $loanDeductions = $loanDeductions->map(function($loanDeduction) use ($deductionType) {
                        return [
                            'employee_id' => $loanDeduction->employee_id,
                            'employee' => $loanDeduction->employee,
                            'deduction_type_id' => $deductionType->id,
                            'deductionType' => $loanDeduction->loan->deductionType,
                            'amount' => $loanDeduction->amount_deducted,
                            'start_date' => $loanDeduction->deduction_date,
                            'end_date' => $loanDeduction->deduction_date, // Same date since it's a specific occurrence
                            'loan_id' => $loanDeduction->loan_id,
                            'loan' => $loanDeduction->loan,
                            'is_loan_deduction' => true, // Flag to identify this as a loan deduction
                            'deduction_date' => $loanDeduction->deduction_date
                        ];
                    });
                    
                    // Merge template deductions with actual loan deductions
                    $deductions = $templateDeductions->concat($loanDeductions);
                } else {
                    // If no loans exist but it's a loan-related deduction type, just get regular deductions
                    $deductions = Deduction::with(['employee', 'deductionType'])
                                 ->where('deduction_type_id', $deductionType->id)
                                 ->get();
                }
            } else {
                // For non-loan deductions, get them directly by type
                $deductions = Deduction::with(['employee', 'deductionType'])
                             ->where('deduction_type_id', $deductionType->id)
                             ->get();
            }
            
            // Skip if no deductions of this type
            if ($deductions->isEmpty()) {
                continue;
            }
            
            // Apply date range filter to regular deductions if provided
            if (!$isLoanRelated && $request->has('start_date') && $request->start_date) {
                $deductions = $deductions->filter(function ($deduction) use ($request) {
                    return $deduction->start_date >= $request->start_date;
                });
            }
            if (!$isLoanRelated && $request->has('end_date') && $request->end_date) {
                $deductions = $deductions->filter(function ($deduction) use ($request) {
                    return $deduction->end_date <= $request->end_date;
                });
            }
            
            // Process deductions to include loan details when applicable
            $processedDeductions = $deductions->map(function($deduction) use ($isLoanRelated) {
                $deductionData = is_array($deduction) ? $deduction : $deduction->toArray();
                
                // Add loan details if this is a loan-related deduction and has a loan
                if ($isLoanRelated && (isset($deductionData['loan']) || isset($deduction['loan']))) {
                    $loan = isset($deductionData['loan']) ? $deductionData['loan'] : $deduction['loan'];
                    $deductionData['loan_details'] = [
                        'loan_type' => $loan['loan_type'] ?? $loan->loan_type ?? 'N/A',
                        'principal_amount' => $loan['principal_amount'] ?? $loan->principal_amount ?? 0,
                        'total_repaid' => $loan['total_repaid'] ?? $loan->total_repaid ?? 0,
                        'remaining_balance' => $loan['remaining_balance'] ?? $loan->remaining_balance ?? 0,
                        'monthly_deduction' => $loan['monthly_deduction'] ?? $loan->monthly_deduction ?? 0,
                        'total_months' => $loan['total_months'] ?? $loan->total_months ?? 0,
                        'remaining_months' => $loan['remaining_months'] ?? $loan->remaining_months ?? 0,
                        'status' => $loan['status'] ?? $loan->status ?? 'N/A'
                    ];
                    
                    // Calculate totals from loan deductions if available
                    if (isset($loan['loanDeductions']) || isset($loan->loanDeductions)) {
                        $loanDeductionsCollection = isset($loan['loanDeductions']) ? $loan['loanDeductions'] : $loan->loanDeductions;
                        $totalDeductions = $loanDeductionsCollection ? $loanDeductionsCollection->sum('amount_deducted') : 0;
                        $deductionData['loan_details']['total_deductions_from_records'] = $totalDeductions;
                    }
                }
                
                return $deductionData;
            });
            
            $reportData = [
                'deduction_type' => $deductionType->name,
                'deductions' => $processedDeductions->toArray(),
                'is_loan_related' => $isLoanRelated
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

    
    private function generateRetiredEmployeesReport(Request $request)
    {
        // Get all retired employees with their details
        $retiredEmployees = Employee::with([
            'department',
            'cadre',
            'gradeLevel',
            'step',
            'bank',
            'deductions.deductionType',
            'additions.additionType',
            'disciplinaryRecords',
            'payrollRecords'
        ])
        ->where('status', 'Retired')
        ->orderBy('first_name')
        ->get();

        // Prepare report data
        $reportData = [
            'report_title' => 'Retired Employees Report',
            'generated_date' => now()->format('F j, Y'),
            'total_retired_employees' => $retiredEmployees->count(),
            'employees' => []
        ];

        // Process each retired employee
        foreach ($retiredEmployees as $employee) {
            // Handle date parsing for date_of_first_appointment
            $dateOfFirstAppointment = null;
            $yearsOfService = 'N/A';
            if ($employee->date_of_first_appointment) {
                $dateOfFirstAppointment = \Carbon\Carbon::parse($employee->date_of_first_appointment);
                $yearsOfService = $dateOfFirstAppointment ? $dateOfFirstAppointment->diffInYears(now()) : 'N/A';
            }
            
            // Handle date parsing for date_of_retirement
            $dateOfRetirement = null;
            if ($employee->date_of_retirement) {
                $dateOfRetirement = \Carbon\Carbon::parse($employee->date_of_retirement);
            }

            $employeeData = [
                'employee_id' => $employee->employee_id,
                'full_name' => $employee->first_name . ' ' . ($employee->middle_name ?? '') . ' ' . $employee->surname,
                'department' => $employee->department->department_name ?? 'N/A',
                'cadre' => $employee->cadre->cadre_name ?? 'N/A',
                'grade_level' => ($employee->gradeLevel->grade_level ?? 'N/A') . ' Step ' . ($employee->step->step_level ?? 'N/A'),
                'date_of_first_appointment' => $dateOfFirstAppointment ? $dateOfFirstAppointment->format('Y-m-d') : 'N/A',
                'date_of_retirement' => $dateOfRetirement ? $dateOfRetirement->format('Y-m-d') : 'N/A',
                'years_of_service' => $yearsOfService,
                'bank_details' => $employee->bank ? $employee->bank->bank_name . ' (' . $employee->account_number . ')' : 'N/A',
                'basic_salary' => $employee->isContractEmployee() ? 
                    ('₦' . number_format($employee->amount ?? 0, 2)) : 
                    ('₦' . number_format($employee->basic_salary ?? 0, 2)),
                'deductions' => [],
                'additions' => [],
                'disciplinary_records' => $employee->disciplinaryRecords ? $employee->disciplinaryRecords->count() : 0,
                'last_payroll_date' => $employee->payrollRecords && $employee->payrollRecords->last() ? 
                    ($employee->payrollRecords->last()->payroll_month ?? 'N/A') . ' ' . ($employee->payrollRecords->last()->payroll_year ?? 'N/A') : 'N/A'
            ];

            // Add deductions
            if ($employee->deductions) {
                foreach ($employee->deductions as $deduction) {
                    // Handle date parsing for deduction dates
                    $deductionStartDate = null;
                    $deductionEndDate = null;
                    if ($deduction->start_date) {
                        $deductionStartDate = \Carbon\Carbon::parse($deduction->start_date);
                    }
                    if ($deduction->end_date) {
                        $deductionEndDate = \Carbon\Carbon::parse($deduction->end_date);
                    }
                    
                    $employeeData['deductions'][] = [
                        'type' => $deduction->deductionType ? ($deduction->deductionType->name ?? 'N/A') : 'N/A',
                        'amount' => '₦' . number_format($deduction->amount ?? 0, 2),
                        'start_date' => $deductionStartDate ? $deductionStartDate->format('Y-m-d') : 'N/A',
                        'end_date' => $deductionEndDate ? $deductionEndDate->format('Y-m-d') : 'N/A'
                    ];
                }
            }

            // Add additions
            if ($employee->additions) {
                foreach ($employee->additions as $addition) {
                    // Handle date parsing for addition dates
                    $additionStartDate = null;
                    $additionEndDate = null;
                    if ($addition->start_date) {
                        $additionStartDate = \Carbon\Carbon::parse($addition->start_date);
                    }
                    if ($addition->end_date) {
                        $additionEndDate = \Carbon\Carbon::parse($addition->end_date);
                    }
                    
                    $employeeData['additions'][] = [
                        'type' => $addition->additionType ? ($addition->additionType->name ?? 'N/A') : 'N/A',
                        'amount' => '₦' . number_format($addition->amount ?? 0, 2),
                        'start_date' => $additionStartDate ? $additionStartDate->format('Y-m-d') : 'N/A',
                        'end_date' => $additionEndDate ? $additionEndDate->format('Y-m-d') : 'N/A'
                    ];
                }
            }

            $reportData['employees'][] = $employeeData;
        }

        // Create report record
        $report = Report::create([
            'report_type' => 'retired_employees',
            'generated_by' => Auth::id(),
            'generated_date' => now(),
            'report_data' => json_encode($reportData),
            'export_format' => $request->export_format,
            'description' => 'Retired Employees Report (' . $retiredEmployees->count() . ' employees)'
        ]);

        // Generate file based on format
        if ($request->export_format === 'PDF') {
            $this->generateRetiredEmployeesPDF($report, $reportData);
        } else {
            $this->generateRetiredEmployeesExcel($report, $reportData);
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'generated_retired_employees_report',
            'description' => "Generated retired employees report for " . $retiredEmployees->count() . " employees",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Report', 'entity_id' => $report->id, 'report_type' => 'retired_employees', 'employee_count' => $retiredEmployees->count()]),
        ]);

        return redirect()->route('reports.index')->with('success', 'Retired employees report generated successfully.');
    }

    // Generate pensioners report
    private function generatePensionersReport(Request $request)
    {
        // Get all pensioners with their details
        $pensioners = \App\Models\Pensioner::with([
            'employee.department',
            'employee.cadre',
            'employee.gradeLevel',
            'employee.step',
            'employee.bank',
            'retirement'
        ])
        ->orderBy('created_at', 'desc')
        ->get();

        // Prepare report data
        $reportData = [
            'report_title' => 'Pensioners Report',
            'generated_date' => now()->format('F j, Y'),
            'total_pensioners' => $pensioners->count(),
            'pensioners' => []
        ];

        // Process each pensioner
        foreach ($pensioners as $pensioner) {
            $employee = $pensioner->employee;
            
            // Handle date parsing for pension start date
            $pensionStartDate = null;
            if ($pensioner->pension_start_date) {
                $pensionStartDate = \Carbon\Carbon::parse($pensioner->pension_start_date);
            }
            
            // Handle date parsing for retirement date
            $retirementDate = null;
            if ($pensioner->retirement && $pensioner->retirement->retirement_date) {
                $retirementDate = \Carbon\Carbon::parse($pensioner->retirement->retirement_date);
            }

            $pensionerData = [
                'employee_id' => $employee->employee_id,
                'full_name' => $employee->first_name . ' ' . ($employee->middle_name ?? '') . ' ' . $employee->surname,
                'department' => $employee->department->department_name ?? 'N/A',
                'cadre' => $employee->cadre->cadre_name ?? 'N/A',
                'grade_level' => ($employee->gradeLevel->grade_level ?? 'N/A') . ' Step ' . ($employee->step->step_level ?? 'N/A'),
                'retirement_date' => $retirementDate ? $retirementDate->format('Y-m-d') : 'N/A',
                'pension_start_date' => $pensionStartDate ? $pensionStartDate->format('Y-m-d') : 'N/A',
                'pension_type' => $pensioner->pension_type ?? 'N/A',
                'pension_amount' => '₦' . number_format($pensioner->pension_amount ?? 0, 2),
                'rsa_balance_at_retirement' => '₦' . number_format($pensioner->rsa_balance_at_retirement ?? 0, 2),
                'lump_sum_amount' => '₦' . number_format($pensioner->lump_sum_amount ?? 0, 2),
                'expected_lifespan_months' => $pensioner->expected_lifespan_months ?? 'N/A',
                'status' => $pensioner->status ?? 'N/A',
                'bank_details' => $employee->bank ? $employee->bank->bank_name . ' (' . $employee->bank->account_no . ')' : 'N/A'
            ];

            $reportData['pensioners'][] = $pensionerData;
        }

        // Create report record
        $report = Report::create([
            'report_type' => 'pensioners',
            'generated_by' => Auth::id(),
            'generated_date' => now(),
            'report_data' => json_encode($reportData),
            'export_format' => $request->export_format,
            'description' => 'Pensioners Report (' . $pensioners->count() . ' pensioners)'
        ]);

        // Generate file based on format
        if ($request->export_format === 'PDF') {
            $this->generatePensionersPDF($report, $reportData);
        } else {
            $this->generatePensionersExcel($report, $reportData);
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'generated_pensioners_report',
            'description' => "Generated pensioners report for " . $pensioners->count() . " pensioners",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Report', 'entity_id' => $report->id, 'report_type' => 'pensioners', 'pensioner_count' => $pensioners->count()]),
        ]);

        return redirect()->route('reports.index')->with('success', 'Pensioners report generated successfully.');
    }

    private function generateRetiredEmployeesPDF($report, $reportData)
    {
        // Ensure reportData is an array if it's a JSON string
        $processedReportData = is_string($reportData) ? json_decode($reportData, true) : $reportData;
        
        $pdf = PDF::loadView('reports.pdf.retired-employees-report', [
            'data' => $processedReportData,
            'report' => $report
        ]);

        $fileName = "retired_employees_report_" . now()->format('Y_m_d_H_i_s') . '.pdf';
        $filePath = "reports/pdf/{$fileName}";

        // Save PDF to storage
        Storage::put($filePath, $pdf->output());

        // Update report with file path
        $report->update(['file_path' => $filePath]);
    }

    private function generateRetiredEmployeesSummaryReport(Request $request)
    {
        // Get all retired employees with their details - using the same query as the retirements.retired view
        $retiredEmployees = Employee::with(['gradeLevel', 'department', 'retirement', 'rank', 'step'])
            ->where('status', 'Retired')
            ->whereHas('retirement') // Only include employees who have retirement records
            ->orderBy('surname', 'asc')
            ->get();

        // Prepare report data
        $reportData = [
            'report_title' => 'Retired Employees Summary Report',
            'generated_date' => now()->format('F j, Y'),
            'total_retired_employees' => $retiredEmployees->count(),
            'employees' => []
        ];

        // Process each retired employee to match the specific format
        foreach ($retiredEmployees as $employee) {
            $employeeData = [
                'employee_id' => $employee->reg_no ?: $employee->employee_id, // Use reg_no as Staff ID if available
                'name' => trim($employee->first_name . ' ' . $employee->surname), // Name field
                'date_of_birth' => $employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->format('Y-m-d') : 'N/A', // Date of Birth
                'age' => $employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->age : 'N/A', // Age
                'years_of_service' => $employee->date_of_first_appointment ? 
                    round(\Carbon\Carbon::parse($employee->date_of_first_appointment)->diffInYears(
                        \Carbon\Carbon::parse($employee->retirement->retirement_date ?? now())
                    ), 1) : 'N/A', // Years of Service
                'rank' => $employee->rank ? $employee->rank->name : 'N/A', // Rank
                'grade_level_step' => $employee->gradeLevel ? 
                    $employee->gradeLevel->name . '-Step ' . ($employee->step ? $employee->step->name : 'N/A') : 'N/A', // Grade Level/Step
                'department' => $employee->department ? $employee->department->department_name : 'N/A', // Department
                'retirement_date' => $employee->retirement && $employee->retirement->retirement_date ? 
                    \Carbon\Carbon::parse($employee->retirement->retirement_date)->format('Y-m-d') : 'N/A', // Retirement Date
                'retire_reason' => $employee->retirement && $employee->retirement->retire_reason ? 
                    $employee->retirement->retire_reason : 'N/A', // Retire Reason
            ];

            $reportData['employees'][] = $employeeData;
        }

        // Create report record
        $report = Report::create([
            'report_type' => 'retired_employees_summary',
            'generated_by' => Auth::id(),
            'generated_date' => now(),
            'report_data' => json_encode($reportData),
            'export_format' => $request->export_format,
            'description' => 'Retired Employees Summary Report (' . $retiredEmployees->count() . ' employees)'
        ]);

        // Generate file based on format
        if ($request->export_format === 'PDF') {
            $this->generateRetiredEmployeesSummaryPDF($report, $reportData);
        } else {
            $this->generateRetiredEmployeesSummaryExcel($report, $reportData);
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'generated_retired_employees_summary_report',
            'description' => "Generated retired employees summary report for " . $retiredEmployees->count() . " employees",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Report', 'entity_id' => $report->id, 'report_type' => 'retired_employees_summary', 'employee_count' => $retiredEmployees->count()]),
        ]);

        return redirect()->route('reports.index')->with('success', 'Retired employees summary report generated successfully.');
    }

    private function generateRetiredEmployeesExcel($report, $reportData)
    {
        // Ensure reportData is an array if it's a JSON string
        $processedReportData = is_string($reportData) ? json_decode($reportData, true) : $reportData;
        
        // Ensure processedReportData is an array
        if (!is_array($processedReportData)) {
            $processedReportData = [];
        }
        
        $fileName = "retired_employees_report_" . now()->format('Y_m_d_H_i_s') . '.csv';
        $filePath = "reports/excel/{$fileName}";

        $file = fopen('php://temp', 'w+');

        // Header
        fputcsv($file, ['Retired Employees Report']);
        fputcsv($file, ['Generated on: ' . now()->format('F j, Y')]);
        fputcsv($file, ['Total Retired Employees: ' . ($processedReportData['total_retired_employees'] ?? 0)]);
        fputcsv($file, []);

        // Column headers
        fputcsv($file, [
            'Employee ID', 
            'Full Name', 
            'Department', 
            'Cadre', 
            'Grade Level', 
            'Date of First Appointment', 
            'Date of Retirement', 
            'Years of Service', 
            'Bank Details', 
            'Basic Salary',
            'Disciplinary Records',
            'Last Payroll Date'
        ]);

        // Check if employees exist and is an array
        $employees = $processedReportData['employees'] ?? [];
        if (is_array($employees)) {
            foreach ($employees as $employee) {
                // Ensure employee is an array
                if (!is_array($employee)) {
                    $employee = [];
                }
                
                fputcsv($file, [
                    $employee['employee_id'] ?? '',
                    $employee['full_name'] ?? '',
                    $employee['department'] ?? '',
                    $employee['cadre'] ?? '',
                    $employee['grade_level'] ?? '',
                    $employee['date_of_first_appointment'] ?? '',
                    $employee['date_of_retirement'] ?? '',
                    $employee['years_of_service'] ?? '',
                    $employee['bank_details'] ?? '',
                    $employee['basic_salary'] ?? '',
                    $employee['disciplinary_records'] ?? '',
                    $employee['last_payroll_date'] ?? ''
                ]);
                
                // Add deductions section if any
                if (!empty($employee['deductions']) && is_array($employee['deductions'])) {
                    fputcsv($file, ['', '', '', 'Deductions:', '', '', '', '', '', '', '', '']);
                    fputcsv($file, ['', '', '', 'Type', 'Amount', 'Start Date', 'End Date', '', '', '', '', '']);
                    foreach ($employee['deductions'] as $deduction) {
                        // Ensure deduction is an array
                        if (!is_array($deduction)) {
                            $deduction = [];
                        }
                        
                        fputcsv($file, [
                            '', '', '', 
                            $deduction['type'] ?? '', 
                            $deduction['amount'] ?? '', 
                            $deduction['start_date'] ?? '', 
                            $deduction['end_date'] ?? '', 
                            '', '', '', '', ''
                        ]);
                    }
                }
                
                // Add additions section if any
                if (!empty($employee['additions']) && is_array($employee['additions'])) {
                    fputcsv($file, ['', '', '', 'Additions:', '', '', '', '', '', '', '', '']);
                    fputcsv($file, ['', '', '', 'Type', 'Amount', 'Start Date', 'End Date', '', '', '', '', '']);
                    foreach ($employee['additions'] as $addition) {
                        // Ensure addition is an array
                        if (!is_array($addition)) {
                            $addition = [];
                        }
                        
                        fputcsv($file, [
                            '', '', '', 
                            $addition['type'] ?? '', 
                            $addition['amount'] ?? '', 
                            $addition['start_date'] ?? '', 
                            $addition['end_date'] ?? '', 
                            '', '', '', '', ''
                        ]);
                    }
                }
                
                // Add blank line between employees
                fputcsv($file, []);
            }
        }

        rewind($file);
        $csvData = stream_get_contents($file);
        fclose($file);

        // Save CSV to storage
        Storage::put($filePath, $csvData);

        // Update report with file path
        $report->update(['file_path' => $filePath]);
    }

    private function generateRetiredEmployeesSummaryExcel($report, $reportData)
    {
        // Ensure reportData is an array if it's a JSON string
        $processedReportData = is_string($reportData) ? json_decode($reportData, true) : $reportData;
        
        // Ensure processedReportData is an array
        if (!is_array($processedReportData)) {
            $processedReportData = [];
        }
        
        $fileName = "retired_employees_summary_report_" . now()->format('Y_m_d_H_i_s') . '.csv';
        $filePath = "reports/excel/{$fileName}";

        $file = fopen('php://temp', 'w+');

        // Header
        fputcsv($file, ['Retired Employees Summary Report']);
        fputcsv($file, ['Generated on: ' . now()->format('F j, Y')]);
        fputcsv($file, ['Total Retired Employees: ' . ($processedReportData['total_retired_employees'] ?? 0)]);
        fputcsv($file, []);

        // Column headers matching the format you requested
        fputcsv($file, [
            'Staff ID',
            'Name', 
            'Date of Birth',
            'Age', 
            'Years of Service',
            'Rank',
            'Grade Level/Step', 
            'Department',
            'Retirement Date', 
            'Retire Reason',
            'Actions' // This column will be empty in the Excel file
        ]);

        // Check if employees exist and is an array
        $employees = $processedReportData['employees'] ?? [];
        if (is_array($employees)) {
            foreach ($employees as $employee) {
                // Ensure employee is an array
                if (!is_array($employee)) {
                    $employee = [];
                }
                
                fputcsv($file, [
                    $employee['employee_id'] ?? '',
                    $employee['name'] ?? '',
                    $employee['date_of_birth'] ?? '',
                    $employee['age'] ?? '',
                    $employee['years_of_service'] ?? '',
                    $employee['rank'] ?? '',
                    $employee['grade_level_step'] ?? '',
                    $employee['department'] ?? '',
                    $employee['retirement_date'] ?? '',
                    $employee['retire_reason'] ?? '',
                    '' // Actions column (empty for Excel report)
                ]);
            }
        }

        rewind($file);
        $csvData = stream_get_contents($file);
        fclose($file);

        // Save CSV to storage
        Storage::put($filePath, $csvData);

        // Update report with file path
        $report->update(['file_path' => $filePath]);
    }

    private function generateRetiredEmployeesSummaryPDF($report, $reportData)
    {
        // Ensure reportData is an array if it's a JSON string
        $processedReportData = is_string($reportData) ? json_decode($reportData, true) : $reportData;
        
        $pdf = PDF::loadView('reports.pdf.retired-employees-summary-report', [
            'data' => $processedReportData,
            'report' => $report
        ]);

        $fileName = "retired_employees_summary_report_" . now()->format('Y_m_d_H_i_s') . '.pdf';
        $filePath = "reports/pdf/{$fileName}";

        // Save PDF to storage
        Storage::put($filePath, $pdf->output());

        // Update report with file path
        $report->update(['file_path' => $filePath]);
    }

    private function generatePDF($report, $employee, $reportData)
    {
        // Ensure reportData is properly formatted
        if (is_string($reportData)) {
            $reportData = json_decode($reportData, true);
        }
        
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
        $report = Report::with(['generatedBy:user_id,username', 'employee:employee_id,first_name,surname'])->findOrFail($id);
    
        // Decode report_data JSON to array if it's a string
        if (is_string($report->report_data)) {
            $report->report_data = json_decode($report->report_data, true);
        } elseif (is_string($report->report_data ?? null)) {
            $report->report_data = json_decode($report->report_data, true);
        }
    
        return view('reports.show', compact('report'));
    }
    
    private function generatePensionersPDF($report, $reportData)
    {
        // Ensure reportData is an array if it's a JSON string
        $processedReportData = is_string($reportData) ? json_decode($reportData, true) : $reportData;
        
        $pdf = PDF::loadView('reports.pdf.pensioners-report', [
            'data' => $processedReportData,
            'report' => $report
        ]);

        $fileName = "pensioners_report_" . now()->format('Y_m_d_H_i_s') . '.pdf';
        $filePath = "reports/pdf/{$fileName}";

        // Save PDF to storage
        Storage::put($filePath, $pdf->output());

        // Update report with file path
        $report->update(['file_path' => $filePath]);
    }

    private function generatePensionersExcel($report, $reportData)
    {
        // Ensure reportData is an array if it's a JSON string
        $processedReportData = is_string($reportData) ? json_decode($reportData, true) : $reportData;
        
        // Ensure processedReportData is an array
        if (!is_array($processedReportData)) {
            $processedReportData = [];
        }
        
        $fileName = "pensioners_report_" . now()->format('Y_m_d_H_i_s') . '.csv';
        $filePath = "reports/excel/{$fileName}";

        $file = fopen('php://temp', 'w+');

        // Header
        fputcsv($file, ['Pensioners Report']);
        fputcsv($file, ['Generated on: ' . now()->format('F j, Y')]);
        fputcsv($file, ['Total Pensioners: ' . ($processedReportData['total_pensioners'] ?? 0)]);
        fputcsv($file, []);

        // Column headers
        fputcsv($file, [
            'Employee ID', 
            'Full Name', 
            'Department', 
            'Cadre', 
            'Grade Level', 
            'Retirement Date', 
            'Pension Start Date', 
            'Pension Type',
            'Pension Amount', 
            'RSA Balance at Retirement', 
            'Lump Sum Amount',
            'Expected Lifespan (Months)',
            'Status',
            'Bank Details'
        ]);

        // Check if pensioners exist and is an array
        $pensioners = $processedReportData['pensioners'] ?? [];
        if (is_array($pensioners)) {
            foreach ($pensioners as $pensioner) {
                // Ensure pensioner is an array
                if (!is_array($pensioner)) {
                    $pensioner = [];
                }
                
                fputcsv($file, [
                    $pensioner['employee_id'] ?? '',
                    $pensioner['full_name'] ?? '',
                    $pensioner['department'] ?? '',
                    $pensioner['cadre'] ?? '',
                    $pensioner['grade_level'] ?? '',
                    $pensioner['retirement_date'] ?? '',
                    $pensioner['pension_start_date'] ?? '',
                    $pensioner['pension_type'] ?? '',
                    $pensioner['pension_amount'] ?? '',
                    $pensioner['rsa_balance_at_retirement'] ?? '',
                    $pensioner['lump_sum_amount'] ?? '',
                    $pensioner['expected_lifespan_months'] ?? '',
                    $pensioner['status'] ?? '',
                    $pensioner['bank_details'] ?? ''
                ]);
            }
        }

        rewind($file);
        $csvData = stream_get_contents($file);
        fclose($file);

        // Save CSV to storage
        Storage::put($filePath, $csvData);

        // Update report with file path
        $report->update(['file_path' => $filePath]);
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
