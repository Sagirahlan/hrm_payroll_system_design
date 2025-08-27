<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SalaryScale;

class SalaryScaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SalaryScale::create(['scale_name' => 'Grade 1', 'basic_salary' => 50000, 'grade_level' => '1', 'step_level' => '1']);
        SalaryScale::create(['scale_name' => 'Grade 2', 'basic_salary' => 75000, 'grade_level' => '2', 'step_level' => '1']);
        SalaryScale::create(['scale_name' => 'Grade 3', 'basic_salary' => 100000, 'grade_level' => '3', 'step_level' => '1']);
    }
}
