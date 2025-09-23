<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Step;

class EmployeeStepAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all employees that have a grade level, with gradeLevel relationship loaded
        $employees = Employee::with('gradeLevel')->whereNotNull('grade_level_id')->get();
        
        foreach ($employees as $employee) {
            // Get steps for this employee's grade level
            if ($employee->gradeLevel) {
                $steps = $employee->gradeLevel->steps;
                
                if ($steps->count() > 0) {
                    // Randomly assign a step to the employee
                    $randomStep = $steps->random();
                    $employee->step_id = $randomStep->id;
                    $employee->save();
                }
            }
        }
    }
}
