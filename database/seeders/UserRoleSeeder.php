<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserRole;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UserRole::create(['role_name' => 'admin']);
        UserRole::create(['role_name' => 'employee']);
    }
}
