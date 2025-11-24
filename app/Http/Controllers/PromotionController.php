<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PromotionHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PromotionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(['permission:view_promotions'], ['only' => ['index', 'show']]);
        $this->middleware(['permission:create_promotions'], ['only' => ['create', 'store', 'searchEmployees', 'getEmployeeDetails']]);
        $this->middleware(['permission:delete_promotions'], ['only' => ['destroy']]);
    }

    /**
     * Display a listing of promotions and demotions for all employees.
     */
    public function index(Request $request)
    {
        $query = PromotionHistory::with('employee', 'creator')->orderBy('created_at', 'desc');

        // Filter by promotion type
        if ($request->filled('type')) {
            $query->where('promotion_type', $request->type);
        }

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by status - default to 'approved' if not specified
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Only show approved promotions by default
            $query->where('status', 'approved');
        }

        // Filter by promotion date
        if ($request->filled('promotion_date')) {
            $query->whereDate('promotion_date', $request->promotion_date);
        }

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->whereHas('employee', function($employeeQuery) use ($searchTerm) {
                    $employeeQuery->where('first_name', 'like', "%{$searchTerm}%")
                                ->orWhere('surname', 'like', "%{$searchTerm}%")
                                ->orWhere('employee_id', 'like', "%{$searchTerm}%");
                })
                ->orWhere('approving_authority', 'like', "%{$searchTerm}%")
                ->orWhere('reason', 'like', "%{$searchTerm}%");
            });
        }

        $promotions = $query->paginate(15);

        // Get all employees for the filter dropdown
        $employees = Employee::where('appointment_type_id', 1) // Assuming 1 is for permanent employees
            ->orderBy('surname', 'asc')
            ->get()
            ->map(function ($employee) {
                // Add a computed property for the full name
                $employee->full_name = trim($employee->first_name . ' ' . $employee->middle_name . ' ' . $employee->surname);
                return $employee;
            });

        return view('promotions.index', compact('promotions', 'employees'));
    }

    /**
     * Show the form for creating a new promotion or demotion.
     */
    public function create(Request $request)
    {
        $query = Employee::where('appointment_type_id', 1) // Permanent employees only
            ->where('status', 'Active')
            ->with(['gradeLevel', 'step', 'department', 'appointmentType']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereRaw("LOWER(CONCAT_WS(' ', first_name, middle_name, surname)) LIKE ?", ["%" . strtolower($search) . "%"])
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        // Filter by department
        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        // Filter by status (though we're already filtering by Active)
        if ($request->filled('employee_status')) {
            $query->where('status', $request->employee_status);
        }

        $employees = $query->paginate(10);

        // Get departments for filter dropdown
        $departments = \App\Models\Department::all();

        return view('promotions.create', compact('employees', 'departments'));
    }

    /**
     * Search employees for promotion selection
     */
    public function searchEmployees(Request $request)
    {
        $query = Employee::where('appointment_type_id', 1) // Permanent employees only
            ->where('status', 'Active')
            ->with(['gradeLevel', 'step', 'department', 'appointmentType']);

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                  ->orWhere('middle_name', 'like', "%{$searchTerm}%")
                  ->orWhere('surname', 'like', "%{$searchTerm}%")
                  ->orWhere('employee_id', 'like', "%{$searchTerm}%");
            });
        }

        $employees = $query->orderBy('surname', 'asc')
            ->limit(50) // Limit results for performance
            ->get()
            ->map(function ($employee) {
                return [
                    'employee_id' => $employee->employee_id,
                    'full_name' => trim($employee->first_name . ' ' . $employee->middle_name . ' ' . $employee->surname),
                    'grade_level' => optional($employee->gradeLevel)->name,
                    'current_grade' => optional($employee->gradeLevel)->name,
                    'current_step' => optional($employee->step)->name,
                ];
            });

        return response()->json($employees);
    }

    /**
     * Get detailed employee information for promotion creation
     */
    public function getEmployeeDetails($employeeId)
    {
        $employee = Employee::with([
            'gradeLevel.salaryScale', // Explicitly load the salary scale relationship
            'step',
            'department',
            'appointmentType',
            'promotionHistory'
        ])->findOrFail($employeeId);

        // Prepare grade level data with salary scale
        $gradeLevelData = null;
        if ($employee->gradeLevel) {
            $gradeLevelData = [
                'id' => $employee->gradeLevel->id,
                'name' => $employee->gradeLevel->name,
                'grade_level' => $employee->gradeLevel->grade_level,
                'description' => $employee->gradeLevel->description,
                'salary_scale' => $employee->gradeLevel->salaryScale ? [
                    'id' => $employee->gradeLevel->salaryScale->id,
                    'acronym' => $employee->gradeLevel->salaryScale->acronym,
                    'full_name' => $employee->gradeLevel->salaryScale->full_name,
                    'description' => $employee->gradeLevel->salaryScale->description,
                ] : null
            ];
        }

        return response()->json(['data' => [
            'employee_id' => $employee->employee_id,
            'first_name' => $employee->first_name,
            'middle_name' => $employee->middle_name,
            'surname' => $employee->surname,
            'full_name' => trim($employee->first_name . ' ' . $employee->middle_name . ' ' . $employee->surname),
            'department' => $employee->department,
            'grade_level' => $gradeLevelData,
            'step' => $employee->step,
            'appointmentType' => $employee->appointmentType,
            'status' => $employee->status,
            'last_promotion_date' => optional($employee->getLastPromotionAttribute())->promotion_date,
            'date_of_first_appointment' => $employee->date_of_first_appointment,
            'years_of_service' => $employee->getYearsOfServiceAttribute(),
        ]]);
    }

    /**
     * Store a newly created promotion or demotion in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'promotion_type' => 'required|in:promotion,demotion',
            'previous_grade_level' => 'required|string|max:50',
            'new_grade_level' => 'required|string|max:50',
            'previous_step' => 'nullable|string|max:50',
            'new_step' => 'nullable|string|max:50',
            'promotion_date' => 'required|date',
            'effective_date' => 'required|date|after_or_equal:promotion_date',
            'approving_authority' => 'nullable|string|max:255',
            'reason' => 'required|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Always create with pending status
            $promotion = PromotionHistory::create([
                'employee_id' => $request->employee_id,
                'promotion_type' => $request->promotion_type,
                'previous_grade_level' => $request->previous_grade_level,
                'new_grade_level' => $request->new_grade_level,
                'previous_step' => $request->previous_step,
                'new_step' => $request->new_step,
                'promotion_date' => $request->promotion_date,
                'effective_date' => $request->effective_date,
                'approving_authority' => $request->approving_authority,
                'reason' => $request->reason,
                'status' => 'pending', // Always set to pending initially
                'created_by' => Auth::user()->user_id,
            ]);

            DB::commit();

            // Redirect to pending changes page
            return redirect()->route('pending-changes.index')
                ->with('success', ucfirst($request->promotion_type) . ' request submitted successfully and is pending approval.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'An error occurred while creating the ' . $request->promotion_type . ': ' . $e->getMessage());
        }
    }

    /**
     * Display the specified promotion or demotion.
     */
    public function show(PromotionHistory $promotion)
    {
        $promotion->load('employee', 'creator');

        return view('promotions.show', compact('promotion'));
    }

    /**
     * Show the form for editing the specified promotion or demotion.
     */
    public function edit(PromotionHistory $promotion)
    {
        $promotion->load('employee');
        $employees = Employee::where('appointment_type_id', 1) // Permanent employees only
            ->where('status', 'Active')
            ->with('gradeLevel', 'step')
            ->get();

        return view('promotions.edit', compact('promotion', 'employees'));
    }

    /**
     * Update the specified promotion or demotion in storage.
     */
    public function update(Request $request, PromotionHistory $promotion)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'promotion_type' => 'required|in:promotion,demotion',
            'previous_grade_level' => 'required|string|max:50',
            'new_grade_level' => 'required|string|max:50',
            'previous_step' => 'nullable|string|max:50',
            'new_step' => 'nullable|string|max:50',
            'promotion_date' => 'required|date',
            'effective_date' => 'required|date|after_or_equal:promotion_date',
            'approving_authority' => 'nullable|string|max:255',
            'reason' => 'required|string|max:1000',
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
        ]);

        try {
            DB::beginTransaction();

            $promotion->update([
                'employee_id' => $request->employee_id,
                'promotion_type' => $request->promotion_type,
                'previous_grade_level' => $request->previous_grade_level,
                'new_grade_level' => $request->new_grade_level,
                'previous_step' => $request->previous_step,
                'new_step' => $request->new_step,
                'promotion_date' => $request->promotion_date,
                'effective_date' => $request->effective_date,
                'approving_authority' => $request->approving_authority,
                'reason' => $request->reason,
                'status' => $request->status,
            ]);

            // Update employee's grade level if status is approved
            if ($request->status === 'approved') {
                $employee = Employee::find($request->employee_id);
                $employee->update([
                    'grade_level_id' => $this->getGradeLevelIdByName($request->new_grade_level),
                    'step_id' => $this->getStepIdByName($request->new_step, $request->new_grade_level)
                ]);
            } else if ($promotion->wasChanged('status') && $promotion->status === 'approved') {
                // If status was changed to approved and it was previously not approved
                $employee = Employee::find($request->employee_id);
                $employee->update([
                    'grade_level_id' => $this->getGradeLevelIdByName($request->new_grade_level),
                    'step_id' => $this->getStepIdByName($request->new_step, $request->new_grade_level)
                ]);
            }

            DB::commit();

            return redirect()->route('promotions.show', $promotion->id)
                ->with('success', ucfirst($request->promotion_type) . ' record updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'An error occurred while updating the ' . $request->promotion_type . ': ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified promotion or demotion from storage.
     */
    public function destroy(PromotionHistory $promotion)
    {
        $promotionType = $promotion->promotion_type;
        $promotion->delete();

        return redirect()->route('promotions.index')
            ->with('success', ucfirst($promotionType) . ' record deleted successfully.');
    }

    /**
     * Approve the pending promotion or demotion
     */
    public function approve(PromotionHistory $promotion, Request $request)
    {
        $request->validate([
            'approval_notes' => 'nullable|string|max:1000'
        ]);

        $promotion->update([
            'status' => 'approved',
            'approval_notes' => $request->approval_notes
        ]);

        // Update employee's grade level and step
        $employee = $promotion->employee;
        $employee->update([
            'grade_level_id' => $this->getGradeLevelIdByName($promotion->new_grade_level),
            'step_id' => $this->getStepIdByName($promotion->new_step, $promotion->new_grade_level)
        ]);

        return redirect()->route('promotions.index')
            ->with('success', ucfirst($promotion->promotion_type) . ' approved successfully.');
    }

    /**
     * Reject the pending promotion or demotion
     */
    public function reject(PromotionHistory $promotion, Request $request)
    {
        $request->validate([
            'approval_notes' => 'required|string|max:1000'
        ]);

        $promotion->update([
            'status' => 'rejected',
            'approval_notes' => $request->approval_notes
        ]);

        return redirect()->route('promotions.index')
            ->with('success', ucfirst($promotion->promotion_type) . ' rejected.');
    }

    /**
     * Get the GradeLevel ID by name
     */
    private function getGradeLevelIdByName($name)
    {
        $gradeLevel = \App\Models\GradeLevel::where('name', $name)->first();
        return $gradeLevel ? $gradeLevel->id : null;
    }

    /**
     * Get the Step ID by name and grade level name
     */
    private function getStepIdByName($name, $gradeLevelName = null)
    {
        $query = \App\Models\Step::where('name', $name);

        if ($gradeLevelName) {
            $gradeLevel = \App\Models\GradeLevel::where('name', $gradeLevelName)->first();
            if ($gradeLevel) {
                $query->where('grade_level_id', $gradeLevel->id);
            }
        }

        $step = $query->first();
        return $step ? $step->id : null;
    }
}
