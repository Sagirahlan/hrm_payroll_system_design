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
        $departments = [
            ['id' => 1, 'name' => 'Administration'],
            ['id' => 2, 'name' => 'Administration and Supply'],
            ['id' => 3, 'name' => 'Ajiwa Water Treatment Plant'],
            ['id' => 4, 'name' => 'Audit'],
            ['id' => 5, 'name' => 'Batagarawa District'],
            ['id' => 6, 'name' => 'Commercial'],
            ['id' => 7, 'name' => 'Finance and Accounts'],
            ['id' => 8, 'name' => 'Funtua District'],
            ['id' => 9, 'name' => 'Headquarters'],
            ['id' => 10, 'name' => 'Jibia District'],
            ['id' => 11, 'name' => 'Katsina District - West'],
            ['id' => 12, 'name' => 'Mashi District'],
            ['id' => 13, 'name' => 'Operations'],
            ['id' => 14, 'name' => 'Others'],
            ['id' => 15, 'name' => 'PPP'],
            ['id' => 16, 'name' => 'PRS'],
            ['id' => 17, 'name' => 'WO/M&E'],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(
                ['department_id' => $dept['id']],
                ['department_name' => $dept['name']]
            );
        }
    }
}