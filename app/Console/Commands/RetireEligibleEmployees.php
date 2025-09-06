<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\Retirement;
use App\Models\Pensioner;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // Added Log facade

class RetireEligibleEmployees extends Command
{
    protected $signature = 'employees:retire-eligible';
    protected $description = 'Automatically retire employees who have reached the retirement age or maximum years of service.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Checking for eligible employees to retire...');
        Log::info('Running RetireEligibleEmployees command...');

        $employees = Employee::with('gradeLevel.salaryScale')
            ->where('status', 'Active')
            ->get();

        Log::info('Found ' . $employees->count() . ' active employees.');

        foreach ($employees as $employee) {
            Log::info("Checking employee ID: {$employee->employee_id}");
            if ($this->isEligibleForRetirement($employee)) {
                $this->retireEmployee($employee);
                $this->info("Retired employee: {$employee->first_name} {$employee->surname} (ID: {$employee->employee_id})");
                Log::info("Retired employee ID: {$employee->employee_id}");
            } else {
                Log::info("Employee ID {$employee->employee_id} is not eligible for retirement.");
            }
        }

        $this->info('Eligible employees have been retired successfully.');
        Log::info('Finished RetireEligibleEmployees command.');
    }

    private function isEligibleForRetirement(Employee $employee)
    {
        if (!$employee->gradeLevel || !$employee->gradeLevel->salaryScale) {
            Log::info("Employee ID {$employee->employee_id}: No grade level or salary scale.");
            return false;
        }

        $retirementAge = (int) $employee->gradeLevel->salaryScale->max_retirement_age;
        $yearsOfService = (int) $employee->gradeLevel->salaryScale->max_years_of_service;

        $age = Carbon::parse($employee->date_of_birth)->age;
        $serviceDuration = Carbon::parse($employee->date_of_first_appointment)->diffInYears(Carbon::now());

        Log::info("Employee ID {$employee->employee_id}: DOB={$employee->date_of_birth}, DFA={$employee->date_of_first_appointment}, RA={$retirementAge}, YOS={$yearsOfService}, Age={$age}, ServiceDuration={$serviceDuration}");

        $isEligible = $age >= $retirementAge || $serviceDuration >= $yearsOfService;
        Log::info("Employee ID {$employee->employee_id}: Is eligible? " . ($isEligible ? 'Yes' : 'No'));

        return $isEligible;
    }

    private function retireEmployee(Employee $employee)
    {
        // Update employee status
        $employee->update(['status' => 'Retired']);

        // Create retirement record
        Retirement::create([
            'employee_id' => $employee->employee_id,
            'retirement_date' => now()->toDateString(),
            'status' => 'Completed',
            'notification_date' => now(),
            'gratuity_amount' => $this->calculateGratuity($employee),
        ]);

        // Create pensioner record
        Pensioner::create([
            'employee_id' => $employee->employee_id,
            'pension_start_date' => now()->toDateString(),
            'pension_amount' => $this->calculatePension($employee),
            'status' => 'Active',
        ]);
    }

    private function calculateGratuity(Employee $employee)
    {
        $lastPayroll = \App\Models\PayrollRecord::where('employee_id', $employee->employee_id)
            ->orderByDesc('created_at')
            ->first();

        if (!$lastPayroll || !$employee->date_of_first_appointment) {
            return 0;
        }

        $retirementDate = now();
        $dateOfFirstAppointment = Carbon::parse($employee->date_of_first_appointment);

        if ($retirementDate->lessThanOrEqualTo($dateOfFirstAppointment)) {
            return 0;
        }

        $yearsOfService = $dateOfFirstAppointment->diffInYears($retirementDate);
        if ($dateOfFirstAppointment->copy()->addYears($yearsOfService)->lt($retirementDate)) {
            $yearsOfService += 1;
        }

        if ($yearsOfService < 1) {
            return 0;
        }

        $salary = $lastPayroll->basic_salary;
        $gratuity = $salary * 0.1 * $yearsOfService;

        return round($gratuity, 2);
    }

    private function calculatePension(Employee $employee)
    {
        $lastPayroll = \App\Models\PayrollRecord::where('employee_id', $employee->employee_id)->latest()->first();
        return $lastPayroll ? ($lastPayroll->basic_salary * 0.5) : 0;
    }
}