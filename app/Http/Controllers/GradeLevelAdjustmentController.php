<?php

namespace App\Http\Controllers;

use App\Models\GradeLevel;
use App\Models\DeductionType;
use App\Models\AdditionType;
use Illuminate\Http\Request;
use App\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;

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
            $description = "Added deduction adjustment '{$adjustable->name}' to grade level '{$gradeLevel->name}'";
            $entityType = 'DeductionType';
        } elseif ($adjustment_type === 'addition') {
            $adjustable = AdditionType::findOrFail($adjustment_id);
            $gradeLevel->additionTypes()->attach($adjustable->id, ['percentage' => $percentage]);
            $description = "Added addition adjustment '{$adjustable->name}' to grade level '{$gradeLevel->name}'";
            $entityType = 'AdditionType';
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'created',
            'description' => $description,
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => $entityType, 'entity_id' => $adjustable->id, 'grade_level_id' => $gradeLevel->id]),
        ]);

        return redirect()->route('grade-levels.adjustments.index', $gradeLevel)
            ->with('success', 'Adjustment added successfully.');
    }

    public function destroy(GradeLevel $gradeLevel, $adjustmentId)
    {
        $detached_deduction = $gradeLevel->deductionTypes()->detach($adjustmentId);
        $detached_addition = $gradeLevel->additionTypes()->detach($adjustmentId);

        if ($detached_deduction) {
            $adjustable = DeductionType::find($adjustmentId);
            $description = "Removed deduction adjustment '{$adjustable->name}' from grade level '{$gradeLevel->name}'";
            $entityType = 'DeductionType';
            $entityId = $adjustable->id;
        } elseif ($detached_addition) {
            $adjustable = AdditionType::find($adjustmentId);
            $description = "Removed addition adjustment '{$adjustable->name}' from grade level '{$gradeLevel->name}'";
            $entityType = 'AdditionType';
            $entityId = $adjustable->id;
        } else {
            return redirect()->route('grade-levels.adjustments.index', $gradeLevel)
                ->with('success', 'Adjustment removed successfully.');
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'description' => $description,
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => $entityType, 'entity_id' => $entityId, 'grade_level_id' => $gradeLevel->id]),
        ]);

        return redirect()->route('grade-levels.adjustments.index', $gradeLevel)
            ->with('success', 'Adjustment removed successfully.');
    }
}
