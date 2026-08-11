<?php

namespace App\Modules\Department\Policies;

use App\Models\Department;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DepartmentPolicy
{
    public function viewAny(User $user)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'view-department');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        return ($hasPermission || $isOwner)
            ? Response::allow()
            : Response::deny('You do not have permission to view departments.');
    }

    public function view(User $user, Department $department)
    {
        if (!$user->company_id) {
            return Response::deny('No company associated.');
        }
        $hasPermission = $user->company_id === $department->company_id &&
            $user->hasAssignedPermission('view-department') &&
            $user->departments->contains($department);
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        return ($hasPermission || $isOwner)
            ? Response::allow()
            : Response::deny('You do not have permission to view this department.');
    }

    public function create(User $user)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'create-department');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        return ($hasPermission || $isOwner)
            ? Response::allow()
            : Response::deny('You do not have permission to create a department.');
    }

    public function update(User $user, Department $department)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'edit-department');
        $isOwner = $user->companies()->wherePivot('company_id', $department->company_id)->exists();

        return ($hasPermission || $isOwner)
            ? Response::allow()
            : Response::deny('You do not have permission to edit this department.');
    }

    public function delete(User $user, Department $department)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'delete-department');
        $isOwner = $user->companies()->wherePivot('company_id', $department->company_id)->exists();

        return ($hasPermission || $isOwner)
            ? Response::allow()
            : Response::deny('You do not have permission to delete this department.');
    }
}
