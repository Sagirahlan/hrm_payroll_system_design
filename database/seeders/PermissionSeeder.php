<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            'manage_employees',
            'view_employees',
            'manage_departments',
            'manage_biometrics',
            'manage_disciplinary',
            'manage_sms',
            'manage_users',
            'manage_retirement',
            'view_retirement',
            'manage_payroll',
            'view_payroll',
            'view_audit_logs',
            'manage_reports',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $adminRole = Role::findByName('admin');
        $adminRole->givePermissionTo(Permission::all());
    }
}