<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Retirement;

class RetirementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all employees with "Retired" status
        $retiredEmployees = Employee::where('status', 'Retired')->get();
        
        // Create retirement records for some of them
        foreach ($retiredEmployees->take(10) as $employee) {
            Retirement::create([
                'employee_id' => $employee->employee_id,
                'retirement_date' => now()->subYears(rand(1, 10)),
                'notification_date' => now()->subYears(rand(1, 10))->addMonths(rand(1, 6)),
                'gratuity_amount' => rand(100000, 500000),
                'status' => 'Completed',
            ]);
        }
    }
}
