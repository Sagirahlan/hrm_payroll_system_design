<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckPromotionPermissions extends Command
{
    protected $signature = 'check:promotion-permissions';
    protected $description = 'Check all promotion-related permissions in the system';

    public function handle()
    {
        $this->info('Checking promotion-related permissions...');
        
        // Get all permissions that relate to promotions
        $promotionPermissions = DB::table('permissions')
            ->where('name', 'like', '%promotion%')
            ->orWhere('name', 'like', '%demotion%')
            ->get();

        if ($promotionPermissions->count() > 0) {
            $this->info("\nFound " . $promotionPermissions->count() . " promotion/demotion related permissions:");
            foreach ($promotionPermissions as $perm) {
                $this->info("- {$perm->name} (ID: {$perm->id}) - {$perm->guard_name}");
            }
        } else {
            $this->info("\nNo promotion/demotion permissions found in the database.");
            
            // Check all permissions to see what's available
            $allPermissions = DB::table('permissions')->get();
            $this->info("\nAll permissions in the system:");
            foreach ($allPermissions as $perm) {
                if (strpos(strtolower($perm->name), 'promotion') !== false || 
                    strpos(strtolower($perm->name), 'demotion') !== false) {
                    $this->info("- {$perm->name} (ID: {$perm->id}) - {$perm->guard_name}");
                }
            }
        }
        
        // Check roles that might have promotion permissions
        $roles = DB::table('roles')
            ->join('role_has_permissions', 'roles.id', '=', 'role_has_permissions.role_id')
            ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->where('permissions.name', 'like', '%promotion%')
            ->orWhere('permissions.name', 'like', '%demotion%')
            ->select('roles.name as role_name', 'permissions.name as permission_name')
            ->distinct()
            ->get();

        if ($roles->count() > 0) {
            $this->info("\nRoles with promotion/demotion permissions:");
            foreach ($roles as $role) {
                $this->info("- Role: {$role->role_name} has permission: {$role->permission_name}");
            }
        } else {
            $this->info("\nNo roles found with promotion/demotion permissions.");
        }
        
        return 0;
    }
}