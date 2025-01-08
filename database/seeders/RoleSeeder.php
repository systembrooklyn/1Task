<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // 1. Create (or find) the agent role
        $agentRole = Role::firstOrCreate(
            ['name' => 'agent', 'guard_name' => 'sanctum'],
            // You can add additional attributes if needed
        );

        // 2. Assign the needed permissions
        $permissions = ['report-dailytask', 'view-dailytask'];

        foreach ($permissions as $permissionName) {
            // Get the permission record
            $permission = Permission::where('name', $permissionName)->first();

            // Make sure it exists before assigning
            if ($permission) {
                $agentRole->givePermissionTo($permission);
            }
        }
    }
}
