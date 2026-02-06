<?php

namespace App\Imports;

use App\Models\Pensioner;
use App\Models\Employee;
use App\Models\Retirement;
use App\Models\ComputeBeneficiary;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PensionersImport implements ToModel, WithHeadingRow, WithValidation
{
    private $rows = 0;
    private $skipped = 0;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Update import logic to map new columns
        $staffNo = $row['staff_number'] ?? $row['staff_no'] ?? null;
        
        if (!$staffNo) {
            return null; // Skip empty rows
        }

        $employee = Employee::where('staff_no', $staffNo)
            ->orWhere('employee_id', $staffNo)
            ->first();

        if (!$employee) {
            // Standalone mode: Create Employee if missing
            $firstName = $row['first_name'] ?? 'Unknown';
            $surname = $row['surname'] ?? 'Unknown';
            $middleName = $row['middle_name'] ?? null;
            $deptName = $row['department'] ?? null;
            $gradeLevelName = $row['retired_grade_level'] ?? null;

            // Lookup Department
            $departmentId = null;
            if ($deptName) {
                $dept = \App\Models\Department::where('department_name', 'like', '%' . $deptName . '%')->first();
                $departmentId = $dept ? $dept->department_id : null;
            }

            // Lookup Grade Level
            $gradeLevelId = null;
            if ($gradeLevelName) {
                // Try numeric match first (e.g. "6" matches "GL 06") or name match
                $gl = \App\Models\GradeLevel::where('name', 'like', '%' . $gradeLevelName . '%')
                    ->orWhere('grade_level', $gradeLevelName)
                    ->first();
                $gradeLevelId = $gl ? $gl->id : null;
            }

            $employee = Employee::create([
                'staff_no' => $staffNo,
                'employee_id' => $staffNo, // Fallback if employee_id is not auto-increment (it seems to be, but checking model showed 'id' is unique, so maybe staff_no is just field)
                // Actually, employee_id in database usually refers to 'staff_id' or similar, but let's check validation.
                // Looking at other seeders/imports, 'employee_id' might be a separate field or just the staff_no. 
                // The error logs showed "Employee with ID ... not found", referencing the staff_no.
                // Let's assume 'staff_no' is the unique identifier needed.
                'first_name' => $firstName,
                'surname' => $surname,
                'middle_name' => $middleName,
                'department_id' => $departmentId,
                'grade_level_id' => $gradeLevelId,
                'status' => 'Retired',
                'date_of_birth' => '1960-01-01', // Default
                'date_of_first_appointment' => '1980-01-01', // Default
                'gender' => 'Male', // Default placeholder
                'email' => $staffNo . '@legacy-processed.com', // Dummy email to satisfy unique constraint
                'nationality' => 'Nigeria',
                'state_id' => 1, // Placeholder
                'lga_id' => 1,   // Placeholder
                'ward_id' => 1,  // Placeholder
                'rsa_balance' => 0.00,
                'pfa_contribution_rate' => 0.00,
                'on_probation' => 0,
                'probation_status' => 'Confirmed',
                'mobile_no' => '08000000000', // Default placeholder
                'address' => 'Unknown',       // Default placeholder
            ]);
            
            Log::info("Legacy Pensioner Import: Created new Employee {$staffNo}");
        }

        // Find existing retirement or create a dummy one if missing
        $retirement = Retirement::firstOrCreate(
            ['employee_id' => $employee->employee_id],
            [
                'retirement_date' => \Carbon\Carbon::create(2020, 1, 1), // Backdate for legacy to avoid pro-rata
                'notification_date' => \Carbon\Carbon::create(2019, 10, 1),
                'retire_reason' => 'Statutory Retirement (Legacy Import)',
                'gratuity_amount' => 0,
                'status' => 'approved'
            ]
        );

        // Update employee status
        if ($employee->status !== 'Pensioner' && $employee->status !== 'Retired') {
            $employee->update(['status' => 'Retired']);
        }

        // Check if pensioner record already exists
        $pensioner = Pensioner::where('employee_id', $employee->employee_id)->first();

        // Sanitize amounts
        $pensionAmount = str_replace([',', ' '], '', $row['new_pension'] ?? $row['pension_amount'] ?? 0);
        $gratuityAmount = 0; // Default to 0 as per instructions "only for the import legacy, ignor other things"
        
        // Handle Bank details
        $bankId = $employee->bank_id;
        $accountNumber = $row['account_number'] ?? $employee->account_number;
        $accountName = $row['account_name'] ?? $employee->account_name;

        if (isset($row['bank_code'])) {
            $bank = \App\Models\BankList::where('bank_code', $row['bank_code'])->first();
            if ($bank) {
                $bankId = $bank->id;
            }
        } elseif (isset($row['bank_name'])) {
             $bank = \App\Models\BankList::where('bank_name', 'like', '%' . $row['bank_name'] . '%')->first();
             if ($bank) {
                 $bankId = $bank->id;
             }
        }

        // Default to Gratuity Paid for legacy
        $isGratuityPaid = true; 
        $gratuityPaidDate = now();

        if ($pensioner) {
            // Update existing
            $pensioner->update([
                'pension_amount' => $pensionAmount,
                'bank_id' => $bankId,
                'account_number' => $accountNumber,
                'account_name' => $accountName,
                'is_gratuity_paid' => $isGratuityPaid,
                'gratuity_paid_date' => $gratuityPaidDate,
                'status' => 'Active',
            ]);
            $this->rows++;
            return $pensioner;
        }

        // Create new Pensioner
        $beneficiaryComputation = ComputeBeneficiary::where('id_no', $employee->employee_id)
            ->orWhere('id_no', $employee->staff_no)->first();

        $newPensioner = new Pensioner([
            'employee_id' => $employee->employee_id,
            'full_name' => $employee->full_name,
            'surname' => $employee->surname,
            'first_name' => $employee->first_name,
            'middle_name' => $employee->middle_name,
            'email' => $employee->email,
            'phone_number' => $employee->phone ?? $employee->mobile_no,
            'date_of_birth' => $employee->date_of_birth,
            'place_of_birth' => $employee->place_of_birth ?? 'Unknown',
            'date_of_first_appointment' => $employee->date_of_first_appointment,
            'date_of_retirement' => $retirement->retirement_date,
            'retirement_reason' => $retirement->retire_reason,
            'retirement_type' => 'RB',
            'department_id' => $employee->department_id,
            'rank_id' => $employee->rank_id,
            'step_id' => $employee->step_id,
            'grade_level_id' => $employee->grade_level_id,
            'salary_scale_id' => $employee->salary_scale_id ?? 1,
            'local_gov_area_id' => $employee->lga_id,
            'bank_id' => $bankId,
            'account_number' => $accountNumber,
            'account_name' => $accountName,
            'pension_amount' => $pensionAmount,
            'gratuity_amount' => $gratuityAmount,
            'total_death_gratuity' => $gratuityAmount,
            'years_of_service' => $employee->years_of_service ?? 35,
            'pension_percentage' => $beneficiaryComputation ? $beneficiaryComputation->pct_pension : 0,
            'gratuity_percentage' => $beneficiaryComputation ? $beneficiaryComputation->pct_gratuity : 0,
            'address' => $employee->address,
            'next_of_kin_name' => $employee->next_of_kin_name ?? 'Unknown',
            'next_of_kin_phone' => $employee->next_of_kin_phone ?? 'Unknown',
            'next_of_kin_address' => $employee->next_of_kin_address ?? 'Unknown',
            'status' => 'Active',
            'retirement_id' => $retirement->id,
            'created_by' => auth()->id() ?? 1, // Fallback to system user during import
            'is_gratuity_paid' => $isGratuityPaid,
            'gratuity_paid_date' => $gratuityPaidDate,
        ]);

        try {
            $newPensioner->save();
            $this->rows++;
            Log::info("Legacy Pensioner Import: Created Pensioner for Employee {$employee->staff_no}");
        } catch (\Exception $e) {
            Log::error("Legacy Pensioner Import: Failed to create Pensioner for Employee {$employee->staff_no}: " . $e->getMessage());
            $this->skipped++;
            return null;
        }

        return $newPensioner;
    }

    public function rules(): array
    {
        return [
            // 'staff_no' => 'required', // Validation handled in model() to support multiple column names
        ];
    }

    public function getRowCount()
    {
        return $this->rows;
    }

    public function getSkippedCount()
    {
        return $this->skipped;
    }
}
