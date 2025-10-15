<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Employee;
use App\Models\Deduction;
use App\Models\DeductionType;
use App\Models\Addition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
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
        // Get all employees who have additions
        $employees = Employee::whereHas('additions')->get();
        
        // Filter employees to only include those who have at least one addition that doesn't have an active loan
        $filteredEmployees = $employees->filter(function ($employee) {
            $additions = $employee->additions;
            foreach ($additions as $addition) {
                if (!$employee->hasActiveLoanForAdditionType($addition->additionType->name)) {
                    return true; // This employee has at least one addition without an active loan
                }
            }
            return false; // All additions for this employee have active loans
        });
        
        $deductionTypes = DeductionType::all();
        
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
            if (!$employee->hasActiveLoanForAdditionType($addition->additionType->name)) {
                $availableAdditions->push($addition);
            }
        }
        
        return response()->json($availableAdditions);
    }

    /**
     * Get employee salary information.
     */
    public function getEmployeeSalary(Employee $employee)
    {
        // Check if employee is a contract employee using the new method
        $isContractEmployee = $employee->isContractEmployee();
        
        if ($isContractEmployee) {
            // For contract employees, use the amount field
            if ($employee->amount) {
                return response()->json([
                    'basic_salary' => $employee->amount
                ]);
            }
        } else {
            // For permanent/temporary employees, use grade level step
            $step = $employee->step;
            
            if ($step && $step->basic_salary) {
                return response()->json([
                    'basic_salary' => $step->basic_salary
                ]);
            }
        }
        
        return response()->json([
            'basic_salary' => null,
            'error' => 'Employee does not have a valid salary configured'
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
        'interest_rate' => 'nullable|numeric|min:0|max:100',
        'monthly_percentage' => 'nullable|numeric|min:0|max:100',
        'monthly_deduction' => 'nullable|numeric|min:0',
        'loan_duration_months' => 'nullable|integer|min:1',
        'start_date' => 'required|date',
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
        
        // Determine if employee is contract or regular using the new method
        $isContractEmployee = $employee->isContractEmployee();
        $salary = 0;
        
        if ($isContractEmployee) {
            // For contract employees, use the amount field
            $salary = $employee->amount;
        } else {
            // For regular employees, use step basic salary
            $step = $employee->step;
            $salary = $step ? $step->basic_salary : 0;
        }

        // Validate that the employee has a salary
        if (!$salary) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['employee_id' => 'Employee does not have a valid salary for percentage calculation.']);
        }

        // CRITICAL: Principal amount is FIXED - never modify it
        $principalAmount = (float) $request->principal_amount;
        
        // Get interest rate from request (default to 0 if not provided)
        $interestRate = (float) ($request->interest_rate ?? 0);
        
        // Calculate interest and total repayment
        $totalInterest = ($principalAmount * $interestRate) / 100;
        $totalRepayment = $principalAmount + $totalInterest;
        
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

       // Calculate end date from start date + total months
        // End date should be the last day of the final month
        // For example, if start date is Nov 1, 2025 and total months is 3:
        // Month 1: November 2025, Month 2: December 2025, Month 3: January 2026
        // End date should be January 31, 2026 (last day of the 3rd month)
        $startDate = \Carbon\Carbon::parse($request->start_date);
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
            'start_date' => $request->start_date,
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
            'start_date' => $loan->start_date,
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