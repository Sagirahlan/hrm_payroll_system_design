<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            DepartmentSeeder::class,
            CadreSeeder::class,
            SalaryScaleSeeder::class,
            SalaryScaleGradeLevelSeeder::class,
            StateSeeder::class,
            LgaSeeder::class,
            WardSeeder::class,
            UserSeeder::class,
            EmployeesTableSeeder::class,
            DeductionTypesSeeder::class,
            NonStatutoryItemsSeeder::class,
            AppointmentTypeSeeder::class,
        ]);
    }
}