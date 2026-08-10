<?php

namespace App\Modules\User\Http\Controllers;

use App\Modules\User\Services\UserService;
use App\Modules\User\Http\Requests\UpdateUserRequest;
use App\Modules\User\Http\Requests\AssignRoleRequest;
use App\Modules\User\Http\Requests\FireTokenRequest;
use App\Exceptions\ResourceDeletedException;
use App\Models\Role;
use App\Models\User;
use App\Modules\User\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function edit(UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $userToEdit = User::findOrFail($id);

        if ($userToEdit->is_deleted) {
            throw new ResourceDeletedException('This user account has been deleted. Please contact support.', 'User Deleted');
        }

        // Authorization checks (same as original)
        if ($user->company_id != $userToEdit->company_id) {
            return response()->json(['message' => 'You can only edit users within your company.'], 403);
        }

        $haveAccess = $user->getAllPermissions()->contains('name', 'edit-user');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        $owner = $userToEdit->companies()->wherePivot('company_id', $userToEdit->company_id)->exists();

        if ($owner && $user->id !== $userToEdit->id) {
            return response()->json(['message' => 'Only the owner can edit their own account.'], 403);
        }

        if ($haveAccess || $isOwner || ($user->id == $userToEdit->id)) {
            $this->userService->updateUser($userToEdit, $request->only(['name', 'last_name']));
            return response()->json(['message' => 'User name changed successfully.'], 200);
        }

        return response()->json(['message' => 'You do not have permission to edit this user.'], 401);
    }

    public function delete(int $id): JsonResponse
    {
        $authUser = Auth::user();
        $loggedInUser = User::find($authUser->id);
        $userToDelete = User::findOrFail($id);

        if ($loggedInUser->company_id != $userToDelete->company_id) {
            return response()->json(['message' => 'You can only delete users within your company.'], 403);
        }

        $haveAccess = $loggedInUser->getAllPermissions()->contains('name', 'delete-user');
        $isOwner = $loggedInUser->companies()->wherePivot('company_id', $loggedInUser->company_id)->exists();
        $owner = $userToDelete->companies()->wherePivot('company_id', $userToDelete->company_id)->exists();

        if ($owner) {
            return response()->json(['message' => 'Owners cannot be deleted.'], 403);
        }

        if ($haveAccess || $isOwner) {
            DB::beginTransaction();
            try {
                $this->userService->deleteUser($userToDelete, true);
                DB::commit();
                return response()->json(['message' => 'User and related data deleted successfully.'], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['message' => 'An error occurred. Could not delete the user.'], 500);
            }
        }

        return response()->json(['message' => 'You do not have permission to delete this user.'], 401);
    }

    public function updateFireToken(FireTokenRequest $request): JsonResponse
    {
        $user = Auth::user();
        $this->userService->updateFireToken($user, $request->input('fireToken'));
        return response()->json([
            'message' => 'User Token updated successfully',
            'data' => new UserResource($user),
        ], 200);
    }

    public function assignRole(AssignRoleRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = User::findOrFail($validated['user_id']);

        if ($user->is_deleted) {
            return response()->json([
                'message' => 'This Account has been deleted please contact the support to retrieve it',
                'errors' => ['email' => ['User Deleted']]
            ], 422);
        }

        $roles = Role::find($validated['role_ids']);

        if ($roles->isEmpty()) {
            return response()->json(['message' => 'No valid roles found.'], 400);
        }

        // Build pivot data with company_id for each role, while validating
        $roleData = [];
        foreach ($roles as $role) {
            if ($user->company_id !== $role->company_id) {
                return response()->json(['message' => 'User and role do not belong to the same company.'], 400);
            }
            if ($role->guard_name !== 'sanctum') {
                return response()->json(['message' => 'Invalid guard name for one of the roles.'], 400);
            }
            $roleData[$role->id] = ['company_id' => $user->company_id];
        }

        $this->userService->assignRolesWithPivot($user, $roleData);

        return response()->json(['message' => 'Roles assigned successfully.'], 200);
    }

    public function unassignRole(AssignRoleRequest $request): JsonResponse
    {
        // Similar to assignRole, use removeRoles
        $user = User::findOrFail($request->input('user_id'));
        $roleIds = $request->input('role_ids');
        $this->userService->removeRoles($user, $roleIds);
        return response()->json(['message' => 'Roles unassigned successfully.'], 200);
    }
}
