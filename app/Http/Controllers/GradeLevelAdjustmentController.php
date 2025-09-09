<?php

namespace App\Http\Controllers;

use App\Models\GradeLevel;
use App\Models\DeductionType;
use App\Models\AdditionType;
use Illuminate\Http\Request;

class GradeLevelAdjustmentController extends Controller
{
    public function index(GradeLevel $gradeLevel)
    {
        $deductionTypes = DeductionType::all();
        $additionTypes = AdditionType::all();
        return view('grade-level-adjustments.index', compact('gradeLevel', 'deductionTypes', 'additionTypes'));
    }

    public function store(Request $request, GradeLevel $gradeLevel)
    {
        $request->validate([
            'adjustment_type' => 'required|in:deduction,addition',
            'adjustment_id' => 'required|integer',
            'percentage' => 'required|numeric|min:0|max:100',
        ]);

        $adjustment_type = $request->input('adjustment_type');
        $adjustment_id = $request->input('adjustment_id');
        $percentage = $request->input('percentage');

        if ($adjustment_type === 'deduction') {
            $adjustable = DeductionType::findOrFail($adjustment_id);
            $gradeLevel->deductionTypes()->attach($adjustable->id, ['percentage' => $percentage]);
        } elseif ($adjustment_type === 'addition') {
            $adjustable = AdditionType::findOrFail($adjustment_id);
            $gradeLevel->additionTypes()->attach($adjustable->id, ['percentage' => $percentage]);
        }

        return redirect()->route('grade-levels.adjustments.index', $gradeLevel)
            ->with('success', 'Adjustment added successfully.');
    }

    public function destroy(GradeLevel $gradeLevel, $adjustmentId)
    {
        // This is a bit tricky because we have a polymorphic relationship.
        // We need to figure out if the adjustment is a deduction or an addition.
        // A simpler way is to just detach from both.
        $gradeLevel->deductionTypes()->detach($adjustmentId);
        $gradeLevel->additionTypes()->detach($adjustmentId);

        return redirect()->route('grade-levels.adjustments.index', $gradeLevel)
            ->with('success', 'Adjustment removed successfully.');
    }
}
