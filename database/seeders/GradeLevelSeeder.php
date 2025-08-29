<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GradeLevel;

class GradeLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        GradeLevel::create(['name' => 'Grade 1', 'basic_salary' => 50000, 'grade_level' => '1', 'step_level' => '1']);
        GradeLevel::create(['name' => 'Grade 2', 'basic_salary' => 75000, 'grade_level' => '2', 'step_level' => '1']);
        GradeLevel::create(['name' => 'Grade 3', 'basic_salary' => 100000, 'grade_level' => '3', 'step_level' => '1']);
    }
}
