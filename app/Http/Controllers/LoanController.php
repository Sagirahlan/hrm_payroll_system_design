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
        // Get employees who have at least one addition
        $employees = Employee::whereHas('additions')->get();
        $deductionTypes = DeductionType::all();
        
        return view('loans.create', compact('employees', 'deductionTypes'));
    }

    /**
     * Get additions for a specific employee.
     */
    public function getAdditionsForEmployee(Employee $employee)
    {
        $additions = $employee->additions()->with('additionType')->get();
        return response()->json($additions);
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
            'monthly_percentage' => 'nullable|numeric|min:0|max:100|required_without:monthly_deduction',
            'monthly_deduction' => 'nullable|numeric|min:0|required_without:monthly_percentage',
            'start_date' => 'required|date',
            'description' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $addition = Addition::find($request->addition_id);

            // If monthly percentage is provided, calculate monthly deduction based on employee's salary
            if ($request->monthly_percentage) {
                $employee = Employee::find($request->employee_id);
                $step = $employee->step;
                
                if ($step && $step->basic_salary) {
                    $monthlyDeduction = ($request->monthly_percentage / 100) * $step->basic_salary;
                    $totalMonths = ceil($request->principal_amount / $monthlyDeduction);
                } else {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['employee_id' => 'Employee does not have a valid salary for percentage calculation.']);
                }
            } else {
                // If monthly deduction is provided directly
                $monthlyDeduction = $request->monthly_deduction;
                $totalMonths = ceil($request->principal_amount / $request->monthly_deduction);
            }

            // Create the loan record
            $loan = Loan::create([
                'employee_id' => $request->employee_id,
                'deduction_type_id' => $request->deduction_type_id,
                'loan_type' => $addition->additionType->name,
                'principal_amount' => $request->principal_amount,
                'monthly_deduction' => $monthlyDeduction,
                'total_months' => $totalMonths,
                'remaining_months' => $totalMonths,
                'monthly_percentage' => $request->monthly_percentage,
                'start_date' => $request->start_date,
                'end_date' => now()->addMonths($totalMonths)->format('Y-m-d'),
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