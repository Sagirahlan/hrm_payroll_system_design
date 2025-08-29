<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AssignAdminPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:assign-permission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign admin role to admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = User::where('username', 'admin')->first();
        
        if ($user) {
            $adminRole = Role::firstOrCreate(['name' => 'admin']);
            $user->assignRole($adminRole);
            $this->info('Admin role assigned to admin user successfully.');
        } else {
            $this->error('Admin user not found.');
        }
        
        return 0;
    }
}