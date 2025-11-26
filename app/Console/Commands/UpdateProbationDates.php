<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use Carbon\Carbon;

class UpdateProbationDates extends Command
{
    protected $signature = 'update:probation-dates';
    protected $description = 'Update probation dates based on date of first appointment';

    public function handle()
    {
        $employee = Employee::where('first_name', 'John')
                           ->where('surname', 'Probation')
                           ->first();

        if (!$employee) {
            $this->error("Employee John Probation not found.");
            return 1;
        }
        
        $this->info("Updating probation dates for employee: {$employee->first_name} {$employee->surname}");
        
        // Get the date of first appointment (or use current date if not set)
        $dateOfFirstAppointment = $employee->date_of_first_appointment ? 
            Carbon::parse($employee->date_of_first_appointment) : 
            Carbon::now();
        
        $probationEndDate = $dateOfFirstAppointment->copy()->addMonths(3);
        
        $employee->update([
            'probation_start_date' => $dateOfFirstAppointment,
            'probation_end_date' => $probationEndDate,
            'probation_notes' => $employee->probation_notes . "\nUpdated: Probation start date based on date of first appointment (" . $dateOfFirstAppointment->format('Y-m-d') . ")"
        ]);
        
        $this->info("✅ Probation dates updated successfully!");
        $this->info("- Date of First Appointment: {$employee->date_of_first_appointment}");
        $this->info("- New Probation Start Date: {$employee->probation_start_date}");
        $this->info("- New Probation End Date: {$employee->probation_end_date}");
        $this->info("- Days until completion: " . $employee->getRemainingProbationDays());
        
        return 0;
    }
}