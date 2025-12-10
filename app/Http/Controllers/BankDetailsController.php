<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Bank;
use App\Models\BankList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BankDetailsController extends Controller
{
    public function index()
    {
        $employees = Employee::with(['bank', 'department'])->get();
        $banks = BankList::where('is_active', true)->orderBy('bank_name')->get();

        return view('bank-details.index', compact('employees', 'banks'));
    }

    public function show($employeeId)
    {
        $employee = Employee::with(['bank', 'department'])->findOrFail($employeeId);
        $banks = BankList::where('is_active', true)->orderBy('bank_name')->get();
        $currentBankDetails = $employee->bank;

        return view('bank-details.show', compact('employee', 'banks', 'currentBankDetails'));
    }

    public function update(Request $request, $employeeId)
    {
        $request->validate([
            'bank_name' => 'required|string|max:100',
            'bank_code' => 'required|string|max:20',
            'account_name' => 'required|string|max:255',
            'account_no' => 'required|string|max:50',
        ]);

        $employee = Employee::findOrFail($employeeId);

        try {
            // Get current bank details for previous_data
            $currentBank = $employee->bank;
            $previousData = [];
            
            if ($currentBank) {
                $previousData = [
                    'bank_name' => $currentBank->bank_name,
                    'bank_code' => $currentBank->bank_code,
                    'account_name' => $currentBank->account_name,
                    'account_no' => $currentBank->account_no,
                ];
            }

            // Prepare new data
            $newData = [
                'bank_name' => $request->bank_name,
                'bank_code' => $request->bank_code,
                'account_name' => $request->account_name,
                'account_no' => $request->account_no,
            ];

            // Create pending change request
            \App\Models\PendingEmployeeChange::create([
                'employee_id' => $employee->employee_id,
                'requested_by' => Auth::id(),
                'change_type' => 'update',
                'data' => $newData,
                'previous_data' => $previousData,
                'reason' => 'Bank details update request',
                'status' => 'pending',
            ]);

            return redirect()->route('bank-details.show', $employeeId)
                ->with('success', 'Bank details update request submitted for approval.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error submitting bank details update: ' . $e->getMessage());
        }
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        $employees = Employee::with(['bank', 'department'])
            ->where(function($q) use ($query) {
                $q->where('first_name', 'LIKE', "%{$query}%")
                  ->orWhere('surname', 'LIKE', "%{$query}%")
                  ->orWhere('middle_name', 'LIKE', "%{$query}%")
                  ->orWhere('employee_id', 'LIKE', "%{$query}%")
                  ->orWhere('staff_no', 'LIKE', "%{$query}%");
            })
            ->get();

        $banks = BankList::where('is_active', true)->orderBy('bank_name')->get();

        return view('bank-details.index', compact('employees', 'banks'));
    }
}