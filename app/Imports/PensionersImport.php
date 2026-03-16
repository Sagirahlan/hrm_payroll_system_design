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
    public $affectedCount = 0;
    private $updateMode = false;
    private $accountNameOnly = false;
    private $totalRowsSeen = 0;
    private $headersLogged = false;

    public function __construct(bool $updateMode = false, bool $accountNameOnly = false)
    {
        $this->updateMode = $updateMode;
        $this->accountNameOnly = $accountNameOnly;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $this->totalRowsSeen++;

        // Log headers once on first row for debugging
        if (!$this->headersLogged) {
            Log::info("Legacy Pensioner Import: Column headers detected: " . implode(', ', array_keys($row)));
            $this->headersLogged = true;
        }

        // Support multiple possible column names for staff number and employee ID
        $staffNo = $row['staff_number'] ?? $row['staff_no'] ?? $row['staff_n'] ?? $row['staffno'] ?? $row['staff_number'] ?? $row['id_no'] ?? null;
        $employeeIdVal = $row['employee_id'] ?? $row['id'] ?? $row['employee_'] ?? $row['employeeid'] ?? $row['emp_id'] ?? $row['loyee_'] ?? null;
        
        // Find existing employee
        $employee = null;
        if (!empty($staffNo)) {
            $employee = Employee::where('staff_no', $staffNo)->first();
        }
        
        if (!$employee && !empty($employeeIdVal)) {
            $employee = Employee::find($employeeIdVal);
        }

        // ACCOUNT NAME ONLY MODE
        if ($this->accountNameOnly) {
            $accountName = $row['account_name'] ?? $row['accountname'] ?? $row['acc_name'] ?? $row['account_n'] ?? $row['account_n_'] ?? $row['acc_name_'] ?? null;
            
            if ($employee) {
                if ($accountName) {
                    // Ensure retirement record exists (required for some pensioner lookups/logic)
                    $retirement = Retirement::firstOrCreate(
                        ['employee_id' => $employee->employee_id],
                        [
                            'retirement_date' => Carbon::create(2020, 1, 1),
                            'notification_date' => Carbon::create(2019, 10, 1),
                            'retire_reason' => 'Statutory Retirement (Legacy Import)',
                            'gratuity_amount' => 0,
                            'status' => 'approved'
                        ]
                    );

                    // Update or create pensioner record for this employee
                    $pRecord = Pensioner::where('employee_id', $employee->employee_id)->first();
                    if ($pRecord) {
                        $pRecord->update(['account_name' => $accountName]);
                    } else {
                        // Create comprehensive pensioner record from employee data
                        Pensioner::create([
                            'employee_id'               => $employee->employee_id,
                            'account_name'              => $accountName,
                            'full_name'                 => $employee->full_name,
                            'surname'                   => $employee->surname,
                            'first_name'                => $employee->first_name,
                            'middle_name'               => $employee->middle_name,
                            'email'                     => $employee->email,
                            'phone_number'              => $employee->phone ?? $employee->mobile_no,
                            'date_of_birth'             => $employee->date_of_birth ?? '1960-01-01',
                            'place_of_birth'            => $employee->place_of_birth ?? 'Unknown',
                            'date_of_first_appointment' => $employee->date_of_first_appointment ?? '1980-01-01',
                            'status'                    => 'Active',
                            'pension_amount'            => 0,
                            'gratuity_amount'           => 0,
                            'department_id'             => $employee->department_id,
                            'grade_level_id'            => $employee->grade_level_id,
                            'bank_id'                   => $employee->bank_id,
                            'account_number'            => $employee->account_number,
                            'address'                   => $employee->address ?? 'Unknown',
                            'retirement_id'             => $retirement->id,
                            'retirement_type'           => 'RB',
                            'date_of_retirement'        => $retirement->retirement_date,
                            'retirement_reason'         => $retirement->retire_reason,
                            'created_by'                => auth()->id() ?? 1,
                        ]);
                    }
                    $this->affectedCount++;
                    Log::info("AccountNameOnly (Legacy): Updated account name for staff " . ($staffNo ?? $employee->employee_id), ['account_name' => $accountName]);
                } else {
                    Log::warning("AccountNameOnly (Legacy): Found staff " . ($staffNo ?? $employee->employee_id) . " but NO account name found in row.");
                }
            } else {
                if ($staffNo || $employeeIdVal) {
                    Log::warning("AccountNameOnly (Legacy): Staff not found for " . ($staffNo ?? $employeeIdVal));
                }
            }
            return null; // Don't proceed with normal import logic
        }

        if (!$staffNo) {
            $this->skipped++;
            $name = trim(($row['first_name'] ?? $row['firstname'] ?? '') . ' ' . ($row['surname'] ?? ''));
            if (!empty(trim($name))) {
                Log::warning("Legacy Pensioner Import: Row {$this->totalRowsSeen} skipped — no staff number found. Name: {$name}");
            }
            return null; // Skip empty rows
        }

        $rowFirstName = trim($row['first_name'] ?? '');
        $rowSurname = trim($row['surname'] ?? '');
        $rowMiddleName = trim($row['middle_name'] ?? '');

        // Check if this is a different person sharing the same staff_no (Legacy identification logic)
        if ($employee && !empty($rowFirstName) && !empty($rowSurname)) {
            if (!$this->namesMatch($employee->first_name, $employee->surname, $rowFirstName, $rowSurname)) {
                $nameEmployee = $this->findEmployeeByName($rowFirstName, $rowSurname);

                if ($nameEmployee) {
                    $employee = $nameEmployee;
                } else {
                    $suffix = 2;
                    while (Employee::where('staff_no', $staffNo . '-' . $suffix)->exists()) {
                        $suffix++;
                    }
                    $staffNo = $staffNo . '-' . $suffix;
                    $employee = null;
                }
            }
        }

        if (!$employee && !empty($rowFirstName) && !empty($rowSurname)) {
            $employee = $this->findEmployeeByName($rowFirstName, $rowSurname);
        }

        if (!$employee) {
            // Truly new — create Employee
            $firstName = $rowFirstName ?: 'Unknown';
            $surname = $rowSurname ?: 'Unknown';
            $middleName = $rowMiddleName ?: null;
            $deptName = $row['department'] ?? null;
            $gradeLevelName = $row['retired_grade_level'] ?? null;

            $departmentId = null;
            if ($deptName) {
                $dept = \App\Models\Department::where('department_name', 'like', '%' . $deptName . '%')->first();
                $departmentId = $dept ? $dept->department_id : null;
            }

            $gradeLevelId = null;
            if ($gradeLevelName) {
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
                'date_of_birth' => '1960-01-01',
                'date_of_first_appointment' => '1980-01-01',
                'gender' => 'Male',
                'email' => strtolower($staffNo . '.' . preg_replace('/[^a-zA-Z]/', '', $firstName)) . '@legacy-processed.com',
                'nationality' => 'Nigeria',
                'state_id' => 1,
                'lga_id' => 1,
                'ward_id' => 1,
                'rsa_balance' => 0.00,
                'pfa_contribution_rate' => 0.00,
                'on_probation' => 0,
                'probation_status' => 'Confirmed',
                'mobile_no' => '08000000000',
                'address' => 'Unknown',
            ]);
        }

        // Find existing retirement or create a dummy one if missing
        $retirement = Retirement::firstOrCreate(
            ['employee_id' => $employee->employee_id],
            [
                'retirement_date' => Carbon::create(2020, 1, 1),
                'notification_date' => Carbon::create(2019, 10, 1),
                'retire_reason' => 'Statutory Retirement (Legacy Import)',
                'gratuity_amount' => 0,
                'status' => 'approved'
            ]
        );

        // Update employee status
        if ($employee->appointment_type_id == 2) {
            // Casual staff — skip
        } elseif ($employee->appointment_type_id == 3) {
            if ($employee->status !== 'Retired-active') {
                $employee->update(['status' => 'Retired-active']);
            }
        } elseif ($employee->status !== 'Pensioner' && $employee->status !== 'Retired') {
            $employee->update(['status' => 'Retired']);
        }

        // Check if pensioner record already exists
        $pensioner = Pensioner::where('employee_id', $employee->employee_id)->first();

        // rest of the logic continues from line 102...
        // Wait, I should include the rest of the logic in the replacement to be safe.
        
        $pensionAmount = str_replace([',', ' '], '', $row['new_pension'] ?? $row['pension_amount'] ?? 0);
        $gratuityAmount = 0;
        
        $bankId = $employee->bank_id;
        $accountNumber = $row['account_number'] ?? $employee->account_number;
        $accountName = $row['account_name'] ?? $employee->account_name;

        if (isset($row['bank_code'])) {
            $bank = \App\Models\BankList::where('bank_code', $row['bank_code'])->first();
            if ($bank) { $bankId = $bank->id; }
        } elseif (isset($row['bank_name'])) {
            $bank = \App\Models\BankList::where('bank_name', 'like', '%' . $row['bank_name'] . '%')->first();
            if ($bank) { $bankId = $bank->id; }
        }

        $isGratuityPaid = true; 
        $gratuityPaidDate = now();

        if ($pensioner) {
            if ($this->updateMode) {
                $safeUpdates = [];
                if ($bankId) { $safeUpdates['bank_id'] = $bankId; }
                if ($accountNumber) { $safeUpdates['account_number'] = $accountNumber; }
                if ($accountName) { $safeUpdates['account_name'] = $accountName; }

                $firstName = $row['first_name'] ?? null;
                $surname = $row['surname'] ?? null;
                $middleName = $row['middle_name'] ?? null;

                if ($firstName) { $safeUpdates['first_name'] = $firstName; }
                if ($surname) { $safeUpdates['surname'] = $surname; }
                if ($middleName) { $safeUpdates['middle_name'] = $middleName; }
                if ($firstName || $surname) {
                    $safeUpdates['full_name'] = trim(($firstName ?? $pensioner->first_name) . ' ' . ($middleName ?? $pensioner->middle_name ?? '') . ' ' . ($surname ?? $pensioner->surname));
                }

                if (!empty($safeUpdates)) {
                    $pensioner->update($safeUpdates);
                    $this->updated++;
                } else {
                    $this->skipped++;
                }
                return $pensioner;
            }

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
            'grade_level_id' => $employee->grade_level_id,
            'bank_id' => $bankId,
            'account_number' => $accountNumber,
            'account_name' => $accountName,
            'pension_amount' => $pensionAmount,
            'gratuity_amount' => $gratuityAmount,
            'status' => 'Active',
            'retirement_id' => $retirement->id,
            'created_by' => auth()->id() ?? 1,
            'is_gratuity_paid' => $isGratuityPaid,
            'gratuity_paid_date' => $gratuityPaidDate,
        ]);

        try {
            $newPensioner->save();
            $this->rows++;
            return $newPensioner;
        } catch (\Exception $e) {
            $this->skipped++;
            return null;
        }
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
