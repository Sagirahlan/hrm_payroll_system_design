<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Rank;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UpdateEmployeeRanksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::all();
        $ranks = Rank::all();

        foreach ($employees as $employee) {
            $employee->rank_id = $ranks->random()->id;
            $employee->save();
        }
    }
}
