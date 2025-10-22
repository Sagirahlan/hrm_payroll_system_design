<?php

namespace App\Http\Controllers;

use App\Models\PayrollRecord;
use App\Models\Employee;
use App\Models\PaymentTransaction;
use App\Models\Deduction;
use App\Models\Addition;
use App\Models\Pensioner;
use App\Services\PayrollCalculationService;
use App\Services\PensionPayrollService;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PayrollRecordsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\GradeLevel;
use App\Models\DeductionType;
use App\Models\AdditionType;
use App\Models\Department;
use App\Models\AuditTrail;

class PayrollController extends Controller
{
    protected $payrollCalculationService;

    public function __construct(PayrollCalculationService $payrollCalculationService)
    {
        $this->middleware(['auth']);
        $this->middleware(['permission:view_payroll'], ['only' => ['index', 'show', 'getStatistics', 'search', 'getDetailedPayroll', 'generatePaySlip', 'exportPayroll', 'showDeductions', 'showAdditions', 'manageAllAdjustments']]);
        $this->middleware(['permission:create_payroll'], ['only' => ['generatePayroll', 'storeDeduction', 'storeAddition', 'storeBulkDeductions', 'storeBulkAdditions', 'bulkDeductions', 'additions', 'deductions', 'manageAllAdjustments', 'importEmployees']]);
        $this->middleware(['permission:edit_payroll'], ['only' => ['edit', 'update', 'recalculate', 'approve', 'reject', 'bulkUpdateStatus', 'bulkSendForReview', 'bulkMarkAsReviewed', 'bulkSendForApproval', 'bulkFinalApprove', 'sendForReview', 'markAsReviewed', 'sendForApproval', 'finalApprove']]);
        $this->middleware(['permission:delete_payroll'], ['only' => ['destroy']]);
        $this->middleware(['permission:generate_payroll'], ['only' => ['generatePayroll', 'recalculate']]);
        $this->middleware(['permission:approve_payroll'], ['only' => ['approve', 'reject', 'bulkSendForReview', 'bulkMarkAsReviewed', 'bulkSendForApproval', 'bulkFinalApprove', 'sendForReview', 'markAsReviewed', 'sendForApproval', 'finalApprove']]);
        $this->middleware(['permission:view_payslips'], ['only' => ['generatePaySlip']]);
        $this->middleware(['permission:manage_payroll_adjustments'], ['only' => ['showDeductions', 'showAdditions', 'storeDeduction', 'storeAddition', 'manageAllAdjustments', 'storeBulkDeductions', 'storeBulkAdditions', 'additions', 'deductions', 'bulkDeductions']]);
        $this->middleware(['permission:bulk_send_payroll_for_review'], ['only' => ['bulkSendForReview', 'sendForReview']]);
        $this->middleware(['permission:bulk_mark_payroll_as_reviewed'], ['only' => ['bulkMarkAsReviewed', 'markAsReviewed']]);
        $this->middleware(['permission:bulk_send_payroll_for_approval'], ['only' => ['bulkSendForApproval', 'sendForApproval']]);
        $this->middleware(['permission:bulk_final_approve_payroll'], ['only' => ['bulkFinalApprove', 'finalApprove']]);
        $this->middleware(['permission:bulk_update_payroll_status'], ['only' => ['bulkUpdateStatus']]);
        $this->payrollCalculationService = $payrollCalculationService;
    }

    // Generate payroll for a specific month
    public function generatePayroll(Request $request)
    {
        set_time_limit(0); // Set no time limit for this script
        $month = $request->input('month', now()->format('Y-m'));
        $appointmentTypeId = $request->input('appointment_type_id');

        // Fetch employees (both Active and Suspended)
        $employeesQuery = Employee::whereIn('status', ['Active', 'Suspended'])
            ->with(['gradeLevel', 'step']);      // Load related grade level and step

        if ($appointmentTypeId) {
            $employeesQuery->where('appointment_type_id', $appointmentTypeId);
            
            // For contract employees (appointment_type_id = 2), don't require grade_level_id
            // since contract employees may not have grade levels assigned
            if ($appointmentTypeId == 2) { // Contract employees
                // No need to filter by grade_level_id for contract employees
            } else {
                // For permanent/temporary employees, require grade level
                $employeesQuery->whereNotNull('grade_level_id');
            }
        } else {
            // If no specific appointment type selected, only include non-contract employees with grade levels
            // and contract employees with amounts
            $employeesQuery->where(function($query) {
                $query->where(function($q) {
                    $q->whereNotNull('grade_level_id') // Non-contract employees with grade levels
                      ->where('appointment_type_id', '!=', 2); // Not contract employees
                })->orWhere(function($q) {
                    $q->where('appointment_type_id', 2) // Contract employees
                      ->whereNotNull('amount'); // With contract amount
                });
            });
        }

        $employees = $employeesQuery->get();

        foreach ($employees as $employee) {
            // Check if the employee is a contract employee
            $isContractEmployee = $employee->isContractEmployee();
            
            // For contract employees, we don't require a grade level
            // For non-contract employees, check if they have a grade level
            if (!$isContractEmployee && (!$employee->gradeLevel || !$employee->gradeLevel->id)) {
                continue;
            }

            // For suspended employees, calculate payroll with special logic
            if ($employee->status === 'Suspended') {
                // Use the same employee model but indicate that it's suspended for calculation
                $calculation = $this->payrollCalculationService->calculatePayroll($employee, $month, true);

                // Create the payroll record for suspended employee with 'Pending Review' status
                $payroll = PayrollRecord::create([
                    'employee_id' => $employee->employee_id,
                    'grade_level_id' => $isContractEmployee ? null : $employee->gradeLevel->id,
                    'payroll_month' => $month . '-01',
                    'basic_salary' => $calculation['basic_salary'], // Will be half for suspended employees
                    'total_additions' => $calculation['total_additions'], // Additions still apply
                    'total_deductions' => $calculation['total_deductions'], // Deductions still apply
                    'net_salary' => $calculation['net_salary'], // Net salary with special calculation for suspended
                    'status' => 'Pending Review',
                    'payment_date' => null,
                    'remarks' => 'Generated for ' . $month . ' (Suspended - Special Calculation Applied)',
                ]);
                
                // ✅ Create a PaymentTransaction for this payroll record
                \App\Models\PaymentTransaction::create([
                    'payroll_id' =>  $payroll->payroll_id,
                    'employee_id' => $employee->employee_id,
                    'amount' => $calculation['net_salary'],
                    'payment_date' => null,
                    'bank_code' => $employee->bank->bank_code ?? null, // safe fallback
                    'account_name' => $employee->bank->account_name ?? ($employee->first_name . ' ' . $employee->surname),
                    'account_number' => $employee->bank->account_no ?? '0000000000',
                ]);
            } else {
                // For active employees, use normal calculation
                $calculation = $this->payrollCalculationService->calculatePayroll($employee, $month, false);

                // Create the payroll record for active employee with 'Pending Review' status
                $payroll = PayrollRecord::create([
                    'employee_id' => $employee->employee_id,
                    'grade_level_id' => $isContractEmployee ? null : $employee->gradeLevel->id,
                    'payroll_month' => $month . '-01',
                    'basic_salary' => $calculation['basic_salary'],
                    'total_additions' => $calculation['total_additions'],
                    'total_deductions' => $calculation['total_deductions'],
                    'net_salary' => $calculation['net_salary'],
                    'status' => 'Pending Review',
                    'payment_date' => null,
                    'remarks' => 'Generated for ' . $month,
                ]);
                
                // ✅ Create a PaymentTransaction for this payroll record
                \App\Models\PaymentTransaction::create([
                    'payroll_id' =>  $payroll->payroll_id,
                    'employee_id' => $employee->employee_id,
                    'amount' => $calculation['net_salary'],
                    'payment_date' => null,
                    'bank_code' => $employee->bank->bank_code ?? null, // safe fallback
                    'account_name' => $employee->bank->account_name ?? ($employee->first_name . ' ' . $employee->surname),
                    'account_number' => $employee->bank->account_no ?? '0000000000',
                ]);
            }
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'generated_payroll',
            'description' => "Generated payroll for month: {$month} for " . count($employees) . " employees (Active and Suspended).",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Payroll', 'entity_id' => null, 'month' => $month, 'employee_count' => count($employees)]),
        ]);

        return redirect()->route('payroll.index')
            ->with('success', 'Payroll generated successfully for ' . $month . ' (Includes both Active and Suspended employees)');
    }

    // Display payroll records with search and filter functionality
    public function index(Request $request)
    {
        $query = PayrollRecord::with(['employee.gradeLevel.steps', 'employee.step', 'gradeLevel', 'transaction']);

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('payroll_id', 'like', "%{$searchTerm}%")
                  ->orWhere('remarks', 'like', "%{$searchTerm}%")
                  ->orWhereHas('employee', function($employeeQuery) use ($searchTerm) {
                      $employeeQuery->where('first_name', 'like', "%{$searchTerm}%")
                                   ->orWhere('surname', 'like', "%{$searchTerm}%")
                                   ->orWhere('employee_id', 'like', "%{$searchTerm}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Month filter
        if ($request->filled('month_filter')) {
            $monthFilter = $request->month_filter . '-01';
            $query->whereYear('payroll_month', Carbon::parse($monthFilter)->year)
                  ->whereMonth('payroll_month', Carbon::parse($monthFilter)->month);
        }

        // Salary range filter
        if ($request->filled('salary_range')) {
            $salaryRange = $request->salary_range;
            switch ($salaryRange) {
                case '0-50000':
                    $query->whereBetween('net_salary', [0, 50000]);
                    break;
                case '50001-100000':
                    $query->whereBetween('net_salary', [50001, 100000]);
                    break;
                case '100001-200000':
                    $query->whereBetween('net_salary', [100001, 200000]);
                    break;
                case '200001-500000':
                    $query->whereBetween('net_salary', [200001, 500000]);
                    break;
                case '500001+':
                    $query->where('net_salary', '>', 500000);
                    break;
            }
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        switch ($sortBy) {
            case 'employee_name':
                $query->join('employees', 'payroll_records.employee_id', '=', 'employees.employee_id')
                      ->orderBy('employees.first_name', $sortDirection)
                      ->orderBy('employees.surname', $sortDirection)
                      ->select('payroll_records.*');
                break;
            case 'net_salary':
            case 'basic_salary':
            case 'payroll_month':
            case 'payroll_id':
                $query->orderBy($sortBy, $sortDirection);
                break;
            default:
                $query->orderBy('created_at', $sortDirection);
                break;
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $payrolls = $query->paginate($perPage);
        $appointmentTypes = \App\Models\AppointmentType::all();

        return view('payroll.index', compact('payrolls', 'appointmentTypes'));
    }

    // Generate pay slip
    public function generatePaySlip($payrollId)
    {
        $payroll = PayrollRecord::with(['employee', 'gradeLevel'])
            ->findOrFail($payrollId);
        
        // Get deductions for the payroll month
        $deductions = Deduction::where('employee_id', $payroll->employee_id)
            ->where(function($query) use ($payroll) {
                $payrollMonth = Carbon::parse($payroll->payroll_month);
                $query->where('start_date', '<=', $payrollMonth)
                      ->where(function($q) use ($payrollMonth) {
                          $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', $payrollMonth);
                      });
            })
            ->with(['deductionType', 'employee.gradeLevel.steps'])
            ->get();
        
        // Get additions for the payroll month
        $additions = Addition::where('employee_id', $payroll->employee_id)
            ->where(function($query) use ($payroll) {
                $payrollMonth = Carbon::parse($payroll->payroll_month);
                $query->where('start_date', '<=', $payrollMonth)
                      ->where(function($q) use ($payrollMonth) {
                          $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', $payrollMonth);
                      });
            })
            ->with(['additionType', 'employee.gradeLevel.steps'])
            ->get();

        $pdf = Pdf::loadView('payroll.payslip', compact('payroll', 'deductions', 'additions'));

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'generated_payslip',
            'description' => "Generated payslip for employee: {$payroll->employee->first_name} {$payroll->employee->surname} for payroll ID: {$payroll->payroll_id}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => $payroll->payroll_id, 'employee_id' => $payroll->employee->employee_id]),
        ]);

        return $pdf->download('payslip_' . $payroll->employee->first_name . '_' . $payroll->employee->surname . '_' . $payroll->payroll_id . '.pdf');
    }

    // Export payroll records to Excel with filters
    public function exportPayroll(Request $request)
    {
        // Apply the same filters as in index method
        $query = PayrollRecord::with(['employee', 'gradeLevel', 'transaction']);

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('payroll_id', 'like', "%{$searchTerm}%")
                  ->orWhere('remarks', 'like', "%{$searchTerm}%")
                  ->orWhereHas('employee', function($employeeQuery) use ($searchTerm) {
                      $employeeQuery->where('first_name', 'like', "%{$searchTerm}%")
                                   ->orWhere('surname', 'like', "%{$searchTerm}%")
                                   ->orWhere('employee_id', 'like', "%{$searchTerm}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Month filter
        if ($request->filled('month_filter')) {
            $monthFilter = $request->month_filter . '-01';
            $query->whereYear('payroll_month', Carbon::parse($monthFilter)->year)
                  ->whereMonth('payroll_month', Carbon::parse($monthFilter)->month);
        }

        // Salary range filter
        if ($request->filled('salary_range')) {
            $salaryRange = $request->salary_range;
            switch ($salaryRange) {
                case '0-50000':
                    $query->whereBetween('net_salary', [0, 50000]);
                    break;
                case '50001-100000':
                    $query->whereBetween('net_salary', [50001, 100000]);
                    break;
                case '100001-200000':
                    $query->whereBetween('net_salary', [100001, 200000]);
                    break;
                case '200001-500000':
                    $query->whereBetween('net_salary', [200001, 500000]);
                    break;
                case '500001+':
                    $query->where('net_salary', '>', 500000);
                    break;
            }
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        switch ($sortBy) {
            case 'employee_name':
                $query->join('employees', 'payroll_records.employee_id', '=', 'employees.employee_id')
                      ->orderBy('employees.first_name', $sortDirection)
                      ->orderBy('employees.surname', $sortDirection)
                      ->select('payroll_records.*');
                break;
            case 'net_salary':
            case 'basic_salary':
            case 'payroll_month':
            case 'payroll_id':
                $query->orderBy($sortBy, $sortDirection);
                break;
            default:
                $query->orderBy('created_at', $sortDirection);
                break;
        }

        $payrolls = $query->get();
        
        $filename = 'payroll_records_' . now()->format('Y_m_d_His');
        if ($request->hasAny(['search', 'status', 'month_filter', 'salary_range', 'date_from', 'date_to'])) {
            $filename .= '_filtered';
        }
        $filename .= '.xlsx';

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'exported_payroll',
            'description' => "Exported payroll records with filters. Format: " . ($request->get('detailed', false) ? 'Detailed Excel' : 'Excel'),
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Payroll', 'entity_id' => null, 'format' => ($request->get('detailed', false) ? 'Detailed Excel' : 'Excel'), 'filters' => $request->all()]),
        ]);

        // Check if detailed export is requested
        if ($request->get('detailed', false)) {
            return Excel::download(new \App\Exports\PayrollRecordsDetailedExport($payrolls), $filename);
        } else {
            return Excel::download(new PayrollRecordsExport($payrolls), $filename);
        }
    }

    public function showDeductions(Request $request, $employeeId)
    {
        $employee = \App\Models\Employee::with('gradeLevel.steps')->findOrFail($employeeId);
        $deductions = \App\Models\Deduction::with(['deductionType', 'employee.gradeLevel.steps'])
            ->where('employee_id', $employeeId)
            ->paginate(10, ['*'], 'deductions_page');
        $deductionTypes = \App\Models\DeductionType::where('is_statutory', false)->get();

        return view('payroll.show_deductions', compact('employee', 'deductions', 'deductionTypes'));
    }

    public function showAdditions(Request $request, $employeeId)
    {
        $employee = \App\Models\Employee::findOrFail($employeeId);
        $additions = \App\Models\Addition::where('employee_id', $employeeId)
            ->paginate(10, ['*'], 'additions_page');
        $additionTypes = \App\Models\AdditionType::where('is_statutory', false)->get();

        return view('payroll.show_additions', compact('employee', 'additions', 'additionTypes'));
    }

    public function storeDeduction(Request $request, $employeeId)
    {
        $employee = Employee::with('gradeLevel.steps', 'step', 'appointmentType')->findOrFail($employeeId);
        $deductionType = DeductionType::find($request->deduction_type_id);
        
        // Prevent creating statutory deductions individually - they should be created via bulk process
        if ($deductionType && $deductionType->is_statutory) {
            return redirect()->back()
                ->withErrors(['error' => 'Statutory deductions must be created via the bulk deduction process.'])
                ->withInput();
        }
        
        // Validation rules for non-statutory deductions
        $rules = [
            'deduction_type_id' => 'required|exists:deduction_types,id',
            'amount_type' => 'required|in:fixed,percentage',
            'amount' => 'required|numeric|min:0',
            'period' => 'required|in:OneTime,Monthly,Perpetual',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ];

        $request->validate($rules);
        
        // For non-statutory deductions, we need amount_type and amount
        if ($deductionType && !$deductionType->is_statutory) {
            $rules['amount_type'] = 'required|in:fixed,percentage';
            $rules['amount'] = 'required|min:0';
        }

        $request->validate($rules);

        if ($deductionType) {
            $amount = 0;
            
            // Calculate the deduction amount for non-statutory deductions
            if (!$deductionType->is_statutory) {
                if ($request->amount_type === 'percentage') {
                    // Check if employee is a contract employee using the new method
                    $isContractEmployee = $employee->isContractEmployee();
                    
                    if ($isContractEmployee) {
                        // For contract employees, use the amount field instead of step basic salary
                        if ($employee->amount && $employee->amount > 0) {
                            $contractAmount = $employee->amount;
                            if ($contractAmount > 0 && $request->amount > 0) {
                                $amount = ($request->amount / 100) * $contractAmount;
                            }
                        } else {
                            return redirect()->back()
                                ->withErrors(['error' => 'Contract employee does not have a valid amount for percentage calculation.'])
                                ->withInput();
                        }
                    } else {
                        // For regular employees, check if employee has a step with basic salary
                        if ($employee->step && $employee->step->basic_salary) {
                            // Use the specific step's basic salary for percentage calculation
                            $basicSalary = $employee->step->basic_salary;
                            if ($basicSalary > 0 && $request->amount > 0) {
                                $amount = ($request->amount / 100) * $basicSalary;
                            }
                        } else {
                            return redirect()->back()
                                ->withErrors(['error' => 'Employee does not have a valid step with basic salary.'])
                                ->withInput();
                        }
                    }
                } else {
                    // Fixed amount - use the provided amount directly
                    $amount = $request->amount;
                }
                
                // Update the deduction type with the provided amount/percentage for non-statutory deductions
                $deductionType->rate_or_amount = $request->amount;
                $deductionType->calculation_type = $request->amount_type === 'percentage' ? 'percentage' : 'fixed_amount';
                $deductionType->save();
            }
            
            $deduction = Deduction::create([
                'employee_id' => $employeeId,
                'deduction_type' => $deductionType->name,
                'amount' => $amount,
                'amount_type' => $request->amount_type ?? null,
                'deduction_period' => $request->period,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'deduction_type_id' => $deductionType->id,
            ]);

            AuditTrail::create([
                'user_id' => Auth::id(),
                'action' => 'created_deduction',
                'description' => "Created deduction '{$deductionType->name}' for employee ID: {$employeeId}",
                'action_timestamp' => now(),
                'log_data' => json_encode(['entity_type' => 'Deduction', 'entity_id' => $deduction->id, 'employee_id' => $employeeId, 'deduction_type' => $deductionType->name]),
            ]);
        }

        return redirect()->route('payroll.deductions.show', $employeeId)
            ->with('success', 'Deduction added successfully.');
    }

    public function storeAddition(Request $request, $employeeId)
    {
        $rules = [
            'addition_type_id' => 'required|exists:addition_types,id',
            'amount_type' => 'required|in:fixed,percentage',
            'amount' => 'required|numeric|min:0.01',
            'period' => 'required|in:OneTime,Monthly,Perpetual',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ];

        $request->validate($rules);

        $additionType = AdditionType::find($request->addition_type_id);
        
        // Prevent creating statutory additions individually - they should be created via bulk process
        if ($additionType && $additionType->is_statutory) {
            return redirect()->back()
                ->withErrors(['error' => 'Statutory additions must be created via the bulk addition process.'])
                ->withInput();
        }

        $employee = Employee::with('step', 'appointmentType')->findOrFail($employeeId);

        if ($additionType) {
            $amount = 0;
            
            if ($request->amount_type === 'percentage') {
                // Check if employee is a contract employee
                $isContractEmployee = $employee->isContractEmployee();
                
                if ($isContractEmployee) {
                    // For contract employees, use the amount field instead of step basic salary
                    if ($employee->amount && $employee->amount > 0) {
                        $contractAmount = $employee->amount;
                        $amount = ($request->amount / 100) * $contractAmount;

                        if ($amount < 0.01) {
                            return redirect()->back()
                                ->withErrors(['error' => 'Calculated addition amount is too small or zero. Check employee\'s contract amount.'])
                                ->withInput();
                        }
                    } else {
                        return redirect()->back()
                            ->withErrors(['error' => 'Contract employee does not have a valid amount for percentage calculation.'])
                            ->withInput();
                    }
                } else {
                    // For regular employees, check if employee has a step with basic salary
                    if (!$employee->step || !$employee->step->basic_salary) {
                        return redirect()->back()
                            ->withErrors(['error' => 'Employee does not have a valid step with basic salary for percentage calculation.'])
                            ->withInput();
                    }
                    // Calculate addition amount based on the employee's step basic salary
                    $basicSalary = $employee->step->basic_salary;
                    $amount = ($request->amount / 100) * $basicSalary;

                    if ($amount < 0.01) {
                        return redirect()->back()
                            ->withErrors(['error' => 'Calculated addition amount is too small or zero. Check employee\'s basic salary.'])
                            ->withInput();
                    }
                }
            } else {
                // Fixed amount - use the provided amount directly
                $amount = $request->amount;
            }

            $addition = Addition::create([
                'employee_id' => $employeeId,
                'addition_type_id' => $request->addition_type_id,
                'amount_type' => $request->amount_type,
                'amount' => $amount,
                'addition_period' => $request->period,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);

            AuditTrail::create([
                'user_id' => Auth::id(),
                'action' => 'created_addition',
                'description' => "Created addition '{$additionType->name}' for employee ID: {$employeeId}",
                'action_timestamp' => now(),
                'log_data' => json_encode(['entity_type' => 'Addition', 'entity_id' => $addition->id, 'employee_id' => $employeeId, 'addition_type' => $additionType->name]),
            ]);
        }

        return redirect()->route('payroll.additions.show', $employeeId)
            ->with('success', 'Addition added successfully.');
    }

    public function manageAllAdjustments(Request $request)
    {
        $query = \App\Models\Employee::whereIn('status', ['Active', 'Suspended'])
            ->with(['department', 'gradeLevel']);
            
        // Search functionality - include full name search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('employee_id', 'like', "%{$search}%")
                  ->orWhere('reg_no', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', surname) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("CONCAT(first_name, ' ', middle_name, ' ', surname) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("CONCAT(surname, ' ', first_name) LIKE ?", ["%{$search}%"]);
            });
        }
        
        // Department filter
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        
        // Status filter
        if ($request->filled('employee_status')) {
            $query->where('status', $request->employee_status);
        }
        
        // Sort by
        $sortBy = $request->get('sort_by', 'employee_id');
        $sortDirection = $request->get('sort_direction', 'asc');
        
        $allowedSorts = ['employee_id', 'first_name', 'surname'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('employee_id', 'asc');
        }
        
        $employees = $query->paginate(10)->withQueryString();
        
        $deductionTypes = \App\Models\DeductionType::where('is_statutory', false)->get();
        $additionTypes = \App\Models\AdditionType::where('is_statutory', false)->get();
        
        // Get departments for filter dropdown
        $departments = \App\Models\Department::orderBy('department_name')->get();

        return view('payroll.manage_all_adjustments', compact('employees', 'deductionTypes', 'additionTypes', 'departments'));
    }

    

    // Advanced search method for AJAX requests
    public function search(Request $request)
    {
        $query = PayrollRecord::with(['employee', 'gradeLevel']);

        if ($request->filled('term')) {
            $term = $request->term;
            $query->where(function($q) use ($term) {
                $q->where('payroll_id', 'like', "%{$term}%")
                  ->orWhereHas('employee', function($employeeQuery) use ($term) {
                      $employeeQuery->where('first_name', 'like', "%{$term}%")
                                   ->orWhere('surname', 'like', "%{$term}%")
                                   ->orWhere('employee_id', 'like', "%{$term}%");
                  });
            });
        }

        $results = $query->limit(10)->get()->map(function($payroll) {
            return [
                'id' => $payroll->payroll_id,
                'text' => $payroll->employee->first_name . ' ' . $payroll->employee->surname . ' - ' . $payroll->payroll_id,
                'employee_name' => $payroll->employee->first_name . ' ' . $payroll->employee->surname,
                'net_salary' => number_format($payroll->net_salary, 2),
                'status' => $payroll->status
            ];
        });

        return response()->json($results);
    }

    // Get payroll statistics for dashboard
    public function getStatistics(Request $request)
    {
        $query = PayrollRecord::query();

        // Apply same filters as index method if provided
        if ($request->filled('month_filter')) {
            $monthFilter = $request->month_filter . '-01';
            $query->whereYear('payroll_month', Carbon::parse($monthFilter)->year)
                  ->whereMonth('payroll_month', Carbon::parse($monthFilter)->month);
        }

        $stats = [
            'total_records' => $query->count(),
            'total_net_salary' => $query->sum('net_salary'),
            'total_basic_salary' => $query->sum('basic_salary'),
            'total_additions' => $query->sum('total_additions'),
            'total_deductions' => $query->sum('total_deductions'),
            'status_breakdown' => $query->select('status', DB::raw('count(*) as count'))
                                       ->groupBy('status')
                                       ->pluck('count', 'status')
                                       ->toArray(),
            'average_salary' => $query->avg('net_salary'),
        ];

        return response()->json($stats);
    }

    // Show individual payroll record
    public function show($payrollId)
    {
        $payroll = PayrollRecord::with(['employee', 'gradeLevel', 'transaction'])
            ->findOrFail($payrollId);
        
        $deductions = Deduction::where('employee_id', $payroll->employee_id)
            ->where(function($query) use ($payroll) {
                $payrollMonth = Carbon::parse($payroll->payroll_month);
                $query->where('start_date', '<=', $payrollMonth)
                      ->where(function($q) use ($payrollMonth) {
                          $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', $payrollMonth);
                      });
            })
            ->with(['deductionType', 'employee.gradeLevel.steps', 'loan']) // Eager load relationships for amount calculation
            ->get()
            ->filter(function ($deduction) use ($payroll) {
                // If this is a loan-related deduction, check if the loan was still active during this payroll period
                if ($deduction->loan_id) {
                    $loan = $deduction->loan;
                    if ($loan) {
                        // Check if the loan was still active during the payroll month
                        $payrollMonth = Carbon::parse($payroll->payroll_month);
                        $loanStart = Carbon::parse($loan->start_date);
                        $loanEnd = Carbon::parse($loan->end_date);
                        
                        // Loan is active if payroll month falls within loan's start and end dates
                        // and the loan status is not completed
                        $isLoanActive = $payrollMonth->between($loanStart, $loanEnd) && $loan->status !== 'completed';
                        
                        // Additionally, check if loan was completed before this payroll month
                        if (!$isLoanActive && $loan->status === 'completed') {
                            // Check if loan ended before this payroll month
                            $loanWasActiveBefore = $loanEnd->lt($payrollMonth);
                            // If loan ended before this payroll month, it should not appear in this payroll
                            if ($loanWasActiveBefore) {
                                return false;
                            }
                        }
                        
                        // For loans that have existing LoanDeduction records for this month, 
                        // check if they were already processed this month (to avoid showing both template deduction and processed loan deduction)
                        $existingLoanDeduction = \App\Models\LoanDeduction::where('loan_id', $loan->loan_id)
                            ->where('payroll_month', $payrollMonth->format('Y-m'))
                            ->first();
                            
                        // If a loan has an existing LoanDeduction for this month, it means it was processed 
                        // by the payroll calculation service, so we should not show the template deduction
                        return !$existingLoanDeduction && $isLoanActive;
                    }
                }
                return true; // For non-loan deductions, use the original logic
            });
        
        $additions = Addition::where('employee_id', $payroll->employee_id)
            ->where(function($query) use ($payroll) {
                $payrollMonth = Carbon::parse($payroll->payroll_month);
                $query->where('start_date', '<=', $payrollMonth->endOfMonth())
                      ->where(function($q) use ($payrollMonth) {
                          $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', $payrollMonth->startOfMonth());
                      });
            })
            ->with(['additionType', 'employee.gradeLevel.steps'])
            ->get()
            ->filter(function ($addition) use ($payroll) {
                if ($addition->addition_period === 'OneTime') {
                    $additionStartDate = Carbon::parse($addition->start_date)->startOfDay();
                    $payrollMonthStart = Carbon::parse($payroll->payroll_month)->startOfMonth();
                    $payrollMonthEnd = Carbon::parse($payroll->payroll_month)->endOfMonth();
                    return $additionStartDate->between($payrollMonthStart, $payrollMonthEnd);
                }
                return true;
            });

        return view('payroll.show', compact('payroll', 'deductions', 'additions'));
    }

    // Get detailed payroll information with deductions and additions for a specific month
    public function getDetailedPayroll($payrollId)
    {
        $payroll = PayrollRecord::with(['employee', 'gradeLevel'])
            ->findOrFail($payrollId);
        
        // Get deductions for the payroll month
        $deductions = Deduction::where('employee_id', $payroll->employee_id)
            ->where(function($query) use ($payroll) {
                $payrollMonth = Carbon::parse($payroll->payroll_month);
                $query->where('start_date', '<=', $payrollMonth)
                      ->where(function($q) use ($payrollMonth) {
                          $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', $payrollMonth);
                      });
            })
            ->with(['deductionType', 'employee.gradeLevel.steps'])
            ->get();
        
        // Get additions for the payroll month
        $additions = Addition::where('employee_id', $payroll->employee_id)
            ->where(function($query) use ($payroll) {
                $payrollMonth = Carbon::parse($payroll->payroll_month);
                $query->where('start_date', '<=', $payrollMonth)
                      ->where(function($q) use ($payrollMonth) {
                          $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', $payrollMonth);
                      });
            })
            ->with(['additionType', 'employee.gradeLevel.steps'])
            ->get()
            ->filter(function ($addition) use ($payroll) {
                if ($addition->addition_period === 'OneTime') {
                    $additionStartDate = Carbon::parse($addition->start_date)->startOfDay();
                    $payrollMonthStart = Carbon::parse($payroll->payroll_month)->startOfMonth();
                    $payrollMonthEnd = Carbon::parse($payroll->payroll_month)->endOfMonth();
                    return $additionStartDate->between($payrollMonthStart, $payrollMonthEnd);
                }
                return true;
            });
        
        // Format the response
        $deductionsData = $deductions->map(function ($deduction) {
            return [
                'type' => $deduction->deduction_type,
                'amount' => $deduction->amount,
                'formatted_amount' => $deduction->formatted_amount,
                'calculation_type' => $deduction->calculation_type_description,
            ];
        });
        
        $additionsData = $additions->map(function ($addition) {
            return [
                'type' => $addition->additionType ? $addition->additionType->name : $addition->addition_type,
                'amount' => $addition->amount,
                'formatted_amount' => $addition->formatted_amount,
                'calculation_type' => $addition->calculation_type_description,
            ];
        });
        
        return response()->json([
            'payroll' => $payroll,
            'deductions' => $deductionsData,
            'additions' => $additionsData,
            'payroll_month' => Carbon::parse($payroll->payroll_month)->format('F Y'),
        ]);
    }

    // Edit payroll record
    public function edit($payrollId)
    {
        $payroll = PayrollRecord::with(['employee', 'gradeLevel'])
            ->findOrFail($payrollId);
        
        return view('payroll.edit', compact('payroll'));
    }

    // Update payroll record
    public function update(Request $request, $payrollId)
    {
        $request->validate([
            'status' => 'required|in:Pending,Processed,Approved,Paid',
            'payment_date' => 'nullable|date',
            'remarks' => 'nullable|string|max:500',
        ]);

        $payroll = PayrollRecord::findOrFail($payrollId);
        
        $payroll->update([
            'status' => $request->status,
            'payment_date' => $request->payment_date,
            'remarks' => $request->remarks,
        ]);

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'updated_payroll',
            'description' => "Updated payroll record ID: {$payrollId}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => $payrollId, 'status' => $request->status]),
        ]);

        return redirect()->route('payroll.show', $payrollId)
            ->with('success', 'Payroll record updated successfully.');
    }

    // Delete payroll record
    public function destroy($payrollId)
    {
        $payroll = PayrollRecord::findOrFail($payrollId);
        
        // Delete associated transaction if exists
        if ($payroll->transaction) {
            $payroll->transaction->delete();
        }
        
        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'deleted_payroll',
            'description' => "Deleted payroll record ID: {$payrollId}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => $payrollId]),
        ]);

        $payroll->delete();

        return redirect()->route('payroll.index')
            ->with('success', 'Payroll record deleted successfully.');
    }

    // Bulk update payroll status
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'payroll_ids' => 'required|array',
            'payroll_ids.*' => 'exists:payroll_records,payroll_id',
            'status' => 'required|in:Pending,Processed,Approved,Paid',
        ]);

        $updated = PayrollRecord::whereIn('payroll_id', $request->payroll_ids)
                                ->update([
                                    'status' => $request->status,
                                    'updated_at' => now()
                                ]);

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'bulk_updated_payroll_status',
            'description' => "Bulk updated status to '{$request->status}' for " . count($request->payroll_ids) . " payroll records.",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => null, 'payroll_ids' => $request->payroll_ids, 'status' => $request->status]),
        ]);

        return redirect()->back()->with('success', "Updated {$updated} payroll records to {$request->status} status.");
    }

    // Recalculate payroll for a specific record
    public function recalculate($payrollId)
    {
        $payroll = PayrollRecord::with(['employee', 'gradeLevel'])
            ->findOrFail($payrollId);

        if (!$payroll->employee || !$payroll->employee->gradeLevel) {
            return redirect()->back()
                ->with('error', 'Cannot recalculate: Employee or grade level not found.');
        }

        // Get the payroll month
        $month = Carbon::parse($payroll->payroll_month)->format('Y-m');
        
        // Check if the employee is suspended and pass the correct flag
        $isSuspended = $payroll->employee->status === 'Suspended';
        
        // Recalculate using the payroll service
        $calculation = $this->payrollCalculationService->calculatePayroll($payroll->employee, $month, $isSuspended);

        // Update the payroll record
        $payroll->update([
            'basic_salary' => $calculation['basic_salary'], // Use calculated basic salary (might be halved for suspended)
            'total_additions' => $calculation['total_additions'],
            'total_deductions' => $calculation['total_deductions'],
            'net_salary' => $calculation['net_salary'],
            'remarks' => ($payroll->remarks ?? '') . ' | Recalculated on ' . now()->format('Y-m-d H:i:s'),
        ]);

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'recalculated_payroll',
            'description' => "Recalculated payroll for record ID: {$payrollId}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => $payrollId]),
        ]);

        // Update associated transaction amount if exists
        if ($payroll->transaction) {
            $payroll->transaction->update([
                'amount' => $calculation['net_salary']
            ]);
        }

        return redirect()->back()
            ->with('success', 'Payroll recalculated successfully.');
    }

    // Approve payroll record
    public function approve($payrollId)
    {
        $payroll = PayrollRecord::findOrFail($payrollId);
        
        $payroll->update([
            'status' => 'Approved',
            'updated_at' => now()
        ]);

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'approved_payroll',
            'description' => "Approved payroll record ID: {$payrollId}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => $payrollId]),
        ]);

        return redirect()->back()
            ->with('success', 'Payroll record approved successfully.');
    }

    // Reject payroll record
    public function reject(Request $request, $payrollId)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $payroll = PayrollRecord::findOrFail($payrollId);
        
        $payroll->update([
            'status' => 'Rejected',
            'remarks' => ($payroll->remarks ?? '') . ' | Rejected: ' . $request->reason,
            'updated_at' => now()
        ]);

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'rejected_payroll',
            'description' => "Rejected payroll record ID: {$payrollId} with reason: {$request->reason}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => $payrollId, 'reason' => $request->reason]),
        ]);

        return redirect()->back()
            ->with('success', 'Payroll record rejected.');
    }

    public function additions(Request $request)
    {
        $allAdditionTypes = AdditionType::all();
        $statutoryAdditions = $allAdditionTypes->where('is_statutory', true);
        $nonStatutoryAdditions = $allAdditionTypes->where('is_statutory', false);

        $departments = Department::orderBy('department_name')->get();
        $gradeLevels = GradeLevel::orderBy('name')->get();
        $appointmentTypes = \App\Models\AppointmentType::all();

        $employeesQuery = Employee::whereIn('status', ['Active', 'Suspended'])->with(['department', 'gradeLevel']);

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $employeesQuery->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                    ->orWhere('surname', 'like', "%{$searchTerm}%")
                    ->orWhere('employee_id', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('department_id')) {
            $employeesQuery->where('department_id', $request->department_id);
        }

        if ($request->filled('grade_level_id')) {
            $employeesQuery->where('grade_level_id', $request->grade_level_id);
        }

        $appointmentTypeId = $request->input('appointment_type_id', 1); // Default to Permanent
        $employeesQuery->where('appointment_type_id', $appointmentTypeId);

        $employees = $employeesQuery->paginate(20)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('payroll._employee_rows', compact('employees'))->render(),
                'next_page_url' => $employees->nextPageUrl(),
            ]);
        }

        return view('payroll.additions', compact('statutoryAdditions', 'nonStatutoryAdditions', 'departments', 'gradeLevels', 'employees', 'appointmentTypes'));
    }

    public function storeBulkAdditions(Request $request)
    {
        $request->validate([
            'addition_types' => 'sometimes|array',
            'addition_types.*' => 'integer|exists:addition_types,id',
            'type_id' => 'sometimes|integer|exists:addition_types,id',
            'period' => 'required_with:type_id|nullable|in:OneTime,Monthly,Perpetual',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'amount' => 'required_with:type_id|nullable|numeric|min:0',
            'amount_type' => 'required_with:type_id|nullable|in:fixed,percentage',
            'employee_ids' => 'required_if:select_all_pages,0|array',
            'employee_ids.*' => 'exists:employees,employee_id',
        ]);

        if (!$request->filled('addition_types') && !$request->filled('type_id')) {
            return redirect()->back()
                ->withErrors(['addition_error' => 'Please select at least one addition type.'])
                ->withInput();
        }

        $employees = collect();

        if ($request->input('select_all_pages') == '1') {
            $employeesQuery = Employee::whereIn('status', ['Active', 'Suspended'])->with('gradeLevel');

            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $employeesQuery->where(function ($q) use ($searchTerm) {
                    $q->where('first_name', 'like', "%{$searchTerm}%")
                        ->orWhere('surname', 'like', "%{$searchTerm}%")
                        ->orWhere('employee_id', 'like', "%{$searchTerm}%");
                });
            }

            if ($request->filled('department_id')) {
                $employeesQuery->where('department_id', $request->department_id);
            }

            if ($request->filled('grade_level_id')) {
                $employeesQuery->where('grade_level_id', $request->grade_level_id);
            }
            
            // Always apply appointment type filter, defaulting to 1 (Permanent) if not specified
            $appointmentTypeId = $request->input('appointment_type_id', 1);
            $employeesQuery->where('appointment_type_id', $appointmentTypeId);
            
            $employees = $employeesQuery->get();
        } else {
            $employees = Employee::whereIn('employee_id', $request->employee_ids)->with('gradeLevel', 'step')->get();
        }

        $additionTypeIds = $request->input('addition_types', []);
        if ($request->filled('type_id')) {
            $additionTypeIds[] = $request->input('type_id');
        }
        
        $additionTypes = AdditionType::findMany($additionTypeIds);

        $data = $request->only(['period', 'start_date', 'end_date', 'amount', 'amount_type']);

        foreach ($employees as $employee) {
            foreach ($additionTypes as $additionType) {
                $amount = 0;
                if ($additionType->is_statutory) {
                    if ($additionType->calculation_type === 'percentage') {
                        if ($employee->step && $employee->step->basic_salary) {
                            $amount = ($additionType->rate_or_amount / 100) * $employee->step->basic_salary;
                        }
                    } else {
                        $amount = $additionType->rate_or_amount;
                    }
                } else {
                    if ($data['amount_type'] === 'percentage') {
                        if ($employee->gradeLevel && $employee->gradeLevel->basic_salary) {
                            $amount = ($data['amount'] / 100) * $employee->gradeLevel->basic_salary;
                        } else {
                            continue;
                        }
                    } else {
                        $amount = $data['amount'];
                    }
                }

                Addition::create([
                    'employee_id' => $employee->employee_id,
                    'addition_type_id' => $additionType->id,
                    'amount' => $amount,
                    'addition_period' => $additionType->is_statutory ? 'Monthly' : $data['period'],
                    'start_date' => $data['start_date'],
                    'end_date' => $additionType->is_statutory ? null : $data['end_date'],
                ]);
            }
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'bulk_created_additions',
            'description' => "Bulk created additions for " . $employees->count() . " employees.",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Addition', 'entity_id' => null, 'employee_count' => $employees->count(), 'addition_types' => $additionTypeIds]),
        ]);

        return redirect()->route('payroll.additions')
            ->with('success', 'Bulk addition assignment completed successfully for ' . $employees->count() . ' employees.');
    }

    public function deductions(Request $request)
    {
        $allDeductionTypes = DeductionType::all();
        $statutoryDeductions = $allDeductionTypes->where('is_statutory', true);
        $nonStatutoryDeductions = $allDeductionTypes->where('is_statutory', false);

        $departments = Department::orderBy('department_name')->get();
        $gradeLevels = GradeLevel::orderBy('name')->get();
        $appointmentTypes = \App\Models\AppointmentType::all();

        $employeesQuery = Employee::whereIn('status', ['Active', 'Suspended'])->with(['department', 'gradeLevel']);

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $employeesQuery->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                    ->orWhere('surname', 'like', "%{$searchTerm}%")
                    ->orWhere('employee_id', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('department_id')) {
            $employeesQuery->where('department_id', $request->department_id);
        }

        if ($request->filled('grade_level_id')) {
            $employeesQuery->where('grade_level_id', $request->grade_level_id);
        }

        if ($request->filled('appointment_type_id')) {
            $employeesQuery->where('appointment_type_id', $request->appointment_type_id);
        }

        $employees = $employeesQuery->paginate(20)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('payroll._employee_rows', compact('employees'))->render(),
                'next_page_url' => $employees->nextPageUrl(),
            ]);
        }

        return view('payroll.deductions', compact('statutoryDeductions', 'nonStatutoryDeductions', 'departments', 'gradeLevels', 'employees', 'appointmentTypes'));
    }

    public function storeBulkDeductions(Request $request)
    {
        $request->validate([
            'deduction_types' => 'sometimes|array',
            'deduction_types.*' => 'integer|exists:deduction_types,id',
            'type_id' => 'sometimes|integer|exists:deduction_types,id',
            'statutory_deduction_month' => 'required_with:deduction_types|date_format:Y-m',
            'period' => 'required_with:type_id|nullable|in:OneTime,Monthly,Perpetual',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'amount' => 'required_with:type_id|nullable|numeric|min:0',
            'amount_type' => 'required_with:type_id|nullable|in:fixed,percentage',
            'employee_ids' => 'required_if:select_all_pages,0|array',
            'employee_ids.*' => 'exists:employees,employee_id',
        ]);

        if (!$request->filled('deduction_types') && !$request->filled('type_id')) {
            return redirect()->back()
                ->withErrors(['deduction_error' => 'Please select at least one deduction type.'])
                ->withInput();
        }

        $employees = collect();

        if ($request->input('select_all_pages') == '1') {
            $employeesQuery = Employee::whereIn('status', ['Active', 'Suspended'])->with('gradeLevel');

            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $employeesQuery->where(function ($q) use ($searchTerm) {
                    $q->where('first_name', 'like', "%{$searchTerm}%")
                        ->orWhere('surname', 'like', "%{$searchTerm}%")
                        ->orWhere('employee_id', 'like', "%{$searchTerm}%");
                });
            }

            if ($request->filled('department_id')) {
                $employeesQuery->where('department_id', $request->department_id);
            }

            if ($request->filled('grade_level_id')) {
                $employeesQuery->where('grade_level_id', $request->grade_level_id);
            }

            $appointmentTypeId = $request->input('appointment_type_id', 1); // Default to Permanent
            $employeesQuery->where('appointment_type_id', $appointmentTypeId);

            $employees = $employeesQuery->get();
        } else {
            $employees = Employee::whereIn('employee_id', $request->employee_ids)->with('gradeLevel')->get();
        }

        $deductionTypeIds = $request->input('deduction_types', []);
        if ($request->filled('type_id')) {
            $deductionTypeIds[] = $request->input('type_id');
        }
        
        $deductionTypes = DeductionType::findMany($deductionTypeIds);

        $data = $request->only(['period', 'start_date', 'end_date', 'amount', 'amount_type', 'statutory_deduction_month']);

        foreach ($employees as $employee) {
            foreach ($deductionTypes as $deductionType) {
                $amount = 0;
                if ($deductionType->is_statutory) {
                    if ($deductionType->calculation_type === 'percentage') {
                        // Check if employee has a grade level with steps for statutory deductions
                        if ($employee->gradeLevel && $employee->gradeLevel->steps->isNotEmpty()) {
                            // Use the specific step if assigned, otherwise use the first step
                            $basicSalary = $employee->step ? $employee->step->basic_salary : $employee->gradeLevel->steps->first()->basic_salary;
                            if ($basicSalary > 0) {
                                // Calculate base deduction amount based on employee's basic salary
                                $amount = ($deductionType->rate_or_amount / 100) * $basicSalary;
                                
                                // For suspended employees, halve the calculated percentage-based deduction
                                if ($employee->status === 'Suspended') {
                                    $amount = $amount / 2;
                                }
                            }
                        }
                    } else {
                        // For fixed amount statutory deductions
                        // For suspended employees, halve the fixed amount
                        if ($employee->status === 'Suspended') {
                            $amount = $deductionType->rate_or_amount / 2;
                        } else {
                            $amount = $deductionType->rate_or_amount;
                        }
                    }
                } else {
                    if ($data['amount_type'] === 'percentage') {
                        if ($employee->gradeLevel && $employee->gradeLevel->basic_salary) {
                            // Calculate non-statutory percentage based deduction
                            $amount = ($data['amount'] / 100) * $employee->gradeLevel->basic_salary;
                            
                            // For suspended employees, also halve non-statutory percentage-based deductions if they are linked to basic salary
                            if ($employee->status === 'Suspended') {
                                $amount = $amount / 2;
                            }
                        } else {
                            continue;
                        }
                    } else {
                        // Fixed amount non-statutory deduction remains full for suspended employees
                        $amount = $data['amount'];
                    }
                }

                Deduction::create([
                    'employee_id' => $employee->employee_id,
                    'deduction_type' => $deductionType->name,
                    'amount' => $amount,
                    'deduction_period' => $deductionType->is_statutory ? 'Monthly' : $data['period'],
                    'start_date' => $deductionType->is_statutory ? 
                        (isset($data['statutory_deduction_month']) && $data['statutory_deduction_month'] ? 
                            $data['statutory_deduction_month'] . '-01' : 
                            date('Y-m') . '-01') : 
                        $data['start_date'],
                    'end_date' => $deductionType->is_statutory ? 
                        date('Y-m-t', strtotime(
                            (isset($data['statutory_deduction_month']) && $data['statutory_deduction_month'] ? 
                                $data['statutory_deduction_month'] : 
                                date('Y-m')) . '-01'
                        )) : 
                        $data['end_date'],
                    'deduction_type_id' => $deductionType->id,
                ]);
            }
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'bulk_created_deductions',
            'description' => "Bulk created deductions for " . $employees->count() . " employees.",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Deduction', 'entity_id' => null, 'employee_count' => $employees->count(), 'deduction_types' => $deductionTypeIds]),
        ]);

        return redirect()->route('payroll.deductions')
            ->with('success', 'Bulk deduction assignment completed successfully for ' . $employees->count() . ' employees.');
    }

    public function bulkDeductions()
    {
        return view('payroll.bulk_deductions');
    }

    // Bulk send all payroll records for review
    public function bulkSendForReview(Request $request)
    {
        if ($request->has('select_all_pages') && $request->input('select_all_pages') == '1') {
            // Apply the same filters used in the index method to get all matching records
            $query = PayrollRecord::query();

            // Apply filters similar to index method
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('month_filter')) {
                $monthFilter = $request->month_filter . '-01';
                $query->whereYear('payroll_month', Carbon::parse($monthFilter)->year)
                      ->whereMonth('payroll_month', Carbon::parse($monthFilter)->month);
            }

            // Only payroll records with 'Pending Review' status can be sent for review
            $query->where('status', 'Pending Review');

            $updated = $query->update([
                'status' => 'Under Review',
                'updated_at' => now()
            ]);

            $payrollIds = $query->pluck('payroll_id')->toArray();
        } else {
            $request->validate([
                'payroll_ids' => 'required|array',
                'payroll_ids.*' => 'exists:payroll_records,payroll_id',
            ]);

            // Only payroll records with 'Pending Review' status can be sent for review
            $updated = PayrollRecord::whereIn('payroll_id', $request->payroll_ids)
                                    ->where('status', 'Pending Review')
                                    ->update([
                                        'status' => 'Under Review',
                                        'updated_at' => now()
                                    ]);

            $payrollIds = $request->payroll_ids;
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'bulk_sent_for_review',
            'description' => "Bulk sent {$updated} payroll records for review",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => null, 'payroll_ids' => $payrollIds, 'updated_count' => $updated]),
        ]);

        return redirect()->back()
            ->with('success', "Successfully sent {$updated} payroll records for review.");
    }

    // Bulk mark all payroll records as reviewed
    public function bulkMarkAsReviewed(Request $request)
    {
        if ($request->has('select_all_pages') && $request->input('select_all_pages') == '1') {
            // Apply the same filters used in the index method to get all matching records
            $query = PayrollRecord::query();

            // Apply filters similar to index method
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('month_filter')) {
                $monthFilter = $request->month_filter . '-01';
                $query->whereYear('payroll_month', Carbon::parse($monthFilter)->year)
                      ->whereMonth('payroll_month', Carbon::parse($monthFilter)->month);
            }

            // Only payroll records with 'Under Review' status can be marked as reviewed
            $query->where('status', 'Under Review');

            $updated = $query->update([
                'status' => 'Reviewed',
                'updated_at' => now()
            ]);

            $payrollIds = $query->pluck('payroll_id')->toArray();
        } else {
            $request->validate([
                'payroll_ids' => 'required|array',
                'payroll_ids.*' => 'exists:payroll_records,payroll_id',
            ]);

            // Only payroll records with 'Under Review' status can be marked as reviewed
            $updated = PayrollRecord::whereIn('payroll_id', $request->payroll_ids)
                                    ->where('status', 'Under Review')
                                    ->update([
                                        'status' => 'Reviewed',
                                        'updated_at' => now()
                                    ]);

            $payrollIds = $request->payroll_ids;
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'bulk_marked_as_reviewed',
            'description' => "Bulk marked {$updated} payroll records as reviewed",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => null, 'payroll_ids' => $payrollIds, 'updated_count' => $updated]),
        ]);

        return redirect()->back()
            ->with('success', "Successfully marked {$updated} payroll records as reviewed.");
    }

    // Bulk send all payroll records for final approval
    public function bulkSendForApproval(Request $request)
    {
        if ($request->has('select_all_pages') && $request->input('select_all_pages') == '1') {
            // Apply the same filters used in the index method to get all matching records
            $query = PayrollRecord::query();

            // Apply filters similar to index method
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('month_filter')) {
                $monthFilter = $request->month_filter . '-01';
                $query->whereYear('payroll_month', Carbon::parse($monthFilter)->year)
                      ->whereMonth('payroll_month', Carbon::parse($monthFilter)->month);
            }

            // Only payroll records with 'Reviewed' status can be sent for final approval
            $query->where('status', 'Reviewed');

            $updated = $query->update([
                'status' => 'Pending Final Approval',
                'updated_at' => now()
            ]);

            $payrollIds = $query->pluck('payroll_id')->toArray();
        } else {
            $request->validate([
                'payroll_ids' => 'required|array',
                'payroll_ids.*' => 'exists:payroll_records,payroll_id',
            ]);

            // Only payroll records with 'Reviewed' status can be sent for final approval
            $updated = PayrollRecord::whereIn('payroll_id', $request->payroll_ids)
                                    ->where('status', 'Reviewed')
                                    ->update([
                                        'status' => 'Pending Final Approval',
                                        'updated_at' => now()
                                    ]);

            $payrollIds = $request->payroll_ids;
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'bulk_sent_for_final_approval',
            'description' => "Bulk sent {$updated} payroll records for final approval",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => null, 'payroll_ids' => $payrollIds, 'updated_count' => $updated]),
        ]);

        return redirect()->back()
            ->with('success', "Successfully sent {$updated} payroll records for final approval.");
    }

    // Bulk final approve all payroll records
    public function bulkFinalApprove(Request $request)
    {
        if ($request->has('select_all_pages') && $request->input('select_all_pages') == '1') {
            // Apply the same filters used in the index method to get all matching records
            $query = PayrollRecord::query();

            // Apply filters similar to index method
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('month_filter')) {
                $monthFilter = $request->month_filter . '-01';
                $query->whereYear('payroll_month', Carbon::parse($monthFilter)->year)
                      ->whereMonth('payroll_month', Carbon::parse($monthFilter)->month);
            }

            // Only payroll records with 'Pending Final Approval' status can be finally approved
            $query->where('status', 'Pending Final Approval');

            $updated = $query->update([
                'status' => 'Approved',
                'updated_at' => now()
            ]);

            $payrollIds = $query->pluck('payroll_id')->toArray();
        } else {
            $request->validate([
                'payroll_ids' => 'required|array',
                'payroll_ids.*' => 'exists:payroll_records,payroll_id',
            ]);

            // Only payroll records with 'Pending Final Approval' status can be finally approved
            $updated = PayrollRecord::whereIn('payroll_id', $request->payroll_ids)
                                    ->where('status', 'Pending Final Approval')
                                    ->update([
                                        'status' => 'Approved',
                                        'updated_at' => now()
                                    ]);

            $payrollIds = $request->payroll_ids;
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'bulk_final_approved_payroll',
            'description' => "Bulk finally approved {$updated} payroll records",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => null, 'payroll_ids' => $payrollIds, 'updated_count' => $updated]),
        ]);

        return redirect()->back()
            ->with('success', "Successfully finally approved {$updated} payroll records.");
    }

    // Individual send payroll for review
    public function sendForReview($payrollId)
    {
        $payroll = PayrollRecord::findOrFail($payrollId);
        
        // Only payroll records with 'Pending Review' status can be sent for review
        if ($payroll->status !== 'Pending Review') {
            return redirect()->back()
                ->with('error', 'Payroll record is not in Pending Review status.');
        }

        $payroll->update([
            'status' => 'Under Review',
            'updated_at' => now()
        ]);

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'sent_payroll_for_review',
            'description' => "Sent payroll record ID: {$payrollId} for review",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => $payrollId]),
        ]);

        return redirect()->back()
            ->with('success', 'Payroll record sent for review successfully.');
    }

    // Individual mark payroll as reviewed
    public function markAsReviewed($payrollId)
    {
        $payroll = PayrollRecord::findOrFail($payrollId);
        
        // Only payroll records with 'Under Review' status can be marked as reviewed
        if ($payroll->status !== 'Under Review') {
            return redirect()->back()
                ->with('error', 'Payroll record is not in Under Review status.');
        }

        $payroll->update([
            'status' => 'Reviewed',
            'updated_at' => now()
        ]);

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'marked_payroll_as_reviewed',
            'description' => "Marked payroll record ID: {$payrollId} as reviewed",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => $payrollId]),
        ]);

        return redirect()->back()
            ->with('success', 'Payroll record marked as reviewed successfully.');
    }

    // Individual send payroll for approval
    public function sendForApproval($payrollId)
    {
        $payroll = PayrollRecord::findOrFail($payrollId);
        
        // Only payroll records with 'Reviewed' status can be sent for final approval
        if ($payroll->status !== 'Reviewed') {
            return redirect()->back()
                ->with('error', 'Payroll record is not in Reviewed status.');
        }

        $payroll->update([
            'status' => 'Pending Final Approval',
            'updated_at' => now()
        ]);

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'sent_payroll_for_final_approval',
            'description' => "Sent payroll record ID: {$payrollId} for final approval",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => $payrollId]),
        ]);

        return redirect()->back()
            ->with('success', 'Payroll record sent for final approval successfully.');
    }

    // Individual final approve payroll
    public function finalApprove($payrollId)
    {
        $payroll = PayrollRecord::findOrFail($payrollId);
        
        // Only payroll records with 'Pending Final Approval' status can be finally approved
        if ($payroll->status !== 'Pending Final Approval') {
            return redirect()->back()
                ->with('error', 'Payroll record is not in Pending Final Approval status.');
        }

        $payroll->update([
            'status' => 'Approved',
            'updated_at' => now()
        ]);

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'finally_approved_payroll',
            'description' => "Finally approved payroll record ID: {$payrollId}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'PayrollRecord', 'entity_id' => $payrollId]),
        ]);

        return redirect()->back()
            ->with('success', 'Payroll record finally approved successfully.');
    }

    // Generate pension payroll for all active pensioners
    public function generatePensionPayroll(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));

        try {
            $pensionPayrollService = new PensionPayrollService();
            $processedPensioners = $pensionPayrollService->generatePensionPayroll($month);

            AuditTrail::create([
                'user_id' => Auth::id(),
                'action' => 'generated_pension_payroll',
                'description' => "Generated pension payroll for month: {$month} for " . count($processedPensioners) . " pensioners.",
                'action_timestamp' => now(),
                'log_data' => json_encode(['entity_type' => 'PensionPayroll', 'entity_id' => null, 'month' => $month, 'pensioner_count' => count($processedPensioners)]),
            ]);

            return redirect()->route('payroll.index')
                ->with('success', 'Pension payroll generated successfully for ' . $month . ' (' . count($processedPensioners) . ' pensioners)');
        } catch (\Exception $e) {
            \Log::error('Error generating pension payroll: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Error generating pension payroll: ' . $e->getMessage());
        }
    }

    
}