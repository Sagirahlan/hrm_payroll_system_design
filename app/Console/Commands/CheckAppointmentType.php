<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AppointmentType;

class CheckAppointmentType extends Command
{
    protected $signature = 'check:appointment-type';
    protected $description = 'Check the appointment type to verify it is Permanent';

    public function handle()
    {
        $appointmentType = AppointmentType::find(1);
        
        if ($appointmentType) {
            $this->info("Appointment Type Details:");
            $this->info("- ID: {$appointmentType->id}");
            $this->info("- Name: {$appointmentType->name}");
            $this->info("- Description: {$appointmentType->description}");
            
            // Check if it's different from 'Contract'
            $isNotContract = $appointmentType->name !== 'Contract';
            $this->info("- Is not Contract: " . ($isNotContract ? 'Yes' : 'No'));
        } else {
            $this->error("Appointment type with ID 1 not found.");
        }
        
        // Show all appointment types
        $allTypes = AppointmentType::all();
        $this->info("\nAll Appointment Types:");
        foreach ($allTypes as $type) {
            $this->info("- ID: {$type->id}, Name: {$type->name}");
        }
        
        return 0;
    }
}