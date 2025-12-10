<?php

namespace App\Http\Controllers;

use App\Models\Pensioner;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PensionerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pensioners = Pensioner::with('employee')->orderBy('created_at', 'desc')->paginate(15);
        return view('pensioners.index', compact('pensioners'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::all(); // Get employees who could become pensioners
        return view('pensioners.create', compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'pension_start_date' => 'required|date',
            'pension_amount' => 'required|numeric|min:0',
            'status' => 'required|string|in:active,inactive,terminated',
            'rsa_balance_at_retirement' => 'nullable|numeric|min:0',
            'lump_sum_amount' => 'nullable|numeric|min:0',
            'pension_type' => 'nullable|string|in:PW,Annuity',
            'expected_lifespan_months' => 'nullable|integer|min:0',
        ]);

        $pensioner = Pensioner::create($request->all());

        return redirect()->route('pensioners.index')
            ->with('success', 'Pensioner created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $pensioner = Pensioner::with('employee')->findOrFail($id);
        return view('pensioners.show', compact('pensioner'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $pensioner = Pensioner::findOrFail($id);
        $employees = Employee::all();
        return view('pensioners.edit', compact('pensioner', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'pension_start_date' => 'required|date',
            'pension_amount' => 'required|numeric|min:0',
            'status' => 'required|string|in:active,inactive,terminated',
            'rsa_balance_at_retirement' => 'nullable|numeric|min:0',
            'lump_sum_amount' => 'nullable|numeric|min:0',
            'pension_type' => 'nullable|string|in:PW,Annuity',
            'expected_lifespan_months' => 'nullable|integer|min:0',
        ]);

        $pensioner = Pensioner::findOrFail($id);
        $pensioner->update($request->all());

        return redirect()->route('pensioners.index')
            ->with('success', 'Pensioner updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $pensioner = Pensioner::findOrFail($id);
        $pensioner->delete();

        return redirect()->route('pensioners.index')
            ->with('success', 'Pensioner deleted successfully.');
    }

    /**
     * Update pensioner status
     */
    public function updateStatus(Request $request, $pensioner_id)
    {
        $request->validate([
            'status' => 'required|string|in:active,inactive,terminated'
        ]);

        $pensioner = Pensioner::findOrFail($pensioner_id);
        $pensioner->status = $request->status;
        $pensioner->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully'
        ]);
    }

    /**
     * Track pensioner payment
     */
    public function trackPayment(Request $request, $pensioner_id)
    {
        $pensioner = Pensioner::findOrFail($pensioner_id);

        // Payment tracking would typically be handled by the Payroll/Payment system
        // This could involve creating payment records or updating payment status

        return response()->json([
            'success' => true,
            'message' => 'Payment tracked successfully'
        ]);
    }

    /**
     * Get pensioner payment history
     */
    public function paymentHistory($pensioner_id)
    {
        $pensioner = Pensioner::with('employee')->findOrFail($pensioner_id);

        // This would fetch payment history from the Payroll/Payment system
        // Currently returning a placeholder view
        return view('pensioners.payment-history', compact('pensioner'));
    }
}