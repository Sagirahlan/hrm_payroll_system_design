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

        $employees = $employeesQuery->paginate(10)->withQueryString();

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
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,employee_id',
        ]);

        $employees = Employee::whereIn('employee_id', $request->employee_ids)->get();

        $type = $request->input('adjustment_type');
        $typeId = $request->input('type_id');
        $data = $request->only(['period', 'start_date', 'end_date', 'amount']);

        foreach ($employees as $employee) {
            if ($type === 'addition') {
                Addition::create([
                    'employee_id' => $employee->employee_id,
                    'addition_type_id' => $typeId,
                    'amount' => $data['amount'],
                    'period' => $data['period'],
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                ]);
            } else { // deduction
                Deduction::create([
                    'employee_id' => $employee->employee_id,
                    'deduction_type_id' => $typeId,
                    'amount' => $data['amount'],
                    'period' => $data['period'],
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                ]);
            }
        }

        return redirect()->route('bulk-assignment.create')
            ->with('success', 'Bulk assignment completed successfully for ' . $employees->count() . ' employees.');
    }
}
