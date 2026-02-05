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
        $staffNo = $row['staff_no'] ?? $row['staff_id'] ?? null;
        
        if (!$staffNo) {
            return null; // Skip empty rows
        }

        $employee = Employee::where('staff_no', $staffNo)
            ->orWhere('employee_id', $staffNo)
            ->first();

        if (!$employee) {
            Log::warning("Legacy Pensioner Import: Employee with ID {$staffNo} not found. Skipped.");
            $this->skipped++;
            return null;
        }

        // We need the employee to be retired.
        // If they are not marked as retired, we can't process them as a pensioner safely without more info.
        // However, user said "put staffs that are already retired". 
        // We will assume they MIGHT be active in system but user wants them as pensioner.
        // Let's enforce that they MUST exist as an Employee first.
        
        // Find existing retirement or create a dummy one if missing (since it's legacy)
        $retirement = Retirement::firstOrCreate(
            ['employee_id' => $employee->employee_id],
            [
                'retirement_date' => now()->subDay(), // Default to yesterday if unknown
                'notification_date' => now()->subMonths(3),
                'retire_reason' => 'Statutory Retirement (Legacy Import)',
                'gratuity_amount' => $row['gratuity_amount'] ?? 0,
                'status' => 'approved'
            ]
        );

        // Update employee status if not already
        if ($employee->status !== 'Pensioner' && $employee->status !== 'Retired') {
            $employee->update(['status' => 'Retired']);
        }

        // Check if pensioner record already exists
        $pensioner = Pensioner::where('employee_id', $employee->employee_id)->first();

        // Data for pensioner
        // Sanitize amounts by removing commas
        $pensionAmount = str_replace(',', '', $row['pension_amount'] ?? 0);
        $gratuityAmount = str_replace(',', '', $row['gratuity_amount'] ?? 0);
        
        // Handle Gratuity Paid Date
        $isGratuityPaid = true; // User requested "mark their gratuity as paid"
        $gratuityPaidDate = null;

        if (isset($row['gratuity_paid_date']) && !empty($row['gratuity_paid_date'])) {
            try {
                // Try parsing Excel date or string date
                $gratuityPaidDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['gratuity_paid_date']);
            } catch (\Exception $e) {
                // Fallback to simple parse
                try {
                     $gratuityPaidDate = Carbon::parse($row['gratuity_paid_date']);
                } catch (\Exception $e2) {
                    $gratuityPaidDate = now();
                }
            }
        } else {
            $gratuityPaidDate = now();
        }

        if ($pensioner) {
            // Update existing
            $pensioner->update([
                'pension_amount' => $pensionAmount,
                'gratuity_amount' => $gratuityAmount,
                'is_gratuity_paid' => $isGratuityPaid,
                'gratuity_paid_date' => $gratuityPaidDate,
                'status' => 'Active', // Ensure they are active pensioners
            ]);
            $this->rows++;
            return $pensioner;
        }

        // Create new Pensioner
        // We need to fill required fields from Employee data
        
        // Try to find beneficiary computation for percentage
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
            'place_of_birth' => $employee->place_of_birth ?? 'Unknown', // Fallback
            'date_of_first_appointment' => $employee->date_of_first_appointment,
            'date_of_retirement' => $retirement->retirement_date,
            'retirement_reason' => $retirement->retire_reason,
            'retirement_type' => 'RB',
            'department_id' => $employee->department_id,
            'rank_id' => $employee->rank_id,
            'step_id' => $employee->step_id,
            'grade_level_id' => $employee->grade_level_id,
            'salary_scale_id' => $employee->salary_scale_id ?? 1, // Fallback if missing? hopefully not
            'local_gov_area_id' => $employee->lga_id,
            'bank_id' => $employee->bank_id, // Assuming employee has bank_id field, or relation
            'account_number' => $employee->account_number ?? $employee->bank_account_no, // Check employee model strictly later
            'account_name' => $employee->account_name ?? $employee->full_name,
            'pension_amount' => $pensionAmount,
            'gratuity_amount' => $gratuityAmount,
            'total_death_gratuity' => $gratuityAmount,
            'years_of_service' => $employee->years_of_service ?? 35, // Fallback
            'pension_percentage' => $beneficiaryComputation ? $beneficiaryComputation->pct_pension : 0,
            'gratuity_percentage' => $beneficiaryComputation ? $beneficiaryComputation->pct_gratuity : 0,
            'address' => $employee->address,
            'next_of_kin_name' => $employee->next_of_kin_name ?? 'Unknown',
            'next_of_kin_phone' => $employee->next_of_kin_phone ?? 'Unknown',
            'next_of_kin_address' => $employee->next_of_kin_address ?? 'Unknown',
            'status' => 'Active',
            'retirement_id' => $retirement->id,
            'created_by' => auth()->id(),
            'is_gratuity_paid' => $isGratuityPaid,
            'gratuity_paid_date' => $gratuityPaidDate,
        ]);

        $newPensioner->save();
        $this->rows++;

        return $newPensioner;
    }

    public function rules(): array
    {
        return [
            'staff_no' => 'required',
            // 'pension_amount' => 'required|numeric', // Optional? No, user said amount.
            // 'gratuity_amount' => 'required|numeric',
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
