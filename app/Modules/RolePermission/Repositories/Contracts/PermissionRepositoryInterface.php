<?php

namespace App\Modules\RolePermission\Repositories\Contracts;

use App\Models\Permission;

interface PermissionRepositoryInterface
{
    public function all();
    public function findById(int $id): ?Permission;
}