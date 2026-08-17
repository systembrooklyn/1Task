<?php

namespace App\Modules\RolePermission\Repositories\Contracts;

use App\Models\Role;

interface RoleRepositoryInterface
{
    public function findById(int $id): ?Role;
    public function findByCompany(int $companyId);
    public function findActiveByCompany(int $companyId);
    public function findByNameAndCompany(string $name, int $companyId): ?Role;
    public function create(array $data): Role;
    public function update(Role $role, array $data): Role;
    public function softDelete(Role $role): void;
    public function syncPermissions(Role $role, array $permissionIds): void;
    public function detachUsers(Role $role): void;
}