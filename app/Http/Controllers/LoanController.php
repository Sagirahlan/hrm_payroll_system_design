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
        $step = $employee->step;
        
        if ($step && $step->basic_salary) {
            return response()->json([
                'basic_salary' => $step->basic_salary
            ]);
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
            'monthly_percentage' => 'nullable|numeric|min:0|max:100|required_without_all:monthly_deduction,loan_duration_months',
            'monthly_deduction' => 'nullable|numeric|min:0|required_without_all:monthly_percentage,loan_duration_months',
            'loan_duration_months' => 'nullable|integer|min:1|required_without_all:monthly_percentage,monthly_deduction',
            'start_date' => 'required|date',
            'description' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $addition = Addition::find($request->addition_id);
            $employee = Employee::find($request->employee_id);
            $step = $employee->step;

            // Validate that the employee has a salary if we need to calculate percentage
            if (!$step || !$step->basic_salary) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['employee_id' => 'Employee does not have a valid salary for percentage calculation.']);
            }

            // Determine the monthly deduction based on the input provided
            // Check in order of precedence to avoid conflicts when multiple fields are submitted
            if ($request->filled('loan_duration_months')) {
                // If number of months is provided, calculate monthly deduction and percentage
                $totalMonths = $request->loan_duration_months;
                $monthlyDeduction = $request->principal_amount / $totalMonths;
                $monthlyPercentage = ($monthlyDeduction / $step->basic_salary) * 100;
            } elseif ($request->filled('monthly_percentage')) {
                // If monthly percentage is provided, calculate monthly deduction based on employee's salary
                $monthlyDeduction = ($request->monthly_percentage / 100) * $step->basic_salary;
                $totalMonths = ceil($request->principal_amount / $monthlyDeduction);
                $monthlyPercentage = $request->monthly_percentage;
            } elseif ($request->filled('monthly_deduction')) {
                // If monthly deduction is provided directly
                $monthlyDeduction = $request->monthly_deduction;
                $totalMonths = ceil($request->principal_amount / $request->monthly_deduction);
                $monthlyPercentage = ($monthlyDeduction / $step->basic_salary) * 100;
            } else {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['error' => 'Please provide either monthly percentage, monthly deduction amount, or loan duration in months.']);
            }

            // Create the loan record
            $loan = Loan::create([
                'employee_id' => $request->employee_id,
                'deduction_type_id' => $request->deduction_type_id,
                'loan_type' => $addition->additionType->name,
                'principal_amount' => $request->principal_amount,
                'monthly_deduction' => $monthlyDeduction,
                'total_months' => (int)$totalMonths,
                'remaining_months' => (int)$totalMonths,
                'monthly_percentage' => $monthlyPercentage,
                'start_date' => $request->start_date,
                'end_date' => now()->addMonths((int)$totalMonths)->format('Y-m-d'),
                'remaining_balance' => $request->principal_amount,
                'status' => 'active',
                'description' => $request->description,
            ]);

          

            // Create a deduction record
            Deduction::create([
                'employee_id' => $loan->employee_id,
                'deduction_type_id' => $loan->deduction_type_id,
                'deduction_type' => $loan->loan_type,
                'amount' => $loan->monthly_deduction,
                'deduction_period' => 'monthly',
                'start_date' => $loan->start_date,
                'end_date' => $loan->end_date,
            ]);

            DB::commit();

            return redirect()->route('loans.index')
                ->with('success', 'Loan created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            
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