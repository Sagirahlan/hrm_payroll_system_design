<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminRole = Role::where('name', 'admin')->first();

        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'username' => 'Admin User',
                'password_hash' => Hash::make('password'),
            ]
        );

        if ($adminRole) {
            $user->assignRole($adminRole);
        }
    }
}