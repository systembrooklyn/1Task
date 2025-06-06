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
        $permissions = [
            'invite-user',
            'edit-user',
            'delete-user',
            'view-user',
            'create-role',
            'edit-role',
            'delete-role',
            'view-role',
            'create-department',
            'edit-department',
            'delete-department',
            'view-department',
            'create-project',
            'edit-project',
            'delete-project',
            'view-project',
            'create-task',
            'edit-task',
            'delete-task',
            'view-task',
            'create-dailytask',
            'edit-dailytask',
            'delete-dailytask',
            'view-dailytask',
            'view-alldailytask',
            'report-dailytask',
            'view-dashboard-owner',
            'view-dashboard',
            'view-dailyTaskReports',
            'view-Allproject',
            'view-alldailytaskevaluation',
            'view-dailytaskevaluation',
            'create-dailytaskevaluation',
            'edit-dailytaskevaluation',
            'delete-dailytaskevaluation',
            'view-chartReports'
        ];
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission,
        'guard_name' => 'sanctum']);
        }
    }
}
