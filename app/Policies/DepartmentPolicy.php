<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DepartmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'view-department');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        return ($hasPermission || $isOwner)
            ? \Illuminate\Auth\Access\Response::allow()
            : \Illuminate\Auth\Access\Response::deny('You do not have permission to view departments.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Department $department)
    {
        if (!$user->company_id) {
            return false;
        }
        $hasPermission = $user->company_id === $department->company_id && $user->hasAssignedPermission('view-department') && 
        $user->departments->contains($department);
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        return $hasPermission || $isOwner;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        $haveAccess = $user->assignedPermissions()->contains('name', 'create-department');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        if (!$haveAccess && !$isOwner) {
            return \Illuminate\Auth\Access\Response::deny('You do not have permission to create a department.');
        }
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Department $department)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'edit-department');
        $isOwner = $user->companies()->wherePivot('company_id', $department->company_id)->exists();

        return ($hasPermission || $isOwner)
            ? \Illuminate\Auth\Access\Response::allow()
            : \Illuminate\Auth\Access\Response::deny('You do not have permission to edit this department.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Department $department)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'delete-department');
        $isOwner = $user->companies()->wherePivot('company_id', $department->company_id)->exists();

        return ($hasPermission || $isOwner)
            ? \Illuminate\Auth\Access\Response::allow()
            : \Illuminate\Auth\Access\Response::deny('You do not have permission to delete this department.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Department $department): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Department $department): bool
    {
        return false;
    }
}
