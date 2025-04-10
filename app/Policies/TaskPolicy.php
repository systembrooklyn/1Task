<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'view-task');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        return ($hasPermission || $isOwner)
            ? \Illuminate\Auth\Access\Response::allow()
            : \Illuminate\Auth\Access\Response::deny('You do not have permission to view tasks.');
    }

    /**
     * Determine whether the user can view the model.` 
     */
    public function view(User $user, Task $task)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'view-task');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        return ($hasPermission || $isOwner)
            ? \Illuminate\Auth\Access\Response::allow()
            : \Illuminate\Auth\Access\Response::deny('You do not have permission to view tasks.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        $haveAccess = $user->assignedPermissions()->contains('name', 'create-task');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        return ($haveAccess || $isOwner)
            ? \Illuminate\Auth\Access\Response::allow()
            : \Illuminate\Auth\Access\Response::deny('You do not have permission to create task.');
    }

    public function viewAllTasks(User $user){
        $hasPermission = $user->assignedPermissions()->contains('name', 'view-alltask');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        return ($hasPermission || $isOwner)
            ? \Illuminate\Auth\Access\Response::allow()
            : \Illuminate\Auth\Access\Response::deny('You do not have permission to view tasks.');
    }
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, task $task)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'edit-task');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        if (!($hasPermission || $isOwner)) {
            return \Illuminate\Auth\Access\Response::deny('You do not have permission to edit task.');
        }

        // Ensure the user belongs to the same company and department
        // || !$user->departments->contains($task->dept_id)
        if ($user->company_id !== $task->company_id) {
            return \Illuminate\Auth\Access\Response::deny('You do not have permission to update this task.');
        }

        return \Illuminate\Auth\Access\Response::allow();
    }
    public function report(User $user, task $task)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'report-task');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        if (!($hasPermission || $isOwner)) {
            return \Illuminate\Auth\Access\Response::deny('You do not have permission to report task.');
        }
        if ($user->company_id !== $task->company_id) {
            return \Illuminate\Auth\Access\Response::deny('You do not have permission to report this task.');
        }

        return \Illuminate\Auth\Access\Response::allow();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, task $task)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'delete-task');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        if (!($hasPermission || $isOwner)) {
            return \Illuminate\Auth\Access\Response::deny('You do not have permission to delete task.');
        }

        if ($user->company_id !== $task->company_id) {
            return \Illuminate\Auth\Access\Response::deny('You do not belong to this company.');
        }

        return \Illuminate\Auth\Access\Response::allow();
    }
}
