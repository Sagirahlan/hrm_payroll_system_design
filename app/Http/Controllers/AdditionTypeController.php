<?php

namespace App\Http\Controllers;

use App\Models\AdditionType;
use Illuminate\Http\Request;
use App\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;

class AdditionTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(['permission:view_addition_types'], ['only' => ['index']]);
        $this->middleware(['permission:create_addition_types'], ['only' => ['create', 'store']]);
        $this->middleware(['permission:edit_addition_types'], ['only' => ['edit', 'update']]);
        $this->middleware(['permission:delete_addition_types'], ['only' => ['destroy']]);
    }

    public function index()
    {
        $additionTypes = AdditionType::all();
        return view('addition-types.index', compact('additionTypes'));
    }

    public function create()
    {
        return view('addition-types.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:addition_types',
            'description' => 'nullable|string',
            'is_statutory' => 'required|boolean',
        ];

        // Add conditional validation rules based on is_statutory value
        if ($request->is_statutory == 1) {
            $rules['calculation_type'] = 'required|in:fixed_amount,percentage';
            $rules['rate_or_amount'] = 'required|numeric';
        }

        $request->validate($rules);

        $additionType = AdditionType::create($request->all());

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'created',
            'description' => "Created addition type: {$additionType->name}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'AdditionType', 'entity_id' => $additionType->id]),
        ]);

        return redirect()->route('addition-types.index')
            ->with('success', 'Addition type created successfully.');
    }

    public function edit(AdditionType $additionType)
    {
        return view('addition-types.edit', compact('additionType'));
    }

    public function update(Request $request, AdditionType $additionType)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:addition_types,code,' . $additionType->id,
            'description' => 'nullable|string',
            'is_statutory' => 'required|boolean',
        ];

        // Add conditional validation rules based on is_statutory value
        if ($request->is_statutory == 1) {
            $rules['calculation_type'] = 'required|in:fixed_amount,percentage';
            $rules['rate_or_amount'] = 'required|numeric';
        }

        $request->validate($rules);

        $additionType->update($request->all());

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'description' => "Updated addition type: {$additionType->name}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'AdditionType', 'entity_id' => $additionType->id]),
        ]);

        return redirect()->route('addition-types.index')
            ->with('success', 'Addition type updated successfully.');
    }

    public function destroy(AdditionType $additionType)
    {
        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'description' => "Deleted addition type: {$additionType->name}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'AdditionType', 'entity_id' => $additionType->id]),
        ]);

        $additionType->delete();

        return redirect()->route('addition-types.index')
            ->with('success', 'Addition type deleted successfully.');
    }
}
