<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePayrollRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->hasPermissionTo('manage_payroll');
    }

    public function rules()
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'payroll_date' => 'required|date',
            'basic_salary' => 'required|numeric',
            'gross_salary' => 'required|numeric',
            'net_salary' => 'required|numeric',
            'status' => 'required|in:Pending,Approved,Paid',
            'deductions.*.description' => 'required|string|max:255',
            'deductions.*.amount' => 'required|numeric',
            'deductions.*.type' => 'required|in:One-time,Monthly,Perpetual',
            'additions.*.description' => 'required|string|max:255',
            'additions.*.amount' => 'required|numeric',
            'additions.*.type' => 'required|in:One-time,Monthly,Perpetual',
        ];
    }
}