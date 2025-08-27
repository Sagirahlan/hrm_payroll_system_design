<?php

namespace App\Http\Controllers;

use App\Models\SalaryScale;
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
                $q->where('scale_name', 'LIKE', '%' . $searchTerm . '%')
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
        $sortBy = $request->get('sort_by', 'scale_name');
        $sortOrder = $request->get('sort_order', 'asc');
        
        $validSortColumns = ['scale_name', 'basic_salary', 'grade_level', 'step_level'];
        if (in_array($sortBy, $validSortColumns)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $salaryScales = $query->paginate(10)->appends($request->query());

        // Get unique values for filter dropdowns
        $gradeLevels = SalaryScale::distinct()->pluck('grade_level')->sort();
        $stepLevels = SalaryScale::distinct()->pluck('step_level')->sort();

        return view('salary-scales.index', compact('salaryScales', 'gradeLevels', 'stepLevels'));
    }

   public function create()
{
    $gradeLevels = SalaryScale::distinct()->pluck('grade_level')->sort();
    $stepLevels = SalaryScale::distinct()->pluck('step_level')->sort();

    return view('salary-scales.create', compact('gradeLevels', 'stepLevels'));
}


    public function store(Request $request)
    {
        $request->validate([
            'scale_name' => 'required|string|max:50',
            'basic_salary' => 'required|numeric|min:0',
            'grade_level' => 'required|integer|min:1',
            'step_level' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        SalaryScale::create([
            'scale_name' => $request->scale_name,
            'basic_salary' => $request->basic_salary,
            'grade_level' => $request->grade_level,
            'step_level' => $request->step_level,
            'description' => $request->description,
        ]);

        return redirect()->route('salary-scales.index')
            ->with('success', 'Salary scale added successfully.');
    }

    public function edit($id)
{
    $salaryScale = SalaryScale::findOrFail($id);
    $gradeLevels = SalaryScale::distinct()->pluck('grade_level')->sort();
    $stepLevels = SalaryScale::distinct()->pluck('step_level')->sort();

    return view('salary-scales.edit', compact('salaryScale', 'gradeLevels', 'stepLevels'));
}


    public function update(Request $request, $id)
    {
        $request->validate([
            'scale_name' => 'required|string|max:50',
            'basic_salary' => 'required|numeric|min:0',
            'grade_level' => 'required|integer|min:1',
            'step_level' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $salaryScale = SalaryScale::findOrFail($id);
        $salaryScale->update([
            'scale_name' => $request->scale_name,
            'basic_salary' => $request->basic_salary,
            'grade_level' => $request->grade_level,
            'step_level' => $request->step_level,
            'description' => $request->description,
        ]);

        return redirect()->route('salary-scales.index')
            ->with('success', 'Salary scale updated successfully.');
    }

    public function destroy($id)
    {
        $salaryScale = SalaryScale::findOrFail($id);
        if ($salaryScale->employees()->count() > 0) {
            return redirect()->route('salary-scales.index')
                ->with('error', 'Cannot delete salary scale with assigned employees.');
        }
        $salaryScale->delete();

        return redirect()->route('salary-scales.index')
            ->with('success', 'Salary scale deleted successfully.');
    }
}