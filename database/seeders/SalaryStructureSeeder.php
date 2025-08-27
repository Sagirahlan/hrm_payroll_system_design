<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SalaryStructure;

class SalaryStructureSeeder extends Seeder
{
    public function run(): void
    {
        SalaryStructure::create([
            'structure_name' => 'Default Salary Structure',
        ]);
    }
}
