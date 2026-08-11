<?php

namespace App\Modules\Project\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    public function viewAny(User $user)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'view-project');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        return ($hasPermission || $isOwner) ? Response::allow() : Response::deny('You do not have permission to view projects.');
    }

    public function viewAllProjects(User $user)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'view-Allproject');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        return ($hasPermission || $isOwner) ? Response::allow() : Response::deny('You do not have permission to view projects.');
    }

    public function view(User $user, Project $project)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'view-project');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        return ($hasPermission || $isOwner) ? Response::allow() : Response::deny('You do not have permission to view project.');
    }

    public function create(User $user)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'create-project');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        return ($hasPermission || $isOwner) ? Response::allow() : Response::deny('You do not have permission to create project.');
    }

    public function update(User $user, Project $project)
    {
        $belongsToCompany = $user->company_id === $project->company_id;
        $hasPermission = $user->assignedPermissions()->contains('name', 'edit-project');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        if (!($hasPermission || $isOwner)) {
            return Response::deny('You do not have permission to edit project.');
        }
        if (!$belongsToCompany) {
            return Response::deny('You do not have access to edit this project.');
        }
        return Response::allow();
    }

    public function delete(User $user, Project $project)
    {
        $belongsToCompany = $user->company_id === $project->company_id;
        $hasPermission = $user->assignedPermissions()->contains('name', 'delete-project');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        if (!($hasPermission || $isOwner)) {
            return Response::deny('You do not have permission to delete project.');
        }
        if (!$belongsToCompany && !$isOwner) {
            return Response::deny('You do not have access to delete this project.');
        }
        return Response::allow();
    }
}
