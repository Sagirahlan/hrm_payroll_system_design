<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // Create permissions for all actions in the application
        $permissions = [
            // Employee Management
            'view_employees',
            'create_employees',
            'edit_employees',
            'delete_employees',
            'manage_employees', // existing - for backward compatibility
            
            // Department Management
            'view_departments',
            'create_departments',
            'edit_departments',
            'delete_departments',
            'manage_departments', // existing - for backward compatibility
            
            // Payroll Management
            'view_payroll',
            'create_payroll',
            'edit_payroll',
            'delete_payroll',
            'manage_payroll', // existing - for backward compatibility
            'generate_payroll',
            'approve_payroll',
            'view_payslips',
            'manage_payroll_adjustments',
            
            // User Management
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'manage_users', // existing - for backward compatibility
            'reset_user_passwords',
            
            // Biometric Management
            'view_biometrics',
            'create_biometrics',
            'edit_biometrics',
            'delete_biometrics',
            'manage_biometrics', // existing - for backward compatibility
            
            // Disciplinary Management
            'view_disciplinary',
            'create_disciplinary',
            'edit_disciplinary',
            'delete_disciplinary',
            'manage_disciplinary', // existing - for backward compatibility
            
            // SMS Management
            'view_sms',
            'send_sms',
            'manage_sms', // existing - for backward compatibility
            
            // Retirement Management
            'view_retirement',
            'create_retirement',
            'edit_retirement',
            'delete_retirement',
            'manage_retirement', // existing - for backward compatibility
            
            // Report Management
            'view_reports',
            'generate_reports',
            'manage_reports', // existing - for backward compatibility
            
            // Audit Trail Management
            'view_audit_logs',
            
            // Employee Change Management
            'approve_employee_changes',
            'manage_employee_changes',
            'view_pending_employee_changes',
            'approve_pending_employee_changes',
            'reject_pending_employee_changes',
            
            // Deduction Management
            'view_deductions',
            'create_deductions',
            'edit_deductions',
            'delete_deductions',
            'manage_deductions',
            
            // Addition Management
            'view_additions',
            'create_additions',
            'edit_additions',
            'delete_additions',
            'manage_additions',
            
            // Pensioner Management
            'view_pensioners',
            'create_pensioners',
            'edit_pensioners',
            'delete_pensioners',
            'manage_pensioners',
            
            // Role Management
            'view_roles',
            'create_roles',
            'edit_roles',
            'delete_roles',
            'manage_roles',
            
            // Grade Level Management
            'view_grade_levels',
            'create_grade_levels',
            'edit_grade_levels',
            'delete_grade_levels',
            'manage_grade_levels',
            
            // Salary Scale Management
            'view_salary_scales',
            'create_salary_scales',
            'edit_salary_scales',
            'delete_salary_scales',
            'manage_salary_scales',
            
            // Cadre Management
            'view_cadres',
            'create_cadres',
            'edit_cadres',
            'delete_cadres',
            'manage_cadres',
            
            // Designation Management
            'view_designations',
            'create_designations',
            'edit_designations',
            'delete_designations',
            'manage_designations',
            
            // Addition Type Management
            'view_addition_types',
            'create_addition_types',
            'edit_addition_types',
            'delete_addition_types',
            'manage_addition_types',
            
            // Deduction Type Management
            'view_deduction_types',
            'create_deduction_types',
            'edit_deduction_types',
            'delete_deduction_types',
            'manage_deduction_types',

            // Payment Management
            'view_payments',
            'create_payments',
            'edit_payments',
            'delete_payments',
            'manage_payments',

            // Promotion Management
            'view_promotions',
            'create_promotions',
            'edit_promotions',
            'delete_promotions',
            'manage_promotions',

            // Profile Management
            'view_profile',
            'edit_profile',
            'change_password',

            // Payroll Bulk Actions
            'bulk_send_payroll_for_review',
            'bulk_mark_payroll_as_reviewed',
            'bulk_send_payroll_for_approval',
            'bulk_final_approve_payroll',
            'bulk_update_payroll_status',

            // Loan Management
            'view_loans',
            'create_loans',
            'edit_loans',
            'delete_loans',
            'manage_loans',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $employeeRole = Role::firstOrCreate(['name' => 'employee']);
        // Assign limited permissions to employee role
        $employeeRole->givePermissionTo([
            'view_profile',
            'edit_profile',
            'change_password',
            'view_payroll',
            'view_payslips',
        ]);
    }
}