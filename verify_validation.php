<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Employee;
use App\Models\AppointmentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

echo "--- Verifying Employee Edit Validation for 'Pensioners' --- \n";

// Mock a request for editing Employee 799 (Musa Aliyu)
$pensionerType = AppointmentType::where('name', 'Pensioners')->first();
if (!$pensionerType) {
    echo "ERROR: 'Pensioners' appointment type not found.\n";
    exit(1);
}

$data = [
    'first_name' => 'Musa',
    'surname' => 'Aliyu',
    'gender' => 'Male',
    'date_of_birth' => '1950-01-01',
    'state_id' => 1,
    'lga_id' => 1,
    'nationality' => 'Nigeria',
    'staff_no' => 'MA-12345',
    'mobile_no' => '08012345678',
    'pay_point' => 'Main',
    'address' => 'Test Address',
    'date_of_first_appointment' => '1970-01-01',
    'appointment_type_id' => $pensionerType->id,
    'status' => 'Retired',
    'department_id' => 1,
    'kin_name' => 'Next of Kin',
    'kin_relationship' => 'Son',
    'kin_mobile_no' => '08087654321',
    'kin_address' => 'Kin Address',
    'bank_name' => 'Test Bank',
    'bank_code' => '011',
    'account_name' => 'Musa Aliyu',
    'account_no' => '1234567890',
    // 'expected_retirement_date' is SHIFTED to optional for Pensioners
];

// We simulate the validation logic from EmployeeController@update
$appointmentType = $pensionerType;

$validationRules = [
    'first_name' => 'required|string|max:50',
    'surname' => 'required|string|max:50',
    'gender' => 'required|string|max:50',
    'date_of_birth' => 'required|date|before:' . Carbon::now()->subYears(18)->format('Y-m-d'),
    'state_id' => 'required|exists:states,state_id',
    'lga_id' => 'required|exists:lgas,id',
    'nationality' => 'required|string|max:50',
    'mobile_no' => 'required|string|max:15',
    'address' => 'required|string',
    'date_of_first_appointment' => 'required|date',
    'appointment_type_id' => 'required|exists:appointment_types,id',
    'status' => 'required|in:' . implode(',', ($appointmentType && ($appointmentType->name === 'Contract' || $appointmentType->name === 'Pensioners')) ? ['Active', 'Suspended', 'Retired', 'Retired-Active', 'Deceased', 'Hold'] : ['Active', 'Suspended', 'Retired', 'Deceased', 'Hold']),
    'kin_name' => 'required|string|max:100',
    'kin_relationship' => 'required|string|max:50',
    'kin_mobile_no' => 'required|string|max:20',
    'kin_address' => 'required|string',
    'bank_name' => 'required|string|max:100',
    'bank_code' => 'required|string|max:20',
    'account_name' => 'required|string|max:100',
    'account_no' => 'required|string|max:20',
];
$validationRules['department_id'] = 'required|exists:departments,department_id';

if ($appointmentType && $appointmentType->name === 'Casual') {
    $validationRules['contract_start_date'] = 'required|date';
    $validationRules['contract_end_date'] = 'required|date|after:contract_start_date';
    $validationRules['amount'] = 'required|numeric';
} elseif ($appointmentType && $appointmentType->name === 'Contract') {
    $validationRules['contract_start_date'] = 'required|date';
    $validationRules['contract_end_date'] = 'required|date|after:contract_start_date';
    $validationRules['amount'] = 'required|numeric';
    $validationRules['cadre_id'] = 'nullable|exists:cadres,cadre_id';
    $validationRules['salary_scale_id'] = 'nullable|exists:salary_scales,id';
    $validationRules['grade_level_id'] = 'nullable|exists:grade_levels,id';
    $validationRules['step_id'] = 'nullable|exists:steps,id';
    $validationRules['expected_retirement_date'] = 'nullable|date';
    $validationRules['rank_id'] = 'nullable|exists:ranks,id';
} elseif ($appointmentType && $appointmentType->name === 'Pensioners') {
    $validationRules['cadre_id'] = 'nullable|exists:cadres,cadre_id';
    $validationRules['salary_scale_id'] = 'nullable|exists:salary_scales,id';
    $validationRules['grade_level_id'] = 'nullable|exists:grade_levels,id';
    $validationRules['step_id'] = 'nullable|exists:steps,id';
    $validationRules['expected_retirement_date'] = 'nullable|date';
    $validationRules['rank_id'] = 'nullable|exists:ranks,id';
} else {
    $validationRules['cadre_id'] = 'required|exists:cadres,cadre_id';
    $validationRules['salary_scale_id'] = 'required|exists:salary_scales,id';
    $validationRules['grade_level_id'] = 'required|exists:grade_levels,id';
    $validationRules['step_id'] = 'required|exists:steps,id';
    $validationRules['expected_retirement_date'] = 'required|date';
    $validationRules['rank_id'] = 'required|exists:ranks,id';
}

$validator = Validator::make($data, $validationRules);

if ($validator->fails()) {
    echo "VALIDATION FAILED: \n";
    print_r($validator->errors()->all());
} else {
    echo "SUCCESS: Validation passed for Pensioner type without mandatory retirement date.\n";
}
