<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PopulatePayrollSnapshots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payroll:populate-snapshots';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate missing snapshot fields (step_id, rank_id, department_id) for existing payroll records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $records = \App\Models\PayrollRecord::whereNull('step_id')
            ->whereNull('department_id')
            ->with(['employee', 'employee.pensioner'])
            ->get();

        if ($records->isEmpty()) {
            $this->info('No records need populating.');
            return;
        }

        $this->info("Found {$records->count()} records to update.");
        $bar = $this->output->createProgressBar($records->count());
        $bar->start();

        foreach ($records as $record) {
            $employee = $record->employee;
            if ($employee) {
                // Determine values from Employee model or Pensioner link
                $stepId = $employee->step_id;
                $rankId = $employee->rank_id;
                $deptId = $employee->department_id;

                // For pensioners, try to get from pensioner table if missing on employee
                if ($record->payment_type === 'Pension' || $record->payment_type === 'Gratuity') {
                    if ($employee->pensioner) {
                        $stepId = $stepId ?? $employee->pensioner->step_id;
                        $rankId = $rankId ?? $employee->pensioner->rank_id;
                        $deptId = $deptId ?? $employee->pensioner->department_id;
                    }
                }

                $record->update([
                    'step_id'       => $stepId,
                    'rank_id'       => $rankId,
                    'department_id' => $deptId,
                ]);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Payroll snapshots populated successfully!');
    }
}
