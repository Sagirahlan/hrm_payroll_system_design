<?php

namespace App\Http\Controllers;

use App\Models\DeductionType;
use Illuminate\Http\Request;
use App\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;

class DeductionTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(['permission:view_deduction_types'], ['only' => ['index']]);
        $this->middleware(['permission:create_deduction_types'], ['only' => ['create', 'store']]);
        $this->middleware(['permission:edit_deduction_types'], ['only' => ['edit', 'update']]);
        $this->middleware(['permission:delete_deduction_types'], ['only' => ['destroy']]);
    }
    public function index()
    {
        $deductionTypes = DeductionType::all();
        return view('deduction-types.index', compact('deductionTypes'));
    }

    public function create()
    {
        return view('deduction-types.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:deduction_types',
            'description' => 'nullable|string',
            'is_statutory' => 'required|boolean',
        ];

        // Add conditional validation rules based on is_statutory value
        if ($request->is_statutory == 1) {
            $rules['calculation_type'] = 'required|in:fixed_amount,percentage';
            $rules['rate_or_amount'] = 'required|numeric';
        }

        $request->validate($rules);

        $deductionType = DeductionType::create($request->all());

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'created',
            'description' => "Created deduction type: {$deductionType->name}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'DeductionType', 'entity_id' => $deductionType->id]),
        ]);

        return redirect()->route('deduction-types.index')
            ->with('success', 'Deduction type created successfully.');
    }

    public function edit(DeductionType $deductionType)
    {
        return view('deduction-types.edit', compact('deductionType'));
    }

    public function update(Request $request, DeductionType $deductionType)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:deduction_types,code,' . $deductionType->id,
            'description' => 'nullable|string',
            'is_statutory' => 'required|boolean',
        ];

        // Add conditional validation rules based on is_statutory value
        if ($request->is_statutory == 1) {
            $rules['calculation_type'] = 'required|in:fixed_amount,percentage';
            $rules['rate_or_amount'] = 'required|numeric';
        }

        $request->validate($rules);

        $deductionType->update($request->all());

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'description' => "Updated deduction type: {$deductionType->name}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'DeductionType', 'entity_id' => $deductionType->id]),
        ]);

        return redirect()->route('deduction-types.index')
            ->with('success', 'Deduction type updated successfully.');
    }

    public function destroy(DeductionType $deductionType)
    {
        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'description' => "Deleted deduction type: {$deductionType->name}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'DeductionType', 'entity_id' => $deductionType->id]),
        ]);

        $deductionType->delete();

        return redirect()->route('deduction-types.index')
            ->with('success', 'Deduction type deleted successfully.');
    }
}
