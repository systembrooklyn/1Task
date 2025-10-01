<?php

namespace App\Http\Controllers;

use App\Exceptions\ResourceDeletedException;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\PlanLimitService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RolePermissionController extends Controller
{
    protected $planService;

    public function __construct(PlanLimitService $planService)
    {
        $this->planService = $planService;
    }
    public function getPermissions()
    {
        $permissions = Permission::get();

        return response()->json($permissions);
    }

    public function getPermission($id)
    {
        $permission = Permission::findOrFail($id);

        return response()->json($permission);
    }


    public function createRole(Request $request)
    {
        $user = Auth::user();
        $this->planService->checkFeatureAccess($user->company_id, 'limit_role');
        $company_id = $user->company_id;
        $this->authorize('create', Role::class);
        $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $existingRole = Role::where('company_id', $company_id)
            ->where('name', $request->name)
            ->first();

        if ($existingRole) {
            return response()->json([
                'message' => "A role with the name {$existingRole->name} already exists in your company.",
            ], 422);
        }
        $role = Role::create([
            'name' => $request->name,
            'company_id' => $company_id,
        ]);
        if(!empty($request->permissions)){
            $role->permissions()->sync($request->permissions);
        }
        $role->permissions;
        $role->permissions->makeHidden('pivot');
        $role->permissions->each->makeHidden('guard_name');
        $role->makeHidden('guard_name');
        return response()->json(['message' => 'Role created successfully', 'role' => $role], 201);
    }

    /**
     * Get all roles for the authenticated user's company.
     */
    public function getRoles()
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $roles = Role::where('company_id', $companyId)->where('is_deleted',0)->get();
        $rolesWithPermissions = $roles->map(function ($role) {
            $permissions = $role->permissions;
            return [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'permissions' => $permissions->map(function ($permission) {
                    return [
                        'permission_id' => $permission->id,
                        'permission_name' => $permission->name
                    ];
                })
            ];
        });
        return response()->json($rolesWithPermissions);
    }

    /**
     * Get a specific role for the authenticated user's company.
     */
    public function getRole($id)
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $role = Role::where('company_id', $companyId)->find($id);
        if ($role->is_deleted) {
            throw new ResourceDeletedException(
                'This Role has been deleted. Please contact support.',
                'Role Deleted'
            );
        }
        if (!$role) {
            return response()->json(['error' => 'Role not found or does not belong to your company.'], 404);
        }
        $permissions = $role->permissions;
        $roleWithPermissions = [
            'role_id' => $role->id,
            'role_name' => $role->name,
            'permissions' => $permissions->map(function ($permission) {
                return [
                    'permission_id' => $permission->id,
                    'permission_name' => $permission->name
                ];
            })
        ];

        return response()->json($roleWithPermissions);
    }

    /**
     * Update an existing role for the authenticated user's company.
     */
    public function updateRole(Request $request, $id)
    {
        $user = Auth::user();
        $company_id = $user->company_id;
        $role = Role::where('company_id', $company_id)->findOrFail($id);
        $this->authorize('update', $role);
        $request->validate([
            'name' => 'required|string',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        if ($role->is_deleted) {
            throw new ResourceDeletedException(
                'This Role has been deleted. Please contact support.',
                'Role Deleted'
            );
        }
        $role->name = $request->name;
        $role->save();
        $role->permissions()->sync($request->permissions);
        return response()->json([
            'message' => 'Role updated successfully',
        ]);
    }

    /**
     * Delete a role for the authenticated user's company.
     */
    public function deleteRole($id)
    {
        $user = Auth::user();
        $company_id = $user->company_id;
        $role = Role::where('company_id', $company_id)->findOrFail($id);
        $this->authorize('delete', $role);
        $role->update([
            'is_deleted' => 1,
            'deleted_at' => Carbon::now(),
        ]);
        DB::table('role_user')->where('role_id', $id)->delete();
        return response()->json(['message' => 'Role deleted successfully']);
    }

    public function assignPermissions(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::findOrFail($request->role_id);
        if ($role->is_deleted) {
            throw new ResourceDeletedException(
                'This Role has been deleted. Please contact support.',
                'Role Deleted'
            );
        }
        $role->permissions()->sync($request->permissions);

        return response()->json(['message' => 'Permissions assigned successfully'], 200);
    }
    public function getRolePermissions($roleId)
    {
        $role = Role::findOrFail($roleId);

        if ($role->is_deleted) {
            throw new ResourceDeletedException(
                'This Role has been deleted. Please contact support.',
                'Role Deleted'
            );
        }
        $permissions = $role->permissions;

        return response()->json([
            'role' => $role->name,
            'permissions' => $permissions
        ], 200);
    }
    public function removePermissionsFromRole(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $user = Auth::user();

        $role = Role::findOrFail($request->role_id);

        if ($user->company_id !== $role->company_id) {
            return response()->json(['message' => 'User and role must belong to the same company.'], 403);
        }

        foreach ($request->permission_ids as $permission_id) {
            $permission = Permission::findOrFail($permission_id);

            if ($role->hasPermissionTo($permission)) {
                $role->revokePermissionTo($permission);
            }
        }

        return response()->json(['message' => 'Permissions removed from role successfully.'], 200);
    }
}
