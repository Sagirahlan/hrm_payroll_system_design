<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;

class VerifyEmployeeProbation extends Command
{
    protected $signature = 'verify:employee-probation';
    protected $description = 'Verify that the employee probation status is properly set';

    public function handle()
    {
        $employee = Employee::where('first_name', 'John')
                           ->where('surname', 'Probation')
                           ->first();

        if ($employee) {
            $this->info("Employee Verification:");
            $this->info("ID: " . $employee->employee_id);
            $this->info("On Probation: " . ($employee->on_probation ? 'Yes' : 'No'));
            $this->info("Probation Start Date: " . ($employee->probation_start_date ?: 'NULL'));
            $this->info("Probation End Date: " . ($employee->probation_end_date ?: 'NULL'));
            $this->info("Probation Status: " . ($employee->probation_status ?: 'NULL'));
            $this->info("Status: " . $employee->status);
            
            if ($employee->on_probation && $employee->probation_start_date && $employee->probation_end_date) {
                $this->info("✅ Employee is properly on probation!");
            } else {
                $this->error("❌ Employee is not properly on probation.");
            }
        } else {
            $this->error("Employee not found.");
        }
        
        return 0;
    }
}