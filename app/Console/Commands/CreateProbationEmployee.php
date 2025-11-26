<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\AppointmentType;
use App\Models\PendingEmployeeChange;
use App\Models\State;
use App\Models\Lga;
use App\Models\Department;
use Carbon\Carbon;

class CreateProbationEmployee extends Command
{
    protected $signature = 'create:probation-employee';
    protected $description = 'Create a sample probation employee to test the system';

    public function handle()
    {
        $this->info('Creating a sample probation employee...');
        
        // Get required reference data
        $permanentType = AppointmentType::where('name', 'Permanent')->first();
        if (!$permanentType) {
            $this->error('Permanent appointment type not found. Creating it...');
            $permanentType = AppointmentType::create([
                'name' => 'Permanent',
                'description' => 'Permanent employee appointment'
            ]);
        }

        $state = State::first();
        if (!$state) {
            $this->error('No state found in database. Please seed your database.');
            return 1;
        }

        $lga = Lga::first();
        if (!$lga) {
            $this->error('No LGA found in database. Please seed your database.');
            return 1;
        }

        $department = Department::first();
        if (!$department) {
            $this->error('No department found in database. Please seed your database.');
            return 1;
        }
        
        // Create pending employee change (simulating a new hire request)
        $pendingChange = PendingEmployeeChange::create([
            'employee_id' => null, // Null for new employee
            'change_type' => 'create',
            'status' => 'pending',
            'requested_by' => 1, // Assuming user ID 1 is available
            'data' => [
                'first_name' => 'John',
                'surname' => 'Probation',
                'middle_name' => 'Michael',
                'gender' => 'Male',
                'date_of_birth' => '1990-01-01',
                'state_id' => $state->state_id,
                'lga_id' => $lga->id,
                'nationality' => 'Nigerian',
                'mobile_no' => '08112345678',
                'email' => 'john.probation.' . time() . '@example.com', // Unique email
                'address' => '123 Probation Street',
                'date_of_first_appointment' => now()->format('Y-m-d'),
                'department_id' => $department->department_id,
                'status' => 'Active',
                'appointment_type_id' => $permanentType->id,
                'staff_no' => 'PROB' . time(),
                'grade_level_id' => 1,
                'step_id' => 1,
                'salary_scale_id' => 1,
                'cadre_id' => 1,
                'banking_info' => 'Test Banking Info',
            ],
            'reason' => 'New employee hire',
        ]);

        $this->info("Pending employee change created: {$pendingChange->id}");
        $this->info("To place the employee on probation:");
        $this->info("1. Go to Pending Changes in the UI");
        $this->info("2. Find the pending change for John Probation");
        $this->info("3. Approve the change");
        $this->info("4. This will create the employee and automatically place them on 3-month probation");
        
        return 0;
    }
}