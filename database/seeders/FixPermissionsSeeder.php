<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FixPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create the permission
        $permission = Permission::firstOrCreate(['name' => 'master_data.manage', 'guard_name' => 'web']);
        
        // 2. Find or create the super admin role
        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        
        // 3. Assign
        $role->givePermissionTo($permission);
        
        // Output info
        $this->command->info('Permission master_data.manage created and assigned to super_admin.');
    }
}
