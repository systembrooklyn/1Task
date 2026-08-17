<?php

namespace App\Modules\RolePermission\Services;

use App\Models\Permission;

interface PermissionServiceInterface
{
    public function all();
    public function find(int $id): Permission;
}
