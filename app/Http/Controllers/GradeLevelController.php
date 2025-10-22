<?php

namespace App\Http\Controllers;

use App\Models\GradeLevel;
use Illuminate\Http\Request;
use App\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;

class GradeLevelController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(['permission:view_grade_levels'], ['only' => ['index']]);
        $this->middleware(['permission:create_grade_levels'], ['only' => ['create', 'store']]);
        $this->middleware(['permission:edit_grade_levels'], ['only' => ['edit', 'update']]);
        $this->middleware(['permission:delete_grade_levels'], ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $query = GradeLevel::query();

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('description', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        // Filter by grade level
        if ($request->filled('grade_level')) {
            $query->where('grade_level', $request->grade_level);
        }

        // Filter by step level
        if ($request->filled('step_level')) {
            $query->where('step_level', $request->step_level);
        }

        // Filter by salary range
        if ($request->filled('min_salary')) {
            $query->where('basic_salary', '>=', $request->min_salary);
        }

        if ($request->filled('max_salary')) {
            $query->where('basic_salary', '<=', $request->max_salary);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        
        $validSortColumns = ['name', 'basic_salary', 'grade_level', 'step_level'];
        if (in_array($sortBy, $validSortColumns)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $gradeLevels = $query->paginate(10)->appends($request->query());

        // Get unique values for filter dropdowns
        $grades = GradeLevel::distinct()->pluck('grade_level')->sort();
        $steps = GradeLevel::distinct()->pluck('step_level')->sort();

        return view('grade-levels.index', compact('gradeLevels', 'grades', 'steps'));
    }

   public function create()
{
    $grades = GradeLevel::distinct()->pluck('grade_level')->sort();
    $steps = GradeLevel::distinct()->pluck('step_level')->sort();

    return view('grade-levels.create', compact('grades', 'steps'));
}


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'basic_salary' => 'required|numeric|min:0',
            'grade_level' => 'required|integer|min:1',
            'step_level' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $gradeLevel = GradeLevel::create([
            'name' => $request->name,
            'basic_salary' => $request->basic_salary,
            'grade_level' => $request->grade_level,
            'step_level' => $request->step_level,
            'description' => $request->description,
        ]);

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'created',
            'description' => "Created grade level: {$gradeLevel->name}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'GradeLevel', 'entity_id' => $gradeLevel->id]),
        ]);

        return redirect()->route('grade-levels.index')
            ->with('success', 'Grade level added successfully.');
    }

    public function edit($id)
{
    $gradeLevel = GradeLevel::findOrFail($id);
    $grades = GradeLevel::distinct()->pluck('grade_level')->sort();
    $steps = GradeLevel::distinct()->pluck('step_level')->sort();

    return view('grade-levels.edit', compact('gradeLevel', 'grades', 'steps'));
}


    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'basic_salary' => 'required|numeric|min:0',
            'grade_level' => 'required|integer|min:1',
            'step_level' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $gradeLevel = GradeLevel::findOrFail($id);
        $gradeLevel->update([
            'name' => $request->name,
            'basic_salary' => $request->basic_salary,
            'grade_level' => $request->grade_level,
            'step_level' => $request->step_level,
            'description' => $request->description,
        ]);

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'description' => "Updated grade level: {$gradeLevel->name}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'GradeLevel', 'entity_id' => $gradeLevel->id]),
        ]);

        return redirect()->route('grade-levels.index')
            ->with('success', 'Grade level updated successfully.');
    }

    public function destroy($id)
    {
        $gradeLevel = GradeLevel::findOrFail($id);
        if ($gradeLevel->employees()->count() > 0) {
            return redirect()->route('grade-levels.index')
                ->with('error', 'Cannot delete grade level with assigned employees.');
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'description' => "Deleted grade level: {$gradeLevel->name}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'GradeLevel', 'entity_id' => $gradeLevel->id]),
        ]);

        $gradeLevel->delete();

        return redirect()->route('grade-levels.index')
            ->with('success', 'Grade level deleted successfully.');
    }
}