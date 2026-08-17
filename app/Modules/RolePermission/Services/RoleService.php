<?php

namespace App\Modules\RolePermission\Services;

use App\Exceptions\ResourceDeletedException;
use App\Models\Role;
use App\Services\PlanLimitService;
use Illuminate\Validation\ValidationException;
use App\Modules\RolePermission\Repositories\Contracts\RoleRepositoryInterface;
use App\Modules\RolePermission\Repositories\Contracts\PermissionRepositoryInterface;

class RoleService implements RoleServiceInterface
{
    protected RoleRepositoryInterface $roleRepo;
    protected PermissionRepositoryInterface $permissionRepo;
    protected PlanLimitService $planService;

    public function __construct(
        RoleRepositoryInterface $roleRepo,
        PermissionRepositoryInterface $permissionRepo,
        PlanLimitService $planService
    ) {
        $this->roleRepo = $roleRepo;
        $this->permissionRepo = $permissionRepo;
        $this->planService = $planService;
    }

    public function createRole(array $data, int $companyId): Role
    {
        $this->planService->checkFeatureAccess($companyId, 'limit_role');

        $existing = $this->roleRepo->findByNameAndCompany($data['name'], $companyId);
        if ($existing) {
            throw ValidationException::withMessages([
                'name' => "A role with the name {$existing->name} already exists in your company."
            ]);
        }

        $role = $this->roleRepo->create([
            'name'       => $data['name'],
            'company_id' => $companyId,
        ]);

        if (!empty($data['permissions'])) {
            $this->roleRepo->syncPermissions($role, $data['permissions']);
        }

        return $role->load('permissions');
    }

    public function getRoles(int $companyId)
    {
        return $this->roleRepo->findActiveByCompany($companyId)->load('permissions');
    }

    public function getRole(int $companyId, int $roleId): Role
    {
        $role = $this->roleRepo->findById($roleId);
        if (!$role || $role->company_id !== $companyId) {
            abort(404, 'Role not found or does not belong to your company.');
        }
        if ($role->is_deleted) {
            throw new ResourceDeletedException(
                'This Role has been deleted. Please contact support.',
                'Role Deleted'
            );
        }
        return $role->load('permissions');
    }

    public function updateRole(int $companyId, int $roleId, array $data): Role
    {
        $role = $this->getRole($companyId, $roleId);
        $this->roleRepo->update($role, ['name' => $data['name']]);
        $this->roleRepo->syncPermissions($role, $data['permissions']);
        return $role;
    }

    public function deleteRole(int $companyId, int $roleId): void
    {
        $role = $this->getRole($companyId, $roleId);
        $this->roleRepo->softDelete($role);
        $this->roleRepo->detachUsers($role);
    }

    public function assignPermissions(int $roleId, array $permissionIds): void
    {
        $role = $this->roleRepo->findById($roleId);
        if (!$role || $role->is_deleted) {
            throw new ResourceDeletedException(
                'This Role has been deleted. Please contact support.',
                'Role Deleted'
            );
        }
        $this->roleRepo->syncPermissions($role, $permissionIds);
    }

    public function getRolePermissions(int $roleId): Role
    {
        $role = $this->roleRepo->findById($roleId);
        if (!$role || $role->is_deleted) {
            throw new ResourceDeletedException(
                'This Role has been deleted. Please contact support.',
                'Role Deleted'
            );
        }
        return $role->load('permissions');
    }

    public function removePermissions(int $roleId, array $permissionIds, int $companyId): void
    {
        $role = $this->getRole($companyId, $roleId);
        foreach ($permissionIds as $permId) {
            $permission = $this->permissionRepo->findById($permId);
            if ($permission && $role->hasPermissionTo($permission)) {
                $role->revokePermissionTo($permission);
            }
        }
    }
}
