<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SetEmployeeProbation extends Command
{
    protected $signature = 'set:employee-probation';
    protected $description = 'Manually set the employee probation status in the database';

    public function handle()
    {
        // Update the employee to set probation fields properly
        $updated = DB::table('employees')
            ->where('first_name', 'John')
            ->where('surname', 'Probation')
            ->update([
                'on_probation' => true,
                'probation_start_date' => now(),
                'probation_end_date' => now()->addMonths(3),
                'probation_status' => 'pending',
                'status' => 'On Probation',
                'probation_notes' => 'Manually set to probation after creation via pending change approval'
            ]);

        if ($updated) {
            $this->info("✅ Employee probation status has been successfully updated in the database!");
            
            // Verify the changes
            $employee = DB::table('employees')
                ->select('on_probation', 'probation_start_date', 'probation_end_date', 'probation_status', 'status')
                ->where('first_name', 'John')
                ->where('surname', 'Probation')
                ->first();

            $this->info("\nVerification after update:");
            $this->info("- On Probation: " . ($employee->on_probation ? 'Yes' : 'No'));
            $this->info("- Probation Start Date: " . $employee->probation_start_date);
            $this->info("- Probation End Date: " . $employee->probation_end_date);
            $this->info("- Probation Status: " . $employee->probation_status);
            $this->info("- Employee Status: " . $employee->status);
        } else {
            $this->error("❌ No employee found with name John Probation to update.");
        }
        
        return 0;
    }
}