<?php

namespace App\Http\Controllers\SalaryScale;

use App\Http\Controllers\Controller;
use App\Models\SalaryScale;
use App\Models\GradeLevel;
use App\Models\Step;
use Illuminate\Http\Request;

class StepController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:manage_payroll']);
    }

    /**
     * Show the form for creating a new step
     */
    public function create($salaryScaleId, $gradeLevelId)
    {
        $salaryScale = SalaryScale::findOrFail($salaryScaleId);
        $gradeLevel = GradeLevel::where('salary_scale_id', $salaryScale->id)->findOrFail($gradeLevelId);
        
        return view('salary-scales.grade-levels.steps.create', compact('salaryScale', 'gradeLevel'));
    }

    /**
     * Store a newly created step in storage
     */
    public function store(Request $request, $salaryScaleId, $gradeLevelId)
    {
        $salaryScale = SalaryScale::findOrFail($salaryScaleId);
        $gradeLevel = GradeLevel::where('salary_scale_id', $salaryScale->id)->findOrFail($gradeLevelId);

        $request->validate([
            'name' => 'required|string|max:50|unique:steps,name,NULL,id,grade_level_id,' . $gradeLevel->id,
            'basic_salary' => 'required|numeric|min:0',
        ]);

        $step = new Step($request->all());
        $step->grade_level_id = $gradeLevel->id;
        $step->save();

        return redirect()->route('salary-scales.grade-levels.edit', [$salaryScale->id, $gradeLevel->id])
            ->with('success', 'Step added successfully.');
    }

    /**
     * Show the form for editing the specified step
     */
    public function edit($salaryScaleId, $gradeLevelId, $stepId)
    {
        $salaryScale = SalaryScale::findOrFail($salaryScaleId);
        $gradeLevel = GradeLevel::where('salary_scale_id', $salaryScale->id)->findOrFail($gradeLevelId);
        $step = Step::where('grade_level_id', $gradeLevel->id)->findOrFail($stepId);
        
        return view('salary-scales.grade-levels.steps.edit', compact('salaryScale', 'gradeLevel', 'step'));
    }

    /**
     * Update the specified step in storage
     */
    public function update(Request $request, $salaryScaleId, $gradeLevelId, $stepId)
    {
        $salaryScale = SalaryScale::findOrFail($salaryScaleId);
        $gradeLevel = GradeLevel::where('salary_scale_id', $salaryScale->id)->findOrFail($gradeLevelId);
        $step = Step::where('grade_level_id', $gradeLevel->id)->findOrFail($stepId);

        $request->validate([
            'name' => 'required|string|max:50|unique:steps,name,' . $step->id . ',id,grade_level_id,' . $gradeLevel->id,
            'basic_salary' => 'required|numeric|min:0',
        ]);

        $step->update($request->all());

        return redirect()->route('salary-scales.grade-levels.edit', [$salaryScale->id, $gradeLevel->id])
            ->with('success', 'Step updated successfully.');
    }

    /**
     * Remove the specified step from storage
     */
    public function destroy($salaryScaleId, $gradeLevelId, $stepId)
    {
        $salaryScale = SalaryScale::findOrFail($salaryScaleId);
        $gradeLevel = GradeLevel::where('salary_scale_id', $salaryScale->id)->findOrFail($gradeLevelId);
        $step = Step::where('grade_level_id', $gradeLevel->id)->findOrFail($stepId);
        
        $step->delete();

        return redirect()->route('salary-scales.grade-levels.edit', [$salaryScale->id, $gradeLevel->id])
            ->with('success', 'Step deleted successfully.');
    }
}