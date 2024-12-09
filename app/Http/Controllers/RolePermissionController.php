<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RolePermissionController extends Controller
{
    
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
        $companyId = $user->company_id;
    
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $role = Role::create([
            'name' => $request->name,
            'company_id' => $companyId
        ]);
    
        return response()->json(['message' => 'Role created successfully', 'role' => $role], 201);
    }

    /**
     * Get all roles for the authenticated user's company.
     */
    public function getRoles()
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        // Get all roles for this company
        $roles = Role::where('company_id', $companyId)->get();

        return response()->json($roles);
    }

    /**
     * Get a specific role for the authenticated user's company.
     */
    public function getRole($id)
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        // Find the role by ID for the user's company
        $role = Role::where('company_id', $companyId)->findOrFail($id);

        return response()->json($role);
    }

    /**
     * Update an existing role for the authenticated user's company.
     */
    public function updateRole(Request $request, $id)
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        // Validate the request
        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $id
        ]);

        // Find and update the role
        $role = Role::where('company_id', $companyId)->findOrFail($id);
        $role->name = $request->name;
        $role->save();

        return response()->json($role);
    }

    /**
     * Delete a role for the authenticated user's company.
     */
    public function deleteRole($id)
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        // Find and delete the role
        $role = Role::where('company_id', $companyId)->findOrFail($id);
        echo $role;
        $role->delete();

        return response()->json(['message' => 'Role deleted successfully']);
    }

    public function assignPermissions(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::findOrFail($request->role_id);

        $role->permissions()->sync($request->permissions);

        return response()->json(['message' => 'Permissions assigned successfully'], 200);
    }
    public function getRolePermissions($roleId)
    {
        // Find the role by its ID
        $role = Role::findOrFail($roleId);

        // Retrieve all permissions associated with the role
        $permissions = $role->permissions;

        return response()->json([
            'role' => $role->name,
            'permissions' => $permissions
        ], 200);
    }
    public function removePermissionsFromRole(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',   // Ensure the role exists
            'permission_ids' => 'required|array',      // Ensure permission_ids is an array
            'permission_ids.*' => 'exists:permissions,id', // Ensure each permission exists by id
        ]);

        // Retrieve the user ID from the Bearer token
        $user = Auth::user(); // Gets the authenticated user from the Bearer token
        $userId = $user->id; // Get the user ID

        // Fetch the role by its ID
        $role = Role::findOrFail($request->role_id);

        // Ensure the user and role are in the same company
        if ($user->company_id !== $role->company_id) {
            return response()->json(['message' => 'User and role must belong to the same company.'], 403);
        }

        // Loop through the permission IDs and remove each permission from the role
        foreach ($request->permission_ids as $permission_id) {
            $permission = Permission::findOrFail($permission_id);

            // Check if the role has the permission before removing it
            if ($role->hasPermissionTo($permission)) {
                $role->revokePermissionTo($permission); // Remove the permission from the role
            }
        }

        // Return a success response
        return response()->json(['message' => 'Permissions removed from role successfully.'], 200);
    }
}
