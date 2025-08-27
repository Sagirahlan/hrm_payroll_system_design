<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateEmployeesWithStateLgaWardIdsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing state, lga, and ward IDs
        $stateIds = DB::table('states')->pluck('state_id');
        $lgaIds = DB::table('lgas')->pluck('id');
        $wardIds = DB::table('wards')->pluck('ward_id');
        
        // If no records exist, create some sample ones
        if ($stateIds->isEmpty()) {
            $stateIds = collect([1, 2, 3, 4, 5]);
        }
        
        if ($lgaIds->isEmpty()) {
            $lgaIds = collect([1, 2, 3, 4, 5]);
        }
        
        if ($wardIds->isEmpty()) {
            $wardIds = collect([1, 2, 3, 4, 5]);
        }
        
        // Get all employees
        $employees = DB::table('employees')->get();
        
        // Update each employee with random state, lga, and ward IDs
        foreach ($employees as $employee) {
            DB::table('employees')
                ->where('employee_id', $employee->employee_id)
                ->update([
                    'state_id' => $stateIds->random(),
                    'lga_id' => $lgaIds->random(),
                    'ward_id' => $wardIds->random(),
                    'updated_at' => now()
                ]);
        }
    }
}