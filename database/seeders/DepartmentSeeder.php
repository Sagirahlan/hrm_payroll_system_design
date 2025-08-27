<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Department::create(['department_name' => 'Human Resources']);
        Department::create(['department_name' => 'Information Technology']);
        Department::create(['department_name' => 'Finance']);
    }
}