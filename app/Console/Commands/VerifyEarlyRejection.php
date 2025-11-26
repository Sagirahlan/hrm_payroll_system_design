<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Services\ProbationService;
use Carbon\Carbon;

class VerifyEarlyRejection extends Command
{
    protected $signature = 'verify:early-rejection';
    protected $description = 'Verify that an employee can be rejected before probation ends';

    public function handle()
    {
        $this->info('Starting Early Rejection Verification...');

        // 1. Create Employee on Probation
        $employee = new Employee();
        $employee->first_name = 'Early';
        $employee->surname = 'Reject';
        $employee->employee_id = rand(100000, 999999);
        $employee->staff_no = 'EARLY' . rand(1000, 9999);
        $employee->on_probation = true;
        $employee->probation_start_date = Carbon::now(); // Started today
        $employee->probation_end_date = Carbon::now()->addMonths(3); // Ends in 3 months
        $employee->probation_status = 'pending';
        $employee->status = 'On Probation';
        
        // Fill required fields to avoid SQL errors
        $employee->grade_level_id = 1;
        $employee->step_id = 1;
        $employee->email = 'early' . rand(1000, 9999) . '@example.com';
        $employee->mobile_no = '080' . rand(10000000, 99999999);
        $employee->nationality = 'Nigerian';
        $employee->nin = '12345678901';
        $employee->pay_point = 'Headquarters';
        $employee->gender = 'Male';
        $employee->date_of_birth = '1990-01-01';
        $employee->date_of_first_appointment = Carbon::now()->format('Y-m-d');
        $employee->state_id = 1;
        $employee->lga_id = 1;
        $employee->ward_id = 1;
        $employee->rank_id = 1;
        $employee->department_id = 1;
        $employee->address = 'Test Address';
        $employee->appointment_type_id = 1;
        
        $employee->save();
        
        $this->info("Created Employee: {$employee->employee_id} (Probation ends: {$employee->probation_end_date})");
        
        // 2. Try to Reject
        $service = new \App\Services\ProbationService();
        $this->info("Attempting to reject...");
        
        try {
            $result = $service->rejectProbation($employee, 'Early rejection test');
            
            if ($result) {
                $this->info("PASS: Rejection successful.");
                
                $employee->refresh();
                if ($employee->probation_status === 'rejected' && $employee->status === 'Terminated') {
                    $this->info("PASS: Employee status updated correctly.");
                } else {
                    $this->error("FAIL: Employee status not updated correctly. Status: {$employee->status}, Probation Status: {$employee->probation_status}");
                }
            } else {
                $this->error("FAIL: Rejection returned false.");
            }
            
        } catch (\Exception $e) {
            $this->error("FAIL: Exception thrown: " . $e->getMessage());
        }

        // Cleanup
        $employee->delete();
        $this->info("Cleanup Done.");
        
        return 0;
    }
}
