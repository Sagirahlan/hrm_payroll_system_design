<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ComputeBeneficiary;
use App\Models\Employee;
use App\Models\Pensioner;
use App\Models\Retirement;

class SyncPensioners extends Command
{
    protected $signature = 'sync:pensioners';
    protected $description = 'Sync missing pensioners from computation beneficiaries';

    public function handle()
    {
        $beneficiaries = ComputeBeneficiary::all();
        $this->info("Found " . $beneficiaries->count() . " beneficiaries.");

        foreach ($beneficiaries as $beneficiary) {
            $employee = Employee::where('employee_id', $beneficiary->id_no)->first();
            if (!$employee) {
                $employee = Employee::where('staff_no', $beneficiary->id_no)->first();
            }

            if ($employee) {
                $retirement = Retirement::firstOrCreate(
                    ['employee_id' => $employee->employee_id],
                    [
                        'retirement_date' => $beneficiary->dod_r,
                        'notification_date' => now(),
                        'gratuity_amount' => $beneficiary->gratuity_amt,
                        'status' => 'Approved',
                        'retire_reason' => 'Statutory',
                        'years_of_service' => $beneficiary->service_yrs_for_compute,
                    ]
                );

                // Check if Pensioner exists by employee_id or beneficiary_computation_id
                $pensioner = Pensioner::where('employee_id', $employee->employee_id)
                                      ->orWhere('beneficiary_computation_id', $beneficiary->id)
                                      ->first();

                if (!$pensioner) {
                    $this->info("Creating Pensioner for {$employee->full_name}...");
                    try {
                        Pensioner::create([
                            'employee_id' => $employee->employee_id,
                            'full_name' => $employee->full_name,
                            'surname' => $employee->surname,
                            'first_name' => $employee->first_name,
                            'middle_name' => $employee->middle_name,
                            'email' => $employee->email,
                            'phone_number' => $employee->mobile_no,
                            'date_of_birth' => $beneficiary->dob,
                            'place_of_birth' => null,
                            'date_of_first_appointment' => $beneficiary->appt_date,
                            'date_of_retirement' => $beneficiary->dod_r,
                            'retirement_reason' => $retirement->retire_reason,
                            'retirement_type' => $beneficiary->gtype,
                            'department_id' => $beneficiary->deptid,
                            'rank_id' => $beneficiary->rankid,
                            'step_id' => $beneficiary->stepid,
                            'grade_level_id' => $employee->grade_level_id,
                            'salary_scale_id' => $beneficiary->salary_scale_id,
                            'local_gov_area_id' => $beneficiary->lgaid,
                            'bank_id' => $beneficiary->bankid,
                            'account_number' => $beneficiary->acc_no,
                            'account_name' => $beneficiary->fulname,
                            'pension_amount' => $beneficiary->pension_per_mnth,
                            'gratuity_amount' => $beneficiary->gratuity_amt,
                            'total_death_gratuity' => $beneficiary->total_death_gratuity,
                            'years_of_service' => $beneficiary->service_yrs_for_compute,
                            'pension_percentage' => $beneficiary->pct_pension,
                            'gratuity_percentage' => $beneficiary->pct_gratuity,
                            'address' => $employee->address,
                            'next_of_kin_name' => $beneficiary->nxtkin_fulname,
                            'next_of_kin_phone' => $beneficiary->nxtkin_mobile,
                            'next_of_kin_address' => $employee->next_of_kin_address,
                            'status' => 'Active',
                            'retirement_id' => $retirement->id,
                            'beneficiary_computation_id' => $beneficiary->id,
                            'created_by' => 1,
                        ]);
                        $this->info("Success.");
                    } catch (\Exception $e) {
                         $this->error("Error creating: " . $e->getMessage());
                    }
                } else {
                    $this->info("Pensioner already exists for {$employee->full_name}. Checking link...");
                    if (!$pensioner->beneficiary_computation_id) {
                         $pensioner->update(['beneficiary_computation_id' => $beneficiary->id]);
                         $this->info("Linked beneficiary computation to existing pensioner.");
                    } else {
                         $this->info("Already linked.");
                    }
                }
            } else {
                $this->warn("Employee not found for Beneficiary ID {$beneficiary->id} (ID No: {$beneficiary->id_no})");
            }
        }
    }
}
