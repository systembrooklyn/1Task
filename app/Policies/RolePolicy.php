<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RolePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Role $role): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'create-role');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        return ($hasPermission || $isOwner)
            ? \Illuminate\Auth\Access\Response::allow()
            : \Illuminate\Auth\Access\Response::deny('You do not have permission to create role.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Role $role)
    {
        $belongsToCompany = $user->company_id === $role->company_id;
        $hasPermission = $user->assignedPermissions()->contains('name', 'edit-role');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        if (!($hasPermission || $isOwner)) {
            return \Illuminate\Auth\Access\Response::deny('You do not have permission to edit this role.');
        }

        if (!$belongsToCompany) {
            return \Illuminate\Auth\Access\Response::deny('This role does not belong to your company.');
        }

        return \Illuminate\Auth\Access\Response::allow();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Role $role)
    {
        $belongsToCompany = $user->company_id === $role->company_id;
        $hasPermission = $user->assignedPermissions()->contains('name', 'delete-role');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        if (!($hasPermission || $isOwner)) {
            return \Illuminate\Auth\Access\Response::deny('You do not have permission to delete this role.');
        }

        if (!$belongsToCompany) {
            return \Illuminate\Auth\Access\Response::deny('This role does not belong to your company.');
        }

        return \Illuminate\Auth\Access\Response::allow();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Role $role): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Role $role): bool
    {
        return false;
    }
}
