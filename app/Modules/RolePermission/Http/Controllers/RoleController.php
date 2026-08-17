<?php

namespace App\Modules\RolePermission\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Exceptions\ResourceDeletedException;
use App\Modules\RolePermission\Http\Requests\CreateRoleRequest;
use App\Modules\RolePermission\Http\Requests\UpdateRoleRequest;
use App\Modules\RolePermission\Http\Requests\AssignPermissionsRequest;
use App\Modules\RolePermission\Http\Requests\RemovePermissionsRequest;
use App\Modules\RolePermission\Services\RoleServiceInterface;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    protected RoleServiceInterface $roleService;

    public function __construct(RoleServiceInterface $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index(): JsonResponse
    {
        $companyId = auth()->user()->company_id;
        $roles = $this->roleService->getRoles($companyId);

        // Original mapping: role_id, role_name, permissions array
        $rolesWithPermissions = $roles->map(function ($role) {
            return [
                'role_id'   => $role->id,
                'role_name' => $role->name,
                'permissions' => $role->permissions->map(function ($permission) {
                    return [
                        'permission_id'   => $permission->id,
                        'permission_name' => $permission->name,
                    ];
                }),
            ];
        });

        return response()->json($rolesWithPermissions);
    }

    public function store(CreateRoleRequest $request): JsonResponse
    {
        $companyId = auth()->user()->company_id;
        $role = $this->roleService->createRole($request->validated(), $companyId);

        // Apply the same hiding as original
        $role->permissions->makeHidden('pivot');
        $role->permissions->each->makeHidden('guard_name');
        $role->makeHidden('guard_name');

        return response()->json([
            'message' => 'Role created successfully',
            'role'    => $role,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $companyId = auth()->user()->company_id;
        $role = $this->roleService->getRole($companyId, $id);

        // Original mapping (same as index)
        $roleWithPermissions = [
            'role_id'   => $role->id,
            'role_name' => $role->name,
            'permissions' => $role->permissions->map(function ($permission) {
                return [
                    'permission_id'   => $permission->id,
                    'permission_name' => $permission->name,
                ];
            }),
        ];

        return response()->json($roleWithPermissions);
    }

    public function update(UpdateRoleRequest $request, int $id): JsonResponse
    {
        $companyId = auth()->user()->company_id;
        $role = $this->roleService->getRole($companyId, $id);
        $this->authorize('update', $role);

        $this->roleService->updateRole($companyId, $id, $request->validated());
        return response()->json(['message' => 'Role updated successfully']);
    }

    public function destroy(int $id): JsonResponse
    {
        $companyId = auth()->user()->company_id;
        $role = $this->roleService->getRole($companyId, $id);

        $this->authorize('delete', $role);

        $this->roleService->deleteRole($companyId, $id);
        return response()->json(['message' => 'Role deleted successfully']);
    }

    public function assignPermissions(AssignPermissionsRequest $request): JsonResponse
    {
        $this->roleService->assignPermissions($request->role_id, $request->permissions);

        return response()->json(['message' => 'Permissions assigned successfully']);
    }

    public function getRolePermissions(int $id): JsonResponse
    {
        $role = $this->roleService->getRolePermissions($id);

        // Original: returns role name + permissions (raw, no hiding)
        return response()->json([
            'role'        => $role->name,
            'permissions' => $role->permissions,
        ]);
    }

    public function removePermissions(RemovePermissionsRequest $request): JsonResponse
    {
        $companyId = auth()->user()->company_id;
        $this->roleService->removePermissions(
            $request->role_id,
            $request->permission_ids,
            $companyId
        );

        return response()->json(['message' => 'Permissions removed from role successfully.']);
    }
}
