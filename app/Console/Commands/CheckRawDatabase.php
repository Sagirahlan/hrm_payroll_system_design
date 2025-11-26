<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckRawDatabase extends Command
{
    protected $signature = 'check:raw-database';
    protected $description = 'Check raw database values for the test employee';

    public function handle()
    {
        $employee = DB::selectOne("
            SELECT 
                employee_id, 
                first_name, 
                surname, 
                staff_no, 
                on_probation, 
                probation_start_date, 
                probation_end_date, 
                probation_status, 
                status,
                appointment_type_id
            FROM employees 
            WHERE first_name = 'John' AND surname = 'Probation'"
        );

        if ($employee) {
            $this->info("Raw Database Values:");
            $this->info("- Employee ID: {$employee->employee_id}");
            $this->info("- Name: {$employee->first_name} {$employee->surname}");
            $this->info("- Staff No: {$employee->staff_no}");
            $this->info("- On Probation (DB): " . ($employee->on_probation ? 'Yes' : 'No'));
            $this->info("- Probation Start Date (DB): " . ($employee->probation_start_date ?: 'NULL'));
            $this->info("- Probation End Date (DB): " . ($employee->probation_end_date ?: 'NULL'));
            $this->info("- Probation Status (DB): {$employee->probation_status}");
            $this->info("- Status (DB): {$employee->status}");
            $this->info("- Appointment Type ID (DB): {$employee->appointment_type_id}");
        } else {
            $this->error("Employee not found in database");
        }
        
        return 0;
    }
}