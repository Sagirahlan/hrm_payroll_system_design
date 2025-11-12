<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EmployeesMultiSheetImport;

class ImportSampleDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:sample-data {--file=sample_employees_import.xlsx : The path to the Excel file to import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import the generated sample employee data';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting sample data import...');
        
        try {
            // Get the file path from option or use default
            $filePath = $this->option('file');
            
            // Check if the sample file exists
            if (!file_exists($filePath)) {
                $this->error("Sample data file not found: {$filePath}");
                $this->info('Please run the data generation script first or specify a valid file path.');
                return 1;
            }
            
            // Import the data using your existing import class
            Excel::import(new EmployeesMultiSheetImport, $filePath);
            
            $this->info('Sample data imported successfully!');
            $this->info('2000 employees and their related data have been imported.');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            return 1;
        }
    }
}