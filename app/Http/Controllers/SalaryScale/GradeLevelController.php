<?php

namespace App\Http\Controllers\SalaryScale;

use App\Http\Controllers\Controller;
use App\Models\SalaryScale;
use App\Models\GradeLevel;
use Illuminate\Http\Request;

class GradeLevelController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:manage_payroll']);
    }

    public function create($salaryScaleId)
    {
        $salaryScale = SalaryScale::findOrFail($salaryScaleId);
        return view('salary-scales.grade-levels.create', compact('salaryScale'));
    }

    public function store(Request $request, $salaryScaleId)
    {
        $salaryScale = SalaryScale::findOrFail($salaryScaleId);

        $request->validate([
            'name' => 'required|string|max:50',
            'basic_salary' => 'required|numeric|min:0',
            'grade_level' => 'required|integer|min:1',
            'step_level' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $gradeLevel = new GradeLevel($request->all());
        $gradeLevel->salary_scale_id = $salaryScale->id;
        $gradeLevel->save();

        return redirect()->route('salary-scales.grade-levels', $salaryScale->id)
            ->with('success', 'Grade level added successfully.');
    }

    public function edit($salaryScaleId, $gradeLevelId)
    {
        $salaryScale = SalaryScale::findOrFail($salaryScaleId);
        $gradeLevel = GradeLevel::where('salary_scale_id', $salaryScale->id)->findOrFail($gradeLevelId);
        return view('salary-scales.grade-levels.edit', compact('salaryScale', 'gradeLevel'));
    }

    public function update(Request $request, $salaryScaleId, $gradeLevelId)
    {
        $salaryScale = SalaryScale::findOrFail($salaryScaleId);
        $gradeLevel = GradeLevel::where('salary_scale_id', $salaryScale->id)->findOrFail($gradeLevelId);

        $request->validate([
            'name' => 'required|string|max:50',
            'basic_salary' => 'required|numeric|min:0',
            'grade_level' => 'required|integer|min:1',
            'step_level' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $gradeLevel->update($request->all());

        return redirect()->route('salary-scales.grade-levels', $salaryScale->id)
            ->with('success', 'Grade level updated successfully.');
    }

    public function destroy($salaryScaleId, $gradeLevelId)
    {
        $salaryScale = SalaryScale::findOrFail($salaryScaleId);
        $gradeLevel = GradeLevel::where('salary_scale_id', $salaryScale->id)->findOrFail($gradeLevelId);
        
        if ($gradeLevel->employees()->count() > 0) {
            return redirect()->route('salary-scales.grade-levels', $salaryScale->id)
                ->with('error', 'Cannot delete grade level with assigned employees.');
        }
        
        $gradeLevel->delete();

        return redirect()->route('salary-scales.grade-levels', $salaryScale->id)
            ->with('success', 'Grade level deleted successfully.');
    }
}
