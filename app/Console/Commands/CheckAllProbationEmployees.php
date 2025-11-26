<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;

class CheckAllProbationEmployees extends Command
{
    protected $signature = 'check:all-probation-employees';
    protected $description = 'Check all employees for probation consistency';

    public function handle()
    {
        $this->info('Checking all employees for probation status consistency...');
        
        // Get all employees who might be on probation
        $employees = Employee::where(function($query) {
            $query->where('on_probation', true)
                  ->orWhere('probation_status', '!=', null)
                  ->orWhere('status', 'On Probation');
        })
        ->orderBy('created_at', 'desc')
        ->get(['employee_id', 'first_name', 'surname', 'on_probation', 'probation_status', 'probation_start_date', 'probation_end_date', 'status', 'date_of_first_appointment']);

        if ($employees->count() > 0) {
            $this->info("Found {$employees->count()} employees with probation-related data:");
            $this->table([
                'ID',
                'Name',
                'On Probation',
                'Probation Status',
                'Probation Start',
                'Probation End',
                'Employee Status',
                'Date of First Appointment'
            ], $employees->map(function ($emp) {
                return [
                    $emp->employee_id,
                    $emp->first_name . ' ' . $emp->surname,
                    $emp->on_probation ? 'Yes' : 'No',
                    $emp->probation_status ?: 'NULL',
                    $emp->probation_start_date ?: 'NULL',
                    $emp->probation_end_date ?: 'NULL',
                    $emp->status,
                    $emp->date_of_first_appointment ?: 'NULL'
                ];
            }));
        } else {
            $this->info("No employees found with probation-related data.");
        }
        
        // Also get the total count of employees on probation
        $totalOnProbation = Employee::where('on_probation', true)->count();
        $this->info("\nTotal employees marked as 'on_probation': {$totalOnProbation}");
        
        return 0;
    }
}