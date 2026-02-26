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
    private $updated = 0;
    private $updateMode = false;

    public function __construct(bool $updateMode = false)
    {
        $this->updateMode = $updateMode;
    }

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

        $rowFirstName = trim($row['first_name'] ?? '');
        $rowSurname = trim($row['surname'] ?? '');
        $rowMiddleName = trim($row['middle_name'] ?? '');

        // Only match by staff_no — do NOT use orWhere('employee_id') as that can match
        // the wrong employee when staff_no accidentally equals another employee's DB ID
         $employee = Employee::where('staff_no', $staffNo)->first();

        // Check if this is a different person sharing the same staff_no
        if ($employee && !empty($rowFirstName) && !empty($rowSurname)) {
            if (!$this->namesMatch($employee->first_name, $employee->surname, $rowFirstName, $rowSurname)) {
                // Different person — try to find the real employee by name
                $nameEmployee = $this->findEmployeeByName($rowFirstName, $rowSurname);

                if ($nameEmployee) {
                    $employee = $nameEmployee;
                    Log::info("Legacy Pensioner Import: Staff {$staffNo} name mismatch — found {$rowFirstName} {$rowSurname} as employee ID {$nameEmployee->employee_id} (staff {$nameEmployee->staff_no})");
                } else {
                    // Not found by name — create with unique staff_no
                    $suffix = 2;
                    while (Employee::where('staff_no', $staffNo . '-' . $suffix)->exists()) {
                        $suffix++;
                    }
                    $staffNo = $staffNo . '-' . $suffix;
                    $employee = null;
                    Log::info("Legacy Pensioner Import: Name mismatch on duplicate staff_no — creating {$rowFirstName} {$rowSurname} as new employee with staff_no {$staffNo}");
                }
            }
        }

        if (!$employee && !empty($rowFirstName) && !empty($rowSurname)) {
            // staff_no not found — try to find existing employee by name before creating
            $employee = $this->findEmployeeByName($rowFirstName, $rowSurname);
            if ($employee) {
                Log::info("Legacy Pensioner Import: Staff {$staffNo} not found — matched {$rowFirstName} {$rowSurname} to existing employee ID {$employee->employee_id} (staff {$employee->staff_no})");
            }
        }

        if (!$employee) {
            // Truly new — create Employee
            $firstName = $rowFirstName ?: 'Unknown';
            $surname = $rowSurname ?: 'Unknown';
            $middleName = $rowMiddleName ?: null;
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
                'first_name' => $firstName,
                'surname' => $surname,
                'middle_name' => $middleName,
                'department_id' => $departmentId,
                'grade_level_id' => $gradeLevelId,
                'status' => 'Retired',
                'date_of_birth' => '1960-01-01', // Default
                'date_of_first_appointment' => '1980-01-01', // Default
                'gender' => 'Male', // Default placeholder
                'email' => strtolower($staffNo . '.' . preg_replace('/[^a-zA-Z]/', '', $firstName)) . '@legacy-processed.com',
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
        // Casual employees (appointment_type_id=2) NEVER get Retired status
        // Contract employees (appointment_type_id=3) → "Retired-active"
        // Permanent employees → "Retired"
        if ($employee->appointment_type_id == 2) {
            // Casual staff — do NOT change status
            Log::info("Legacy Pensioner Import: Skipping status change for casual employee {$employee->staff_no}");
        } elseif ($employee->appointment_type_id == 3) {
            if ($employee->status !== 'Retired-active') {
                $employee->update(['status' => 'Retired-active']);
            }
        } elseif ($employee->status !== 'Pensioner' && $employee->status !== 'Retired') {
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
            if ($this->updateMode) {
                // Update Mode: Only update safe fields (bank/account/names/contact)
                // Preserve pension_amount, status, gratuity, and all payroll-sensitive fields
                $safeUpdates = [];

                if ($bankId) {
                    $safeUpdates['bank_id'] = $bankId;
                }
                if ($accountNumber) {
                    $safeUpdates['account_number'] = $accountNumber;
                }
                if ($accountName) {
                    $safeUpdates['account_name'] = $accountName;
                }

                // Update names if provided in the import file
                $firstName = $row['first_name'] ?? null;
                $surname = $row['surname'] ?? null;
                $middleName = $row['middle_name'] ?? null;

                if ($firstName) {
                    $safeUpdates['first_name'] = $firstName;
                }
                if ($surname) {
                    $safeUpdates['surname'] = $surname;
                }
                if ($middleName) {
                    $safeUpdates['middle_name'] = $middleName;
                }
                if ($firstName || $surname) {
                    $safeUpdates['full_name'] = trim(($firstName ?? $pensioner->first_name) . ' ' . ($middleName ?? $pensioner->middle_name ?? '') . ' ' . ($surname ?? $pensioner->surname));
                }

                if (!empty($safeUpdates)) {
                    $pensioner->update($safeUpdates);
                    $this->updated++;
                    Log::info("Legacy Pensioner Import (Update Mode): Updated safe fields for Pensioner {$employee->staff_no}", $safeUpdates);
                } else {
                    $this->skipped++;
                    Log::info("Legacy Pensioner Import (Update Mode): No changes for Pensioner {$employee->staff_no}");
                }

                return $pensioner;
            }

            // Normal mode: Update all fields (original behavior)
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

    public function getUpdatedCount()
    {
        return $this->updated;
    }

    public function isUpdateMode()
    {
        return $this->updateMode;
    }

    /**
     * Normalize a name by stripping special characters for comparison
     */
    private function normalizeName(string $name): string
    {
        // Remove apostrophes, hyphens, dots, extra spaces
        $name = preg_replace("/['\-\.]/", '', $name);
        $name = preg_replace('/\s+/', ' ', $name);
        return strtolower(trim($name));
    }

    /**
     * Check if two sets of names match (fuzzy, handles reversed order and special chars)
     */
    private function namesMatch(string $empFirst, string $empSurname, string $rowFirst, string $rowSurname): bool
    {
        $ef = $this->normalizeName($empFirst);
        $es = $this->normalizeName($empSurname);
        $rf = $this->normalizeName($rowFirst);
        $rs = $this->normalizeName($rowSurname);

        // Direct match (partial)
        if ((str_contains($ef, $rf) || str_contains($rf, $ef)) &&
            (str_contains($es, $rs) || str_contains($rs, $es))) {
            return true;
        }

        // Reversed match (first_name <-> surname)
        if ((str_contains($ef, $rs) || str_contains($rs, $ef)) &&
            (str_contains($es, $rf) || str_contains($rf, $es))) {
            return true;
        }

        return false;
    }

    /**
     * Find an employee by name, handling reversed names and special characters
     */
    private function findEmployeeByName(string $firstName, string $surname): ?Employee
    {
        $normFirst = $this->normalizeName($firstName);
        $normSurname = $this->normalizeName($surname);

        // Load non-casual, non-legacy employees and match by normalized name
        // Excludes casual staff (appointment_type_id=2) to prevent false matches
        $candidates = Employee::where('email', 'not like', '%@legacy-processed.com')
            ->where('appointment_type_id', '!=', 2)
            ->where(function($q) use ($firstName, $surname) {
                // Broad SQL filter first to reduce candidates
                $q->where(function($q2) use ($firstName, $surname) {
                    $q2->where('first_name', 'like', '%' . substr($firstName, 0, 3) . '%')
                       ->orWhere('surname', 'like', '%' . substr($firstName, 0, 3) . '%')
                       ->orWhere('first_name', 'like', '%' . substr($surname, 0, 3) . '%')
                       ->orWhere('surname', 'like', '%' . substr($surname, 0, 3) . '%');
                });
            })
            ->get();

        foreach ($candidates as $emp) {
            if ($this->namesMatch($emp->first_name, $emp->surname, $firstName, $surname)) {
                return $emp;
            }
        }

        return null;
    }
}
