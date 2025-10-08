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

        // Calculate pension details based on RSA
        $pensionDetails = $this->calculatePensionDetails($employee);
        
        // Create pensioner record
        Pensioner::create([
            'employee_id' => $employee->employee_id,
            'pension_start_date' => now()->toDateString(),
            'pension_amount' => $pensionDetails['monthly_pension'],
            'rsa_balance_at_retirement' => $pensionDetails['rsa_balance'],
            'lump_sum_amount' => $pensionDetails['lump_sum'],
            'pension_type' => $pensionDetails['pension_type'],
            'expected_lifespan_months' => $pensionDetails['expected_lifespan_months'],
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

        // For Nigerian CPS: Gratuity = 100% of last gross annual emoluments
        // Gross emoluments = Basic salary + all allowances (housing, transport, etc.)
        $grossMonthlyEmoluments = $lastPayroll->basic_salary + $lastPayroll->total_additions;
        $grossAnnualEmoluments = $grossMonthlyEmoluments * 12; // Annual calculation

        // Nigerian CPS gratuity formula: 100% of last gross annual emoluments
        $gratuity = $grossAnnualEmoluments;

        return round($gratuity, 2);
    }

    private function calculatePension(Employee $employee)
    {
        $lastPayroll = \App\Models\PayrollRecord::where('employee_id', $employee->employee_id)->latest()->first();
        return $lastPayroll ? ($lastPayroll->basic_salary * 0.5) : 0;
    }
    
    private function calculatePensionDetails(Employee $employee)
    {
        // Get the last payroll record for the employee (by created_at, fallback to payroll_date)
        $lastPayroll = \App\Models\PayrollRecord::where('employee_id', $employee->employee_id)
            ->orderByDesc('created_at')
            ->first();

        if (!$lastPayroll || !$employee->rsa_balance) {
            return [
                'monthly_pension' => 0,
                'rsa_balance' => 0,
                'lump_sum' => 0,
                'pension_type' => 'PW',
                'expected_lifespan_months' => 240
            ];
        }

        $rsaBalance = $employee->rsa_balance;
        
        // Calculate lump sum (up to 25% of RSA balance)
        $lumpSum = min($rsaBalance * 0.25, $rsaBalance); // At most 25% of the balance or the whole balance if less than 25%
        
        // Calculate remaining RSA balance after lump sum for monthly payments
        $remainingRsaBalance = $rsaBalance - $lumpSum;
        
        // Default to Programmed Withdrawal (PW) method
        $pensionType = 'PW'; // Could be 'PW' for Programmed Withdrawal or 'Annuity'
        
        // Expected lifespan: default to 20 years (240 months) for PW calculation
        $expectedLifespanMonths = 240;
        
        // Monthly pension calculation based on Programmed Withdrawal method
        $monthlyPension = $remainingRsaBalance / $expectedLifespanMonths;
        
        // Ensure minimum pension guarantee as per Nigerian CPS (₦32,000/month as of Sept 2025)
        $minimumPension = 32000;
        $monthlyPension = max($monthlyPension, $minimumPension);
        
        return [
            'monthly_pension' => $monthlyPension,
            'rsa_balance' => $rsaBalance,
            'lump_sum' => $lumpSum,
            'pension_type' => $pensionType,
            'expected_lifespan_months' => $expectedLifespanMonths
        ];
    }
}