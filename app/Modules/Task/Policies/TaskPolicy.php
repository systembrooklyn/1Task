<?php

namespace App\Modules\Task\Policies;

use App\Models\User;
use App\Models\Task;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    public function viewAny(User $user)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'view-task');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        return ($hasPermission || $isOwner) ? Response::allow() : Response::deny('You do not have permission to view tasks.');
    }

    public function view(User $user, Task $task)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'view-task');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        return ($hasPermission || $isOwner) ? Response::allow() : Response::deny('You do not have permission to view tasks.');
    }

    public function create(User $user)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'create-task');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        return ($hasPermission || $isOwner) ? Response::allow() : Response::deny('You do not have permission to create task.');
    }

    public function update(User $user, Task $task)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'edit-task');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        if (!($hasPermission || $isOwner)) {
            return Response::deny('You do not have permission to edit task.');
        }
        if ($user->company_id !== $task->company_id) {
            return Response::deny('You do not have permission to update this task.');
        }
        return Response::allow();
    }

    public function delete(User $user, Task $task)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'delete-task');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        if (!($hasPermission || $isOwner)) {
            return Response::deny('You do not have permission to delete task.');
        }
        if ($user->company_id !== $task->company_id) {
            return Response::deny('You do not belong to this company.');
        }
        return Response::allow();
    }
}
