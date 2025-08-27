<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateEmployeesWithProperIdsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all employees
        $employees = DB::table('employees')->get();
        
        // Get all state, lga, and ward IDs
        $stateIds = DB::table('states')->pluck('state_id')->toArray();
        $lgaIds = DB::table('lgas')->pluck('id')->toArray();
        $wardIds = DB::table('wards')->pluck('ward_id')->toArray();
        
        // Update each employee with random but valid state, lga, and ward IDs
        foreach ($employees as $employee) {
            DB::table('employees')
                ->where('employee_id', $employee->employee_id)
                ->update([
                    'state_id' => $stateIds[array_rand($stateIds)],
                    'lga_id' => $lgaIds[array_rand($lgaIds)],
                    'ward_id' => $wardIds[array_rand($wardIds)],
                    'updated_at' => now()
                ]);
        }
        
        echo "Updated " . count($employees) . " employees with proper state, LGA, and ward IDs.\n";
    }
}