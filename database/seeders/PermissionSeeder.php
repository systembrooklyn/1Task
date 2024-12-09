<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(['name' => 'create-department']);
        Permission::create(['name' => 'edit-department']);
        Permission::create(['name' => 'delete-department']);
        Permission::create(['name' => 'view-department']);
        
        // Permissions for Projects
        Permission::create(['name' => 'create-project']);
        Permission::create(['name' => 'edit-project']);
        Permission::create(['name' => 'delete-project']);
        Permission::create(['name' => 'view-project']);
    }
}
