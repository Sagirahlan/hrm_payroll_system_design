<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class CheckProbationFields extends Command
{
    protected $signature = 'check:probation-fields';
    protected $description = 'Check if probation fields exist in employees table';

    public function handle()
    {
        $columns = Schema::getColumnListing('employees');
        
        $probationFields = [
            'on_probation',
            'probation_start_date',
            'probation_end_date',
            'probation_status',
            'probation_notes'
        ];
        
        $this->info('Checking for probation fields in employees table...');
        
        $missingFields = [];
        foreach ($probationFields as $field) {
            if (in_array($field, $columns)) {
                $this->info("✓ Field '{$field}' exists");
            } else {
                $this->error("✗ Field '{$field}' is missing");
                $missingFields[] = $field;
            }
        }
        
        if (empty($missingFields)) {
            $this->info("\n✓ All probation fields exist in the database!");
        } else {
            $this->error("\n✗ Missing probation fields: " . implode(', ', $missingFields));
        }
        
        return 0;
    }
}