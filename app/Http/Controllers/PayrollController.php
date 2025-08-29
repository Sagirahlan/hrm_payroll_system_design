<?php

namespace App\Http\Controllers;

use App\Models\PayrollRecord;
use App\Models\Employee;
use App\Models\PaymentTransaction;
use App\Models\Deduction;
use App\Models\Addition;
use App\Services\PayrollCalculationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PayrollRecordsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\GradeLevel;

class PayrollController extends Controller
{
    protected $payrollCalculationService;

    public function __construct(PayrollCalculationService $payrollCalculationService)
    {
        $this->middleware(['auth', 'permission:manage_payroll']);
        $this->payrollCalculationService = $payrollCalculationService;
    }

    // Generate payroll for a specific month
    public function generatePayroll(Request $request)
    {
        set_time_limit(0); // Set no time limit for this script
        $month = $request->input('month', now()->format('Y-m'));
        
        // Fetch only employees with valid grade level assigned
        $employees = Employee::where('status', 'Active')
            ->whereNotNull('grade_level_id') // Ensures grade level exists
            ->with('gradeLevel')      // Load related grade level
            ->get();

        foreach ($employees as $employee) {
            // Safety check if relationship failed
            if (!$employee->gradeLevel || !$employee->gradeLevel->id) {
                continue;
            }

            $calculation = $this->payrollCalculationService->calculatePayroll($employee, $month);

            // Create the payroll record
            $payroll = PayrollRecord::create([
                'employee_id' => $employee->employee_id,
                'grade_level_id' => $employee->gradeLevel->id,
                'payroll_month' => $month . '-01',
                'basic_salary' => $employee->gradeLevel->basic_salary, // ✅ add this
                'total_additions' => $calculation['total_additions'],
                'total_deductions' => $calculation['total_deductions'],
                'net_salary' => $calculation['net_salary'],
                'status' => 'Pending',
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

        return redirect()->route('payroll.index')
            ->with('success', 'Payroll generated successfully for ' . $month);
    }

    // Display payroll records with search and filter functionality
    public function index(Request $request)
    {
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

        // Pagination
        $perPage = $request->get('per_page', 20);
        $payrolls = $query->paginate($perPage);

        return view('payroll.index', compact('payrolls'));
    }

    // Generate pay slip
    public function generatePaySlip($payrollId)
    {
        $payroll = PayrollRecord::with(['employee', 'gradeLevel'])
            ->findOrFail($payrollId);
        $deductions = Deduction::where('employee_id', $payroll->employee_id)->get();
        $additions = Addition::where('employee_id', $payroll->employee_id)->get();
        $pdf = Pdf::loadView('payroll.payslip', compact('payroll', 'deductions', 'additions'));
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

        return Excel::download(new PayrollRecordsExport($payrolls), $filename);
    }

    // Manage deductions and additions
    public function manageDeductionsAdditions($employeeId)
    {
        $employee = Employee::findOrFail($employeeId);
        $deductions = Deduction::where('employee_id', $employeeId)->get();
        $additions = Addition::where('employee_id', $employeeId)->get();
        return view('payroll.deductions_additions', compact('employee', 'deductions', 'additions'));
    }

    // Store a new deduction
    public function storeDeduction(Request $request, $employeeId)
    {
        $request->validate([
            'name_type' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0',
            'period' => 'required|in:OneTime,Monthly,Perpetual',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        Deduction::create([
            'employee_id' => $employeeId,
            'name_type' => $request->deduction_type,
            'amount' => $request->amount,
            'period' => $request->deduction_period,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return redirect()->route('payroll.deductions_additions', $employeeId)
            ->with('success', 'Deduction added successfully.');
    }

    public function storeAddition(Request $request, $employeeId)
    {
        $request->validate([
            'name_type' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0',
            'period' => 'required|in:OneTime,Monthly,Perpetual',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        \App\Models\Addition::create([
            'employee_id' => $employeeId,
            'name_type' => $request->addition_type,
            'amount' => $request->amount,
            'period' => $request->addition_period,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return redirect()->route('payroll.deductions_additions', $employeeId)
            ->with('success', 'Addition added successfully.');
    }

    public function submitAdjustments(Request $request)
    {
        $validated = $request->validate([
            'adjustment_type' => 'required|in:addition,deduction',
            'name_type'       => 'required|string|max:255',
            'amount'          => 'required|numeric|min:0',
            'period'          => 'required|in:OneTime,Monthly,Perpetual',
            'start_date'      => 'required|date',
            'end_date'        => 'nullable|date|after_or_equal:start_date',
            'employee_ids'    => 'required|array',
            'employee_ids.*'  => 'exists:employees,employee_id',
        ]);

        foreach ($validated['employee_ids'] as $empId) {
            $data = [
                'employee_id' => $empId,
                'name_type' => $validated['name_type'],  
                'amount'      => $validated['amount'],
                'period'      => $validated['period'],
                'start_date'  => $validated['start_date'],
                'end_date'    => $validated['end_date'],
                'created_at'  => now(),
                'updated_at'  => now(),
            ];

            if ($validated['adjustment_type'] === 'addition') {
                DB::table('additions')->insert($data);
            } elseif ($validated['adjustment_type'] === 'deduction') {
                DB::table('deductions')->insert($data);
            }
        }

        return redirect()->back()->with('success', 'Adjustments applied successfully.');
    }

    public function showBulkAdjustmentForm()
    {
        $employees = Employee::where('status', 'Active')->get();
       
        return view('payroll.bulk_adjustments', compact('employees'));
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
            ->get();
        
        $additions = Addition::where('employee_id', $payroll->employee_id)
            ->where(function($query) use ($payroll) {
                $payrollMonth = Carbon::parse($payroll->payroll_month);
                $query->where('start_date', '<=', $payrollMonth)
                      ->where(function($q) use ($payrollMonth) {
                          $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', $payrollMonth);
                      });
            })
            ->get();

        return view('payroll.show', compact('payroll', 'deductions', 'additions'));
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
        
        // Recalculate using the payroll service
        $calculation = $this->payrollCalculationService->calculatePayroll($payroll->employee, $month);

        // Update the payroll record
        $payroll->update([
            'basic_salary' => $payroll->employee->gradeLevel->basic_salary,
            'total_additions' => $calculation['total_additions'],
            'total_deductions' => $calculation['total_deductions'],
            'net_salary' => $calculation['net_salary'],
            'remarks' => ($payroll->remarks ?? '') . ' | Recalculated on ' . now()->format('Y-m-d H:i:s'),
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

        return redirect()->back()
            ->with('success', 'Payroll record rejected.');
    }
}