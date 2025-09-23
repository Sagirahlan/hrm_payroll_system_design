<?php

namespace App\Http\Controllers;

use App\Models\SalaryScale;
use App\Models\GradeLevel;
use App\Models\Step;
use Illuminate\Http\Request;

class SalaryScaleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:manage_payroll']);
    }


    public function index(Request $request)
    {
        $query = SalaryScale::query();

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('acronym', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('full_name', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'acronym');
        $sortOrder = $request->get('sort_order', 'asc');
        
        $validSortColumns = ['acronym', 'full_name'];
        if (in_array($sortBy, $validSortColumns)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $salaryScales = $query->paginate(10)->appends($request->query());

        return view('salary-scales.index', compact('salaryScales'));
    }

    public function create()
    {
        return view('salary-scales.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'acronym' => 'required|string|max:10|unique:salary_scales',
            'full_name' => 'required|string|max:100',
            'sector_coverage' => 'required|string',
            'max_retirement_age' => 'required|integer|min:1',
            'max_years_of_service' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        SalaryScale::create($request->all());

        return redirect()->route('salary-scales.index')
            ->with('success', 'Salary scale added successfully.');
    }

    public function edit($id)
    {
        $salaryScale = SalaryScale::findOrFail($id);
        return view('salary-scales.edit', compact('salaryScale'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'acronym' => 'required|string|max:10|unique:salary_scales,acronym,' . $id,
            'full_name' => 'required|string|max:100',
            'sector_coverage' => 'required|string',
            'max_retirement_age' => 'required|integer|min:1',
            'max_years_of_service' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $salaryScale = SalaryScale::findOrFail($id);
        $salaryScale->update($request->all());

        return redirect()->route('salary-scales.index')
            ->with('success', 'Salary scale updated successfully.');
    }

    public function destroy($id)
    {
        $salaryScale = SalaryScale::findOrFail($id);
        if ($salaryScale->gradeLevels()->count() > 0) {
            return redirect()->route('salary-scales.index')
                ->with('error', 'Cannot delete salary scale with assigned grade levels.');
        }
        $salaryScale->delete();

        return redirect()->route('salary-scales.index')
            ->with('success', 'Salary scale deleted successfully.');
    }

    public function showGradeLevels(Request $request, $salaryScaleId)
    {
        $salaryScale = SalaryScale::findOrFail($salaryScaleId);
        $query = $salaryScale->gradeLevels()->with('steps');

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('description', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        if ($request->filled('filter_grade_level')) {
            $query->where('grade_level', $request->filter_grade_level);
        }

        $gradeLevels = $query->paginate(15)->appends($request->query());

        $distinctGradeLevels = $salaryScale->gradeLevels()->select('grade_level')->distinct()->get();

        return view('salary-scales.grade-levels', compact('salaryScale', 'gradeLevels', 'distinctGradeLevels'));
    }

    public function getStepsForGradeLevel($salaryScaleId, $gradeLevelName)
    {
        $gradeLevel = GradeLevel::where('salary_scale_id', $salaryScaleId)
                                 ->where('name', $gradeLevelName)
                                 ->first();

        if ($gradeLevel) {
            $steps = Step::where('grade_level_id', $gradeLevel->id)
                         ->orderBy('name', 'asc')
                         ->get();
            return response()->json($steps);
        }

        return response()->json([]);
    }
}
