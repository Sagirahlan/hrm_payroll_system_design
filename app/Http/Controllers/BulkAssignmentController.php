<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeductionType;
use App\Models\AdditionType;
use App\Models\Employee;
use App\Models\Deduction;
use App\Models\Addition;
use App\Models\Department;
use App\Models\GradeLevel;

class BulkAssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:manage_payroll']);
    }

    public function create(Request $request)
    {
        $deductionTypes = DeductionType::where('is_statutory', false)->get();
        $additionTypes = AdditionType::where('is_statutory', false)->get();
        $departments = Department::orderBy('department_name')->get();
        $gradeLevels = GradeLevel::orderBy('name')->get();

        $employeesQuery = Employee::where('status', 'Active')->with(['department', 'gradeLevel']);

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

        $employees = $employeesQuery->paginate(20)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('bulk-assignment._employee_rows', compact('employees'))->render(),
                'next_page_url' => $employees->nextPageUrl(),
            ]);
        }

        return view('bulk-assignment.create', compact('deductionTypes', 'additionTypes', 'departments', 'gradeLevels', 'employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'adjustment_type' => 'required|in:addition,deduction',
            'type_id' => 'required|integer',
            'period' => 'required|in:OneTime,Monthly,Perpetual',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'amount' => 'required|numeric|min:0',
            'amount_type' => 'required|in:fixed,percentage',
            'employee_ids' => 'required_if:select_all_pages,0|array',
            'employee_ids.*' => 'exists:employees,employee_id',
        ]);

        $employees = collect();

        if ($request->input('select_all_pages') == '1') {
            $employeesQuery = Employee::where('status', 'Active')->with('gradeLevel');

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
            $employees = $employeesQuery->get();
        } else {
            $employees = Employee::whereIn('employee_id', $request->employee_ids)->with('gradeLevel')->get();
        }

        $type = $request->input('adjustment_type');
        $typeId = $request->input('type_id');
        $data = $request->only(['period', 'start_date', 'end_date', 'amount', 'amount_type']);

        foreach ($employees as $employee) {
            $finalAmount = $data['amount'];
            if ($data['amount_type'] === 'percentage') {
                if ($employee->gradeLevel && $employee->gradeLevel->basic_salary) {
                    $finalAmount = ($data['amount'] / 100) * $employee->gradeLevel->basic_salary;
                } else {
                    continue; // Skip employee if they don't have a grade level or basic salary
                }
            }

            if ($type === 'addition') {
                $additionType = AdditionType::find($typeId);
                if ($additionType) {
                    Addition::create([
                        'employee_id' => $employee->employee_id,
                        'addition_type' => $additionType->name,
                        'amount' => $finalAmount,
                        'addition_period' => $data['period'],
                        'start_date' => $data['start_date'],
                        'end_date' => $data['end_date'],
                    ]);
                }
            } else { // deduction
                $deductionType = DeductionType::find($typeId);
                if ($deductionType) {
                    Deduction::create([
                        'employee_id' => $employee->employee_id,
                        'deduction_type' => $deductionType->name,
                        'amount' => $finalAmount,
                        'deduction_period' => $data['period'],
                        'start_date' => $data['start_date'],
                        'end_date' => $data['end_date'],
                    ]);
                }
            }
        }

        return redirect()->route('bulk-assignment.create')
            ->with('success', 'Bulk assignment completed successfully for ' . $employees->count() . ' employees.');
    }
}
