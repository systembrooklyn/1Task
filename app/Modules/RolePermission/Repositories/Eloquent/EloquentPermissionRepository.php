<?php

namespace App\Modules\RolePermission\Repositories\Eloquent;

use App\Models\Permission;
use App\Modules\RolePermission\Repositories\Contracts\PermissionRepositoryInterface;

class EloquentPermissionRepository implements PermissionRepositoryInterface
{
    public function all()
    {
        return Permission::all();
    }

    public function findById(int $id): ?Permission
    {
        return Permission::find($id);
    }
}