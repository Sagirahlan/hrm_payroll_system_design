<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GradeLevel;
use App\Models\SalaryScale;

class GradeLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all salary scales
        $salaryScales = SalaryScale::all();

        // Define grade levels for each salary scale
        foreach ($salaryScales as $salaryScale) {
            // Example: Create grade levels 1 to 5 for each salary scale
            // In a real application, you would define the actual grade levels for each salary scale
            for ($i = 1; $i <= 5; $i++) {
                GradeLevel::create([
                    'name' => 'Grade ' . $i . ' - ' . $salaryScale->acronym,
                    'basic_salary' => 50000 + ($i * 10000), // Example salary calculation
                    'grade_level' => $i,
                    'step_level' => 1,
                    'salary_scale_id' => $salaryScale->id
                ]);
            }
        }
    }
}
