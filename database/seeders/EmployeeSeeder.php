<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        Employee::create([
            'first_name' => 'John',
            'surname' => 'Doe',
            'middle_name' => 'Michael',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'state_of_origin' => 'Zamfara',
            'lga' => 'Gusau',
            'nationality' => 'Nigerian',
            'nin' => Str::random(11),
            'registration_no' => Str::random(10),
            'mobile_no' => '08012345678',
            'email' => 'johndoe@example.com',
            'address' => '123 Main Street',
            'date_of_first_appointment' => '2015-06-01',
            'cadre_id' => 1,
            'salary_structure_id' => 1,
            'department_id' => 1,
            'expected_next_promotion' => '2026-06-01',
            'expected_retirement_date' => '2045-06-01',
            'status' => 'Active',
            'highest_certificate' => 'B.Sc. Computer Science',
            'grade_level_limit' => 14,
            'appointment_type_id' => 1,
            'photo_path' => 'photos/employees/john_doe.jpg',
        ]);
    }
}