<?php

namespace App\Policies;

use App\Models\DailyTask;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DailyTaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'view-dailytask');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        return ($hasPermission || $isOwner)
            ? \Illuminate\Auth\Access\Response::allow()
            : \Illuminate\Auth\Access\Response::deny('You do not have permission to view daily tasks.');
    }

    /**
     * Determine whether the user can view the model.` 
     */
    public function view(User $user, DailyTask $dailyTask)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'view-dailytask');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        return ($hasPermission || $isOwner)
            ? \Illuminate\Auth\Access\Response::allow()
            : \Illuminate\Auth\Access\Response::deny('You do not have permission to view daily tasks.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        $haveAccess = $user->assignedPermissions()->contains('name', 'create-dailytask');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        return ($haveAccess || $isOwner)
            ? \Illuminate\Auth\Access\Response::allow()
            : \Illuminate\Auth\Access\Response::deny('You do not have permission to create daily task.');
    }

    public function viewAllTasks(User $user){
        $hasPermission = $user->assignedPermissions()->contains('name', 'view-alldailytask');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        return ($hasPermission || $isOwner)
            ? \Illuminate\Auth\Access\Response::allow()
            : \Illuminate\Auth\Access\Response::deny('You do not have permission to view daily tasks.');
    }
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DailyTask $dailyTask)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'edit-dailytask');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        if (!($hasPermission || $isOwner)) {
            return \Illuminate\Auth\Access\Response::deny('You do not have permission to edit daily task.');
        }

        // Ensure the user belongs to the same company and department
        // || !$user->departments->contains($dailyTask->dept_id)
        if ($user->company_id !== $dailyTask->company_id) {
            return \Illuminate\Auth\Access\Response::deny('You do not have permission to update this task.');
        }

        return \Illuminate\Auth\Access\Response::allow();
    }
    public function report(User $user, DailyTask $dailyTask)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'report_dailytask');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        if (!($hasPermission || $isOwner)) {
            return \Illuminate\Auth\Access\Response::deny('You do not have permission to report daily task.');
        }
        if ($user->company_id !== $dailyTask->company_id) {
            return \Illuminate\Auth\Access\Response::deny('You do not have permission to report this task.');
        }

        return \Illuminate\Auth\Access\Response::allow();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DailyTask $dailyTask)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'delete-dailytask');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        if (!($hasPermission || $isOwner)) {
            return \Illuminate\Auth\Access\Response::deny('You do not have permission to delete daily task.');
        }

        if ($user->company_id !== $dailyTask->company_id) {
            return \Illuminate\Auth\Access\Response::deny('You do not belong to this company.');
        }

        return \Illuminate\Auth\Access\Response::allow();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DailyTask $dailyTask): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DailyTask $dailyTask): bool
    {
        return false;
    }
}
