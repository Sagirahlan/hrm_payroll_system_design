<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->hasPermissionTo('manage_employees');
    }

    public function rules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'gender' => 'required|in:Male,Female',
            'date_of_birth' => 'required|date',
            'mobile_no' => 'required|string|max:15',
            'email' => 'nullable|email|max:255',
            'address' => 'required|string',
            'state_of_origin' => 'required|string|max:255',
            'lga' => 'required|string|max:255',
            'nationality' => 'required|string|max:255',
            'appointment_type' => 'required|string|max:255',
            'first_appointment_date' => 'required|date',
            'psn_no' => 'required|string|max:255|unique:employees',
            'department_id' => 'required|exists:departments,id',
            'rank' => 'required|string|max:255',
            'salary_scale' => 'required|string|max:255',
            'basic_salary' => 'required|numeric',
            'grade_level' => 'nullable|string|max:255',
            'step_level' => 'nullable|string|max:255',
            'next_promotion_date' => 'nullable|date',
            'retirement_date' => 'nullable|date',
            'status' => 'required|in:Active,Suspended,Retired,Deceased',
            'highest_certificate' => 'nullable|string|max:255',
            'grade_level_limit' => 'nullable|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'nin' => 'nullable|string|max:20',
            'biometric_data' => 'nullable|string',
        ];
    }
}