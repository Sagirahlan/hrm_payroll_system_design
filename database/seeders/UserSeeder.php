<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $adminRole = Role::where('name', 'admin')->first();

        // Existing admin (already in DB)
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'username' => 'Admin User',
                'password_hash' => Hash::make('M8Q5L7A2R9X4K6B3D1Z'),
            ]
        );

        if ($adminRole) {
            $admin->assignRole($adminRole);
        }

        // New user (will only be inserted if not exists)
        $newUser = User::firstOrCreate(
            ['email' => 'sagirahlan00@gmail.com'],
            [
                'username' => 'sagirahlan',
                'password_hash' => Hash::make('12345678'),
            ]
        );

        if ($adminRole) {
            $newUser->assignRole($adminRole); // optional
        }
    }
}
