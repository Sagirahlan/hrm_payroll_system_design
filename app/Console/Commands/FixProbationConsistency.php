<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use Carbon\Carbon;

class FixProbationConsistency extends Command
{
    protected $signature = 'fix:probation-consistency';
    protected $description = 'Fix probation data inconsistencies in employee records';

    public function handle()
    {
        $this->info('Fixing probation data inconsistencies...');
        
        // Find employees that have inconsistent probation data
        $inconsistentEmployees = Employee::where(function($query) {
            // Employees with status = 'On Probation' but on_probation = false
            $query->where('status', 'On Probation')
                  ->where('on_probation', false);
        })
        ->orWhere(function($query) {
            // Employees with probation_status = 'pending' but missing dates
            $query->where('probation_status', 'pending')
                  ->whereNull('probation_start_date')
                  ->whereNull('probation_end_date');
        })
        ->get();

        $this->info("Found {$inconsistentEmployees->count()} employees with inconsistent probation data.");

        $fixedCount = 0;

        foreach ($inconsistentEmployees as $employee) {
            $needsUpdate = false;
            $updateData = [];

            // Case 1: Employee has status 'On Probation' but on_probation is false
            if ($employee->status == 'On Probation' && !$employee->on_probation) {
                $updateData['on_probation'] = true;
                $needsUpdate = true;
                $this->info("Fixing on_probation for employee: {$employee->first_name} {$employee->surname}");
            }

            // Case 2: Employee has probation_status = 'pending' but missing dates
            if ($employee->probation_status == 'pending' && 
                (is_null($employee->probation_start_date) || is_null($employee->probation_end_date))) {
                
                // Calculate probation start/end dates based on date of first appointment
                $probationStartDate = $employee->date_of_first_appointment ? 
                    Carbon::parse($employee->date_of_first_appointment) : 
                    Carbon::now();

                $probationEndDate = $probationStartDate->copy()->addMonths(3);

                $updateData['probation_start_date'] = $probationStartDate;
                $updateData['probation_end_date'] = $probationEndDate;
                $updateData['probation_notes'] = ($employee->probation_notes ?? '') . "\n" . 'Fixed: Probation dates set based on date of first appointment.';
                $needsUpdate = true;
                $this->info("Fixing probation dates for employee: {$employee->first_name} {$employee->surname}");
            }

            if ($needsUpdate) {
                $employee->update($updateData);
                $fixedCount++;
            }
        }

        $this->info("✅ Fixed probation data for {$fixedCount} employees.");
        
        // Now find employees that should be on probation (permanent employees with no salary) but aren't properly flagged
        $permanentProbationEmployees = Employee::where(function($query) {
            $query->whereHas('appointmentType', function($subQuery) {
                $subQuery->where('name', '!=', 'Contract');
            })
            ->orWhereDoesntHave('appointmentType');
        })
        ->where('status', 'Active') // Currently active but might need probation
        ->where(function($query) {
            $query->whereNull('on_probation')
                  ->orWhere('on_probation', false);
        })
        ->where(function($query) {
            // If they've been employed for less than 3 months, they should be on probation
            $query->whereRaw('DATEDIFF(?, date_of_first_appointment) < 90', [Carbon::now()->format('Y-m-d')])
                  ->orWhereNull('date_of_first_appointment');
        })
        ->get();

        $this->info("Found {$permanentProbationEmployees->count()} permanent employees who might need probation status update.");
        
        foreach ($permanentProbationEmployees as $employee) {
            // Check if this employee should be on probation
            if ($employee->date_of_first_appointment) {
                $dateOfHire = Carbon::parse($employee->date_of_first_appointment);
                $now = Carbon::now();
                
                // If hired less than 3 months ago and is a permanent employee, they should potentially be on probation
                if ($dateOfHire->diffInDays($now) < 90 && 
                    $employee->appointmentType && 
                    $employee->appointmentType->name !== 'Contract') {
                    
                    $this->info("Employee {$employee->first_name} {$employee->surname} is a new permanent hire (". $dateOfHire->diffInDays($now)." days) - may need probation setup.");
                }
            }
        }

        $this->info('Probation consistency fix completed!');
        
        return 0;
    }
}