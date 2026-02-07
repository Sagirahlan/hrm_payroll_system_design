<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Employee;
use App\Models\Deduction;
use App\Models\DeductionType;
use App\Models\Addition;
use App\Models\AdditionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(['permission:view_loans'], ['only' => ['index', 'show']]);
        $this->middleware(['permission:create_loans'], ['only' => ['create', 'store']]);
        $this->middleware(['permission:delete_loans'], ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the loans.
     */
    public function index()
    {
        $loans = Loan::with(['employee', 'deductionType'])->paginate(15);

        return view('loans.index', compact('loans'));
    }

    /**
     * Show the form for creating a new loan.
     */
    public function create()
    {
        // Get all employees who have loan-related additions
        $employees = Employee::whereHas('additions', function ($query) {
            $query->whereHas('additionType', function ($subquery) {
                $subquery->whereRaw("LOWER(name) LIKE '%loan%' OR LOWER(name) LIKE '%advance%'");
            });
        })->get();

        // Filter employees to only include those who have at least one loan-related addition that doesn't have an active loan
        $filteredEmployees = $employees->filter(function ($employee) {
            $additions = $employee->additions;
            foreach ($additions as $addition) {
                if ($this->isLoanRelatedType($addition->additionType->name) && !$employee->hasActiveLoanForAdditionType($addition->additionType->name)) {
                    return true; // This employee has at least one loan-related addition without an active loan
                }
            }
            return false; // All loan-related additions for this employee have active loans
        });

        // Get deduction types that match loan-related addition types
        $loanRelatedAdditionTypes = AdditionType::whereRaw("LOWER(name) LIKE '%loan%' OR LOWER(name) LIKE '%advance%'")->get();

        // Create corresponding deduction types based on addition types
        $deductionTypes = collect();
        foreach ($loanRelatedAdditionTypes as $additionType) {
            // Look for a matching deduction type with the same name
            $deductionType = DeductionType::where('name', $additionType->name)->first();
            if ($deductionType) {
                $deductionTypes->push($deductionType);
            }
        }

        // If no corresponding deduction types are found, use all deduction types
        if ($deductionTypes->isEmpty()) {
            $deductionTypes = DeductionType::all();
        }

        return view('loans.create', compact('filteredEmployees', 'deductionTypes'));
    }

    /**
     * Get additions for a specific employee.
     */
    public function getAdditionsForEmployee(Employee $employee)
    {
        // Get additions for this employee that don't have active loans of the same type
        $availableAdditions = collect();

        $additions = $employee->additions()->with('additionType')->get();

        foreach ($additions as $addition) {
            // Only include addition types that are loan-related (not allowances)
            if ($this->isLoanRelatedType($addition->additionType->name) && !$employee->hasActiveLoanForAdditionType($addition->additionType->name)) {
                $availableAdditions->push($addition);
            }
        }

        // Prepare response data to include both additions and matching deduction types
        $response = [];
        foreach ($availableAdditions as $addition) {
            // Find matching deduction type for each addition type
            $deductionType = DeductionType::where('name', $addition->additionType->name)->first();

            // Safely get addition date and month
            $additionDate = null;
            $additionMonth = null;

            if ($addition->start_date) {
                try {
                    $additionDate = $addition->start_date->format('Y-m-d');
                    $additionMonth = $addition->start_date->format('Y-m');
                } catch (\Exception $e) {
                    \Log::warning('Error formatting addition start_date', ['addition_id' => $addition->addition_id, 'error' => $e->getMessage()]);
                }
            }

            // Fallback to created_at if start_date is not available
            if (!$additionDate && $addition->created_at) {
                try {
                    $additionDate = $addition->created_at->format('Y-m-d');
                    $additionMonth = $addition->created_at->format('Y-m');
                } catch (\Exception $e) {
                    \Log::warning('Error formatting addition created_at', ['addition_id' => $addition->addition_id, 'error' => $e->getMessage()]);
                }
            }

            $item = [
                'addition_id' => $addition->addition_id,
                'amount' => $addition->amount,
                'addition_type' => $addition->additionType,
                'has_matching_deduction' => $deductionType !== null,
                'deduction_type_id' => $deductionType ? $deductionType->id : null,
                'addition_date' => $additionDate,
                'addition_month' => $additionMonth
            ];
            $response[] = $item;
        }

        return response()->json($response);
    }

    /**
     * Get employee salary information.
     */
    public function getEmployeeSalary(Employee $employee)
    {
        // Check if employee is retired - use pension amount
        if ($employee->status === 'Retired') {
            // For retired employees (pensioners), use pension amount
            $pensioner = $employee->pensioner;
            
            if ($pensioner && $pensioner->pension_amount) {
                return response()->json([
                    'basic_salary' => $pensioner->pension_amount,
                    'is_retired' => true,
                    'salary_type' => 'pension'
                ]);
            }
            
            // If no pensioner record, return error
            return response()->json([
                'basic_salary' => null,
                'error' => 'Retired employee does not have a valid pension amount configured',
                'is_retired' => true
            ]);
        }
        
        // Check if employee is a Casual employee using the new method
        $isCasualEmployee = $employee->isCasualEmployee();

        if ($isCasualEmployee) {
            // For Casual employees, use the amount field
            if ($employee->amount) {
                return response()->json([
                    'basic_salary' => $employee->amount,
                    'is_retired' => false,
                    'salary_type' => 'contract'
                ]);
            }
        } else {
            // For permanent/temporary employees, use grade level step
            $step = $employee->step;

            if ($step && $step->basic_salary) {
                return response()->json([
                    'basic_salary' => $step->basic_salary,
                    'is_retired' => false,
                    'salary_type' => 'basic_salary'
                ]);
            }
        }

        return response()->json([
            'basic_salary' => null,
            'error' => 'Employee does not have a valid salary configured',
            'is_retired' => false
        ]);
    }

    /**
     * Store a newly created loan in storage.
     */
  public function store(Request $request)
{
    $request->validate([
        'employee_id' => 'required|exists:employees,employee_id',
        'addition_id' => 'required|exists:additions,addition_id',
        'principal_amount' => 'required|numeric|min:0',
        'monthly_percentage' => 'nullable|numeric|min:0|max:100',
        'monthly_deduction' => 'nullable|numeric|min:0',
        'loan_duration_months' => 'nullable|integer|min:1',
        'deduction_start_month' => 'required|date_format:Y-m',
        'description' => 'nullable|string',
        'deduction_type_id' => 'required|exists:deduction_types,id'
    ], [
        'monthly_percentage.required_without_all' => 'Please provide either monthly percentage, monthly deduction amount, or loan duration in months.',
        'monthly_deduction.required_without_all' => 'Please provide either monthly percentage, monthly deduction amount, or loan duration in months.',
        'loan_duration_months.required_without_all' => 'Please provide either monthly percentage, monthly deduction amount, or loan duration in months.',
    ]);

    try {
        DB::beginTransaction();

        $addition = Addition::find($request->addition_id);
        $employee = Employee::find($request->employee_id);

        // Determine salary/pension amount based on employee type
        $salary = 0;
        
        // Check if employee is retired - use pension amount
        if ($employee->status === 'Retired') {
            $pensioner = $employee->pensioner;
            if ($pensioner && $pensioner->pension_amount) {
                $salary = $pensioner->pension_amount;
            }
        } 
        // Check if employee is a Casual employee
        elseif ($employee->isCasualEmployee()) {
            // For Casual employees, use the amount field
            $salary = $employee->amount;
        } 
        // For regular employees, use step basic salary
        else {
            $step = $employee->step;
            $salary = $step ? $step->basic_salary : 0;
        }

        // Validate that the employee has a salary/pension
        if (!$salary) {
            $errorMessage = $employee->status === 'Retired' 
                ? 'Retired employee does not have a valid pension amount for loan calculation.'
                : 'Employee does not have a valid salary for loan calculation.';
                
            return redirect()->back()
                ->withInput()
                ->withErrors(['employee_id' => $errorMessage]);
        }

        // CRITICAL: Principal amount is FIXED - never modify it
        $principalAmount = (float) $request->principal_amount;

        // Interest rate is no longer collected, so default to 0
        $interestRate = 0;

        // Calculate interest and total repayment (interest is 0)
        $totalInterest = 0;
        $totalRepayment = $principalAmount;

        // Priority order: loan_duration_months > monthly_deduction > monthly_percentage
        // This ensures the user's primary choice is respected

        if ($request->filled('loan_duration_months') && $request->loan_duration_months > 0) {
            // PRIORITY 1: User specified EXACT number of months
            $totalMonths = (int) $request->loan_duration_months;

            // Calculate exact monthly deduction: Total Repayment ÷ Exact Months
            $monthlyDeduction = $totalRepayment / $totalMonths;

            // Calculate what percentage this represents
            $monthlyPercentage = ($monthlyDeduction / $salary) * 100;

            \Log::info('Loan Duration Method', [
                'months_input' => $request->loan_duration_months,
                'total_months' => $totalMonths,
                'principal' => $principalAmount,
                'interest_rate' => $interestRate,
                'total_interest' => $totalInterest,
                'total_repayment' => $totalRepayment,
                'monthly_deduction' => $monthlyDeduction
            ]);

        } elseif ($request->filled('monthly_deduction') && $request->monthly_deduction > 0) {
            // PRIORITY 2: User specified monthly deduction amount
            $monthlyDeduction = (float) $request->monthly_deduction;

            // Calculate months needed - round up to ensure loan is fully paid
            $totalMonths = (int) ceil($totalRepayment / $monthlyDeduction);

            // Calculate percentage
            $monthlyPercentage = ($monthlyDeduction / $salary) * 100;

        } elseif ($request->filled('monthly_percentage') && $request->monthly_percentage > 0) {
            // PRIORITY 3: User specified percentage of salary
            $monthlyPercentage = (float) $request->monthly_percentage;

            // Calculate monthly deduction from percentage
            $monthlyDeductionBasedOnPercentage = ($monthlyPercentage / 100) * $salary;

            // Calculate months needed based on the percentage-based deduction
            $totalMonths = (int) ceil($totalRepayment / $monthlyDeductionBasedOnPercentage);

            // To ensure total repayment is fully covered, adjust the monthly deduction
            $monthlyDeduction = $totalRepayment / $totalMonths;

        } else {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Please provide either monthly percentage, monthly deduction amount, or loan duration in months.']);
        }

       // Calculate end date from deduction start month + total months
        // End date should be the last day of the final month
        // For example, if deduction start month is 2025-11 and total months is 3:
        // Month 1: November 2025, Month 2: December 2025, Month 3: January 2026
        // End date should be January 31, 2026 (last day of the 3rd month)
        $deductionStartMonth = $request->deduction_start_month; // Format: Y-m
        $startDate = \Carbon\Carbon::parse($deductionStartMonth . '-01');
        $endDate = $startDate->copy()->addMonths(max(0, $totalMonths - 1))->endOfMonth();

        // Create the loan record
        $loan = Loan::create([
            'employee_id' => $request->employee_id,
            'deduction_type_id' => $request->deduction_type_id,
            'loan_type' => $addition->additionType->name,
            'principal_amount' => $principalAmount,
            'total_interest' => $totalInterest,
            'interest_rate' => $interestRate,
            'total_repayment' => $totalRepayment,
            'monthly_deduction' => $monthlyDeduction,
            'total_months' => $totalMonths,
            'remaining_months' => $totalMonths,
            'monthly_percentage' => $monthlyPercentage,
            'deduction_start_month' => $deductionStartMonth,
            'end_date' => $endDate->format('Y-m-d'),
            'remaining_balance' => $totalRepayment, // Remaining balance should be total repayment, not just principal
            'status' => 'active',
            'description' => $request->description,
        ]);

        // Create a deduction record to show in UI
        Deduction::create([
            'employee_id' => $loan->employee_id,
            'deduction_type_id' => $loan->deduction_type_id,
            'deduction_type' => $loan->loan_type,
            'amount' => $monthlyDeduction,
            'deduction_period' => 'monthly',
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $loan->end_date,
            'loan_id' => $loan->loan_id,
        ]);

        DB::commit();

        \Log::info('Loan Created', [
            'loan_id' => $loan->loan_id,
            'total_months' => $loan->total_months,
            'monthly_deduction' => $loan->monthly_deduction,
            'principal' => $loan->principal_amount,
            'total_interest' => $loan->total_interest,
            'interest_rate' => $loan->interest_rate,
            'total_repayment' => $loan->total_repayment
        ]);

        return redirect()->route('loans.index')
            ->with('success', 'Loan created successfully.');

    } catch (\Exception $e) {
        DB::rollback();

        \Log::error('Loan Creation Failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return redirect()->back()
            ->withInput()
            ->withErrors(['error' => 'Failed to create loan: ' . $e->getMessage()]);
    }
}

    /**
     * Display the specified loan.
     */
    public function show(Loan $loan)
    {
        $loan->load(['employee', 'deductionType']);

        return view('loans.show', compact('loan'));
    }



    /**
     * Helper method to check if an addition type is loan-related
     */
    private function isLoanRelatedType($typeName)
    {
        $loanKeywords = ['loan', 'advance', 'cash advance', 'special loan', 'staff loan', 'salary advance'];
        $lowerTypeName = strtolower($typeName);

        foreach ($loanKeywords as $keyword) {
            if (strpos($lowerTypeName, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove the specified loan from storage.
     */
    public function destroy(Loan $loan)
    {
        try {
            DB::beginTransaction();

            // Delete the corresponding deduction record first
            $deduction = Deduction::where('loan_id', $loan->loan_id)->first();
            if ($deduction) {
                $deduction->delete();
            }

            // Delete the loan record
            $loan->delete();

            DB::commit();

            return redirect()->route('loans.index')
                ->with('success', 'Loan deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete loan: ' . $e->getMessage()]);
        }
    }
}

