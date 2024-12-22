<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'view-project');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        return ($hasPermission || $isOwner)
            ? \Illuminate\Auth\Access\Response::allow()
            : \Illuminate\Auth\Access\Response::deny('You do not have permission to view projects.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project)
    {
        $belongsToCompany = $user->company_id === $project->company_id;
        $hasPermission = $user->assignedPermissions()->contains('name', 'view-project');

        return ($belongsToCompany && $hasPermission)
            ? \Illuminate\Auth\Access\Response::allow()
            : \Illuminate\Auth\Access\Response::deny('You do not have permission to view this project.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'create-project');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        return ($hasPermission || $isOwner)
            ? \Illuminate\Auth\Access\Response::allow()
            : \Illuminate\Auth\Access\Response::deny('You do not have permission to create project.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project)
    {
        $belongsToCompany = $user->company_id === $project->company_id;
        $hasPermission = $user->assignedPermissions()->contains('name', 'edit-project');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        if (!($hasPermission || $isOwner)) {
            return \Illuminate\Auth\Access\Response::deny('You do not have permission to edit project.');
        }

        if (!$belongsToCompany) {
            return \Illuminate\Auth\Access\Response::deny('You do not have access to edit this project.');
        }

        return \Illuminate\Auth\Access\Response::allow();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project)
    {
        $belongsToCompany = $user->company_id === $project->company_id;
        $hasPermission = $user->assignedPermissions()->contains('name', 'delete-project');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        if (!($hasPermission || $isOwner)) {
            return \Illuminate\Auth\Access\Response::deny('You do not have permission to delete project.');
        }

        if (!$belongsToCompany && !$isOwner) {
            return \Illuminate\Auth\Access\Response::deny('You do not have access to delete this project.');
        }

        return \Illuminate\Auth\Access\Response::allow();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Project $project): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return false;
    }
}
