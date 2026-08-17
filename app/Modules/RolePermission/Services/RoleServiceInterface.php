<?php

namespace App\Modules\RolePermission\Services;

use App\Models\Role;

interface RoleServiceInterface
{
    public function createRole(array $data, int $companyId): Role;
    public function getRoles(int $companyId);
    public function getRole(int $companyId, int $roleId): Role;
    public function updateRole(int $companyId, int $roleId, array $data): Role;
    public function deleteRole(int $companyId, int $roleId): void;
    public function assignPermissions(int $roleId, array $permissionIds): void;
    public function getRolePermissions(int $roleId): Role;
    public function removePermissions(int $roleId, array $permissionIds, int $companyId): void;
}