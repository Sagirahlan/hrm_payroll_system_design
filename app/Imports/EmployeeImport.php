<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\AppointmentType;
use App\Models\NextOfKin;
use App\Models\Bank;
use App\Models\BankList;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class EmployeeImport implements ToModel, WithValidation, WithHeadingRow
{
    public function model(array $row)
    {
        \Illuminate\Support\Facades\Log::info('Import Row Data:', $row);

        // Skip empty rows
        if (!isset($row['first_name']) || trim($row['first_name']) === '') {
            return null;
        }

        // Check if employee with same staff_no already exists
        $existingEmployee = null;
        if (!empty($row['staff_no'])) {
            $existingEmployee = Employee::where('staff_no', $row['staff_no'])->first();
        }

        // Handle state/lga/ward mapping to IDs for both create and update
        $stateId = null;
        $lgaId = null;
        $wardId = null;
        $payPoint = null;
        $cadreId = null;
        $gradeLevelId = null;
        $stepId = null;
        $rankId = null;
        $departmentId = null;
        $appointmentTypeId = null;

        // Handle state mapping - check for various possible column names
        $stateValue = $row['state_of_origin'] ??
                     $row['state'] ??
                     $row['state_name'] ??
                     $row['state_id'] ??
                     null;

        if (!empty($stateValue) && !in_array(strtolower(trim($stateValue)), ['n/a', 'na', 'not applicable', 'none', 'null', ''])) {
            $state = \App\Models\State::where('name', 'like', '%' . $stateValue . '%')->first();
            if (!$state) {
                $state = \App\Models\State::where('state_id', $row['state_of_origin'])->first(); // If it's already an ID
            }
            $stateId = $state ? $state->state_id : null;
        }

        // Handle lga mapping - check for various possible column names
        $lgaValue = $row['lga'] ??
                   $row['lga_name'] ??
                   $row['lga_id'] ??
                   $row['local_government'] ??
                   null;

        if (!empty($lgaValue) && !in_array(strtolower(trim($lgaValue)), ['n/a', 'na', 'not applicable', 'none', 'null', ''])) {
            $lga = \App\Models\Lga::where('name', 'like', '%' . $lgaValue . '%')->first();
            if (!$lga) {
                $lga = \App\Models\Lga::where('id', $lgaValue)->first(); // If it's already an ID
            }
            $lgaId = $lga ? $lga->id : null;
        }

        // Handle ward mapping - check for various possible column names
        $wardValue = $row['ward'] ??
                    $row['ward_name'] ??
                    $row['ward_id'] ??
                    null;

        if (!empty($wardValue) && !in_array(strtolower(trim($wardValue)), ['n/a', 'na', 'not applicable', 'none', 'null', ''])) {
            $ward = \App\Models\Ward::where('ward_name', 'like', '%' . $wardValue . '%')->first();
            if (!$ward) {
                $ward = \App\Models\Ward::where('ward_id', $wardValue)->first(); // If it's already an ID
            }
            $wardId = $ward ? $ward->ward_id : null;
        }

        // Add pay point value (if present and not a placeholder)
        $payPointValue = $row['pay_point'] ?? $row['paypoint'] ?? null;
        if (!empty($payPointValue) && !in_array(strtolower(trim($payPointValue)), ['n/a', 'na', 'not applicable', 'none', 'null', ''])) {
            $payPoint = $payPointValue;
        }

        if (!empty($row['cadre_id'])) {
            // Try to find cadre by name first, then try the ID
            $cadre = \App\Models\Cadre::where('name', 'like', '%' . $row['cadre_id'] . '%')->first();
            if (!$cadre) {
                $cadre = \App\Models\Cadre::where('cadre_id', $row['cadre_id'])->first(); // If it's already an ID
            }
            $cadreId = $cadre ? $cadre->cadre_id : null;
        }

        if (!empty($row['grade_level_id'])) {
            // Try to find grade level by name first, then try the ID
            $gradeLevel = \App\Models\GradeLevel::where('name', 'like', '%' . $row['grade_level_id'] . '%')->first();
            if (!$gradeLevel) {
                $gradeLevel = \App\Models\GradeLevel::where('id', $row['grade_level_id'])->first(); // If it's already an ID
            }
            $gradeLevelId = $gradeLevel ? $gradeLevel->id : null;
        }

        if (!empty($row['step_id'])) {
            // Try to find step by name first, then try the ID
            $stepQuery = \App\Models\Step::query();

            // If we have a grade level resolved, scope the step search to it
            if ($gradeLevelId) {
                $stepQuery->where('grade_level_id', $gradeLevelId);
            }

            // Search by name (like) or exact ID
            $searchValue = $row['step_id'];
            
            // Clone query for first attempt (by name)
            $stepByName = clone $stepQuery;
            $step = $stepByName->where('name', 'like', '%' . $searchValue . '%')->first();

            if (!$step) {
                // Clone query for second attempt (by ID)
                $stepById = clone $stepQuery;
                $step = $stepById->where('id', $searchValue)->first(); 
            }
            
            $stepId = $step ? $step->id : null;
        }

        if (!empty($row['rank_id'])) {
            // Try to find rank by name first, then try the ID
            $rank = \App\Models\Rank::where('name', 'like', '%' . $row['rank_id'] . '%')->first();
            if (!$rank) {
                $rank = \App\Models\Rank::where('id', $row['rank_id'])->first(); // If it's already an ID
            }
            $rankId = $rank ? $rank->id : null;
        }

        if (!empty($row['department_id'])) {
            // Try to find department by name first, then try the ID
            $department = \App\Models\Department::where('department_name', 'like', '%' . $row['department_id'] . '%')->first();
            if (!$department) {
                $department = \App\Models\Department::where('department_id', $row['department_id'])->first(); // If it's already an ID
            }
            $departmentId = $department ? $department->department_id : null;
        }

        if (!empty($row['appointment_type_id'])) {
            // Try to find appointment type by name first, then try the ID
            $appointmentType = \App\Models\AppointmentType::where('name', 'like', '%' . $row['appointment_type_id'] . '%')->first();
            if (!$appointmentType) {
                $appointmentType = \App\Models\AppointmentType::where('id', $row['appointment_type_id'])->first(); // If it's already an ID
            }
            $appointmentTypeId = $appointmentType ? $appointmentType->id : null;
        }

        // Helper to check for occupation columns
        $occupation = $row['next_of_kin_occupation'] ?? 
                     $row['occupation'] ?? 
                     $row['kin_occupation'] ?? 
                     $row['nextofkin_occupation'] ?? 
                     null;

        // Helper to check for place of work columns
        $placeOfWork = $row['next_of_kin_place_of_work'] ?? 
                      $row['place_of_work'] ?? 
                      $row['kin_place_of_work'] ?? 
                      $row['nextofkin_place_of_work'] ?? 
                      null;

        // Temporary logging for debugging
        if ($occupation === 'N/A' || $placeOfWork === 'N/A') {
            \Illuminate\Support\Facades\Log::info('Import debug N/A found:', [
                'occupation_raw' => $occupation,
                'place_of_work_raw' => $placeOfWork, 
                'row_keys' => array_keys($row)
            ]);
        }

        // Handle next of kin data if present in the Excel file
        $nextOfKinData = null;
        if (!empty($row['next_of_kin_name']) || !empty($row['next_of_kin_relationship']) ||
            !empty($row['next_of_kin_mobile']) || !empty($row['next_of_kin_address']) ||
            !empty($occupation) || !empty($placeOfWork)) { // Added check for occupation/pow trigger

            $nextOfKinData = [
                'name' => $row['next_of_kin_name'] ?? $row['next_of_kin'] ?? null,
                'relationship' => $row['next_of_kin_relationship'] ?? $row['relationship'] ?? null,
                'mobile_no' => $row['next_of_kin_mobile'] ?? $row['nextofkin_mobile'] ?? null,
                'address' => $row['next_of_kin_address'] ?? $row['nextofkin_address'] ?? null,
                'occupation' => $occupation,
                'place_of_work' => $placeOfWork,
            ];
        }

        // Handle bank data if present in the Excel file
        $bankData = null;
        $bankName = $row['bank_name'] ?? $row['bank'] ?? $row['bankname'] ?? $row['bank_name_display'] ?? null;

        // Log bank-related fields for debugging
        \Illuminate\Support\Facades\Log::info('Bank Import Debug:', [
            'bank_name_found' => $bankName,
            'account_no_raw' => $row['account_no'] ?? $row['account_number'] ?? $row['accountno'] ?? $row['acc_no'] ?? 'NOT FOUND',
            'account_name_raw' => $row['account_name'] ?? $row['accountname'] ?? 'NOT FOUND',
            'available_keys' => array_keys($row)
        ]);

        if (!empty($bankName) && !in_array(strtolower(trim($bankName)), ['n/a', 'na', 'not applicable', 'none', 'null', ''])) {
            // Look up the bank code from the BankList (case-insensitive)
            $bankList = BankList::whereRaw('LOWER(bank_name) LIKE ?', ['%' . strtolower(trim($bankName)) . '%'])->first();
            $bankCode = $bankList ? $bankList->bank_code : null;

            // If not in BankList, use a default or try to get from Excel
            $excelBankCode = $row['bank_code'] ?? $row['bankcode'] ?? $row['code'] ?? null;
            if (!$bankCode && $excelBankCode && !in_array(strtolower(trim($excelBankCode)), ['n/a', 'na', 'not applicable', 'none', 'null', ''])) {
                $bankCode = $excelBankCode;
            }

            $accountName = $row['account_name'] ?? $row['accountname'] ?? $row['acc_name'] ?? ($row['first_name'] . ' ' . $row['surname']);
            $accountNumber = $row['account_no'] ?? $row['account_number'] ?? $row['accountno'] ?? $row['acc_no'] ?? $row['account_num'] ?? null;

            // Use the standardized bank name from BankList if found, otherwise use Excel value
            $normalizedBankName = $bankList ? $bankList->bank_name : $bankName;

            $bankData = [
                'bank_name' => $normalizedBankName,
                'bank_code' => $bankCode,
                'account_name' => $accountName,
                'account_no' => $accountNumber,
            ];
            
            \Illuminate\Support\Facades\Log::info('Bank Data Created:', $bankData);
        }

        // Casual/Contract fields
        $contractStartDate = $this->transformDate($row['casual_start_date'] ?? $row['contract_start_date'] ?? null);
        $contractEndDate = $this->transformDate($row['casual_end_date'] ?? $row['casual_end_date'] ?? $row['contract_end_date'] ?? null);
        $amount = $row['amount'] ?? null;
        
        // Clean amount if it contains currency symbols or commas
        if ($amount) {
            $amount = floatval(preg_replace('/[^0-9.]/', '', $amount));
        }

        if ($existingEmployee) {
            // Update existing employee instead of creating a new one
            $existingEmployee->update([
                'first_name' => $row['first_name'] ?? null,
                'surname' => $row['surname'] ?? null,
                'middle_name' => $row['middle_name'] ?? null,
                'gender' => $row['gender'] ?? null,
                'date_of_birth' => $this->transformDate($row['date_of_birth'] ?? null),
                'state_id' => $stateId,
                'lga_id' => $lgaId,
                'ward_id' => $wardId,
                'pay_point' => $payPoint,
                'nationality' => $row['nationality'] ?? null,
                'nin' => $row['nin'] ?? null,
                'mobile_no' => $this->transformPhone($row['mobile_no'] ?? null),
                'email' => $row['email'] ?? null,
                'address' => $row['address'] ?? null,
                'date_of_first_appointment' => $this->transformDate($row['date_of_first_appointment'] ?? null),
                'cadre_id' => $cadreId,
                'grade_level_id' => $gradeLevelId,
                'step_id' => $stepId,
                'rank_id' => $rankId,
                'department_id' => $departmentId,
                'expected_next_promotion' => $this->transformDate($row['expected_next_promotion'] ?? null),
                'expected_retirement_date' => $this->transformDate($row['expected_retirement_date'] ?? null),
                'status' => $row['status'] ?? null,
                'highest_certificate' => $row['highest_certificate'] ?? null,
                'grade_level_limit' => $row['grade_level_limit'] ?? null,
                'appointment_type_id' => $appointmentTypeId,
                'contract_start_date' => $contractStartDate,
                'contract_end_date' => $contractEndDate,
                'amount' => $amount,
            ]);

            // Update next of kin if provided
            if ($nextOfKinData) {
                $existingNextOfKin = NextOfKin::where('employee_id', $existingEmployee->employee_id)->first();
                if ($existingNextOfKin) {
                    $existingNextOfKin->update($nextOfKinData);
                } else {
                    $nextOfKinData['employee_id'] = $existingEmployee->employee_id;
                    NextOfKin::create($nextOfKinData);
                }
            }

            // Update bank data if provided
            if ($bankData) {
                $existingBank = Bank::where('employee_id', $existingEmployee->employee_id)->first();
                if ($existingBank) {
                    $existingBank->update($bankData);
                } else {
                    $bankData['employee_id'] = $existingEmployee->employee_id;
                    Bank::create($bankData);
                }
            }

            return $existingEmployee;
        } else {
            // Create new employee
            $employee = new Employee([
                'first_name' => $row['first_name'] ?? null,
                'surname' => $row['surname'] ?? null,
                'middle_name' => $row['middle_name'] ?? null,
                'gender' => $row['gender'] ?? null,
                'date_of_birth' => $this->transformDate($row['date_of_birth'] ?? null),
                'state_id' => $stateId,
                'lga_id' => $lgaId,
                'ward_id' => $wardId,
                'pay_point' => $payPoint,
                'nationality' => $row['nationality'] ?? null,
                'nin' => $row['nin'] ?? null,
                'mobile_no' => $this->transformPhone($row['mobile_no'] ?? null),
                'email' => $row['email'] ?? null,
                'address' => $row['address'] ?? null,
                'date_of_first_appointment' => $this->transformDate($row['date_of_first_appointment'] ?? null),
                'cadre_id' => $cadreId,
                'staff_no' => $row['staff_no'] ?? null,
                'grade_level_id' => $gradeLevelId,
                'step_id' => $stepId,
                'rank_id' => $rankId,
                'department_id' => $departmentId,
                'expected_next_promotion' => $this->transformDate($row['expected_next_promotion'] ?? null),
                'expected_retirement_date' => $this->transformDate($row['expected_retirement_date'] ?? null),
                'status' => $row['status'] ?? null,
                'highest_certificate' => $row['highest_certificate'] ?? null,
                'grade_level_limit' => $row['grade_level_limit'] ?? null,
                'appointment_type_id' => $appointmentTypeId,
                'contract_start_date' => $contractStartDate,
                'contract_end_date' => $contractEndDate,
                'amount' => $amount,
            ]);
            $employee->save();

            // Create next of kin if provided
            if ($nextOfKinData) {
                $nextOfKinData['employee_id'] = $employee->employee_id;
                NextOfKin::create($nextOfKinData);
            }

            // Create bank data if provided
            if ($bankData) {
                $bankData['employee_id'] = $employee->employee_id;
                Bank::create($bankData);
            }

            return $employee;
        }
    }

    public function rules(): array
    {
        return [
            // Define validation rules if needed
        ];
    }

    private function transformDate($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            } else {
                return Carbon::parse($value)->format('Y-m-d');
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    private function transformPhone($value)
    {
        if ($value === null) {
            return null;
        }

        // If it's numeric, format it as Nigerian phone number
        return preg_replace('/[^0-9]/', '', $value);
    }
}
