<?php

namespace App\Http\Controllers;

use App\Models\DeductionType;
use Illuminate\Http\Request;

class DeductionTypeController extends Controller
{
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
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:deduction_types',
            'description' => 'nullable|string',
            'is_statutory' => 'required|boolean',
            'calculation_type' => 'required|in:fixed_amount,percentage',
            'rate_or_amount' => 'nullable|numeric',
        ]);

        DeductionType::create($request->all());

        return redirect()->route('deduction-types.index')
            ->with('success', 'Deduction type created successfully.');
    }

    public function edit(DeductionType $deductionType)
    {
        return view('deduction-types.edit', compact('deductionType'));
    }

    public function update(Request $request, DeductionType $deductionType)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:deduction_types,code,' . $deductionType->id,
            'description' => 'nullable|string',
            'is_statutory' => 'required|boolean',
            'calculation_type' => 'required|in:fixed_amount,percentage',
            'rate_or_amount' => 'nullable|numeric',
        ]);

        $deductionType->update($request->all());

        return redirect()->route('deduction-types.index')
            ->with('success', 'Deduction type updated successfully.');
    }

    public function destroy(DeductionType $deductionType)
    {
        $deductionType->delete();

        return redirect()->route('deduction-types.index')
            ->with('success', 'Deduction type deleted successfully.');
    }
}
