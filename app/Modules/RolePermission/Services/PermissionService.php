<?php

namespace App\Modules\RolePermission\Services;

use App\Models\Permission;
use App\Modules\RolePermission\Repositories\Contracts\PermissionRepositoryInterface;

class PermissionService implements PermissionServiceInterface
{
    protected PermissionRepositoryInterface $permissionRepo;

    public function __construct(PermissionRepositoryInterface $permissionRepo)
    {
        $this->permissionRepo = $permissionRepo;
    }

    public function all()
    {
        return $this->permissionRepo->all();
    }

    public function find(int $id): Permission
    {
        $permission = $this->permissionRepo->findById($id);
        if (!$permission) {
            abort(404, 'Permission not found');
        }
        return $permission;
    }
}
