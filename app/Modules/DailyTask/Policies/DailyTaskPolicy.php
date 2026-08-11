<?php

namespace App\Modules\DailyTask\Policies;

use App\Models\User;
use App\Models\DailyTask;
use Illuminate\Auth\Access\Response;

class DailyTaskPolicy
{
    public function viewAny(User $user)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'view-dailytask');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        return ($hasPermission || $isOwner) ? Response::allow() : Response::deny('You do not have permission to view daily tasks.');
    }

    public function view(User $user, DailyTask $dailyTask)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'view-dailytask');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        return ($hasPermission || $isOwner) ? Response::allow() : Response::deny('You do not have permission to view daily tasks.');
    }

    public function create(User $user)
    {
        $haveAccess = $user->assignedPermissions()->contains('name', 'create-dailytask');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        return ($haveAccess || $isOwner) ? Response::allow() : Response::deny('You do not have permission to create daily task.');
    }

    public function viewAllTasks(User $user)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'view-alldailytask');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        return ($hasPermission || $isOwner) ? Response::allow() : Response::deny('You do not have permission to view daily tasks.');
    }

    public function update(User $user, DailyTask $dailyTask)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'edit-dailytask');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        if (!($hasPermission || $isOwner)) {
            return Response::deny('You do not have permission to edit daily task.');
        }
        if ($user->company_id !== $dailyTask->company_id) {
            return Response::deny('You do not have permission to update this task.');
        }
        return Response::allow();
    }

    public function report(User $user, DailyTask $dailyTask)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'report-dailytask');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        if (!($hasPermission || $isOwner)) {
            return Response::deny('You do not have permission to report daily task.');
        }
        if ($user->company_id !== $dailyTask->company_id) {
            return Response::deny('You do not have permission to report this task.');
        }
        return Response::allow();
    }

    public function delete(User $user, DailyTask $dailyTask)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'delete-dailytask');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        if (!($hasPermission || $isOwner)) {
            return Response::deny('You do not have permission to delete daily task.');
        }
        if ($user->company_id !== $dailyTask->company_id) {
            return Response::deny('You do not belong to this company.');
        }
        return Response::allow();
    }
}
