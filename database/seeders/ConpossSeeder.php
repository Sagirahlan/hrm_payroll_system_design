<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\GradeLevel;

class ConpossSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conposs_ranks = [
            ['name' => 'Police Recruit', 'basic_salary' => 9019, 'grade_level' => '1', 'step_level' => '1'],
            ['name' => 'Police Constable', 'basic_salary' => 47203, 'grade_level' => '2', 'step_level' => '1'],
            ['name' => 'Police Corporal', 'basic_salary' => 47914, 'grade_level' => '3', 'step_level' => '1'],
            ['name' => 'Police Sergeant', 'basic_salary' => 52256, 'grade_level' => '4', 'step_level' => '1'],
            ['name' => 'Sergeant Major', 'basic_salary' => 58674, 'grade_level' => '5', 'step_level' => '1'],
            ['name' => 'Cadet Inspector', 'basic_salary' => 73231, 'grade_level' => '6', 'step_level' => '1'],
            ['name' => 'Assistant Superintendent of Police (ASP)', 'basic_salary' => 141961, 'grade_level' => '7', 'step_level' => '1'],
            ['name' => 'Deputy Superintendent of Police (DSP)', 'basic_salary' => 159566, 'grade_level' => '8', 'step_level' => '1'],
            ['name' => 'Superintendent of Police (SP)', 'basic_salary' => 174547, 'grade_level' => '9', 'step_level' => '1'],
            ['name' => 'Chief Superintendent of Police (CSP)', 'basic_salary' => 185906, 'grade_level' => '10', 'step_level' => '1'],
            ['name' => 'Assistant Commissioner of Police (ACP)', 'basic_salary' => 198061, 'grade_level' => '11', 'step_level' => '1'],
            ['name' => 'Deputy Commissioner of Police (DCP)', 'basic_salary' => 260783, 'grade_level' => '12', 'step_level' => '1'],
            ['name' => 'Commissioner of Police (CP)', 'basic_salary' => 284873, 'grade_level' => '13', 'step_level' => '1'],
            ['name' => 'Assistant Inspector General of Police (AIG)', 'basic_salary' => 499751, 'grade_level' => '14', 'step_level' => '1'],
            ['name' => 'Deputy Inspector General of Police (DIG)', 'basic_salary' => 546572, 'grade_level' => '15', 'step_level' => '1'],
            ['name' => 'Inspector General of Police (IGP)', 'basic_salary' => 711450, 'grade_level' => '16', 'step_level' => '1'],
        ];

        foreach ($conposs_ranks as $rank) {
            GradeLevel::create($rank);
        }
    }
}