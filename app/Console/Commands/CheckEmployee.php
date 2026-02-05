<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;

class CheckEmployee extends Command
{
    protected $signature = 'check:employee-probation';
    protected $description = 'Check the probation status of the test employee';

    public function handle()
    {
        $employee = Employee::where('first_name', 'John')
                           ->where('surname', 'Probation')
                           ->first();

        if ($employee) {
            $this->info("Employee found:");
            $this->info("- ID: " . $employee->employee_id);
            $this->info("- Name: {$employee->first_name} {$employee->surname}");
            $this->info("- Staff No: {$employee->staff_no}");
            $this->info("- Appointment Type ID: {$employee->appointment_type_id}");
            $this->info("- On Probation: " . ($employee->on_probation ? 'Yes' : 'No'));
            $this->info("- Probation Status: {$employee->probation_status}");
            $this->info("- Probation Start: {$employee->probation_start_date}");
            $this->info("- Probation End: {$employee->probation_end_date}");
            
            // Get the appointment type to verify if it's permanent
            $appointmentType = \App\Models\AppointmentType::find($employee->appointment_type_id);
            if ($appointmentType) {
                $this->info("- Appointment Type: {$appointmentType->name}");
                
                // Check if it should have been placed on probation
                if ($appointmentType->name !== 'Casual') {
                    $this->info("- This employee should have been placed on probation as their appointment type is: {$appointmentType->name}");
                } else {
                    $this->info("- This is a Casual employee, which is why they're not on probation");
                }
            }
        } else {
            $this->error("Employee with name John Probation not found.");
        }
        
        return 0;
    }
}

