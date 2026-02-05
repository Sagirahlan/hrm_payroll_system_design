<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use League\Csv\Writer;

class ExportEmployeesToCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employees:export-csv {--file= : Output file path} {--status= : Filter by employee status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export employee details to CSV file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Exporting employee details to CSV...');
        
        // Get file path from option or use default
        $filePath = $this->option('file') ?: storage_path('app/employees_export.csv');
        
        // Get status filter if provided
        $status = $this->option('status');
        
        // Query employees with relationships (keep any relations you need)
        $query = Employee::with([
            'department',
            'gradeLevel',
            'step',
            'appointmentType',
            'state',
            'lga',
            'ward',
            'bank'
        ]);
        
        // Apply status filter if provided
        if ($status) {
            $query->where('status', $status);
        }
        
        // Get employees
        $employees = $query->get();
        
        if ($employees->isEmpty()) {
            $this->warn('No employees found to export.');
            return Command::SUCCESS;
        }
        
        // Create CSV writer
        $csv = Writer::createFromPath($filePath, 'w');
        
        // Insert header in the requested order
        $csv->insertOne([
            'surname',
            'middle_name',
            'gender',
            'date_of_birth',
            'state_id',
            'lga_id',
            'ward_id',
            'nationality',
            'nin',
            'staff_no',
            'mobile_no',
            'email',
            'pay_point',
            'address',
            'date_of_first_appointment',
            'appointment_type_id',
            'status',
            'highest_certificate',
            'department_id',
            'cadre_id',
            'salary_scale_id',
            'grade_level_id',
            'step_id',
            'rank_id',
            'expected_next_promotion',
            'expected_retirement_date',
            'kin_name',
            'kin_relationship',
            'kin_mobile_no',
            'kin_address',
            'kin_occupation',
            'kin_place_of_work',
            'bank_name',
            'bank_code',
            'account_name',
            'account_no',
            'years_of_service',
            'contract_start_date',
            'contract_end_date',
            'amount',
            'first_name'
        ]);
        
        // Insert records in the same order
        foreach ($employees as $employee) {
            $csv->insertOne([
                $employee->surname,
                $employee->middle_name,
                $employee->gender,
                $employee->date_of_birth,
                $employee->state_id,
                $employee->lga_id,
                $employee->ward_id,
                $employee->nationality,
                $employee->nin,
                $employee->staff_no,
                $employee->mobile_no,
                $employee->email,
                $employee->pay_point,
                $employee->address,
                $employee->date_of_first_appointment,
                $employee->appointment_type_id,
                $employee->status,
                $employee->highest_certificate,
                $employee->department_id,
                $employee->cadre_id,
                $employee->salary_scale_id,
                $employee->grade_level_id,
                $employee->step_id,
                $employee->rank_id,
                $employee->expected_next_promotion,
                $employee->expected_retirement_date,
                $employee->kin_name,
                $employee->kin_relationship,
                $employee->kin_mobile_no,
                $employee->kin_address,
                $employee->kin_occupation,
                $employee->kin_place_of_work,
                optional($employee->bank)->bank_name ?? '',
                optional($employee->bank)->bank_code ?? '',
                optional($employee->bank)->account_name ?? '',
                optional($employee->bank)->account_no ?? '',
                $employee->years_of_service,
                $employee->contract_start_date,
                $employee->contract_end_date,
                $employee->amount,
                $employee->first_name
            ]);
        }
        
        $this->info("Successfully exported {$employees->count()} employees to: {$filePath}");
        
        return Command::SUCCESS;
    }
}

