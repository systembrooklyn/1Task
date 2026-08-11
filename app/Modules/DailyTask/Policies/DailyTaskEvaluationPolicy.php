<?php

namespace App\Modules\DailyTask\Policies;

use App\Models\User;
use App\Models\DailyTaskEvaluation;
use Illuminate\Auth\Access\Response;

class DailyTaskEvaluationPolicy
{
    public function viewAny(User $user)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'view-alldailytaskevaluation');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        return ($hasPermission || $isOwner) ? Response::allow() : Response::deny('You do not have permission to view daily task evaluations.');
    }

    public function view(User $user, DailyTaskEvaluation $evaluation)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'view-dailytaskevaluation');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        if (!($hasPermission || $isOwner)) {
            return Response::deny('You do not have permission to view this evaluation.');
        }
        if ($user->company_id !== $evaluation->dailyTask->company_id) {
            return Response::deny('You do not have permission to view this evaluation.');
        }
        return Response::allow();
    }

    public function create(User $user)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'create-dailytaskevaluation');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        return ($hasPermission || $isOwner) ? Response::allow() : Response::deny('You do not have permission to create daily task evaluations.');
    }

    public function update(User $user, DailyTaskEvaluation $evaluation)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'edit-dailytaskevaluation');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        if (!($hasPermission || $isOwner)) {
            return Response::deny('You do not have permission to edit this evaluation.');
        }
        if ($user->company_id !== $evaluation->dailyTask->company_id) {
            return Response::deny('You do not have permission to update this evaluation.');
        }
        return Response::allow();
    }

    public function delete(User $user, DailyTaskEvaluation $evaluation)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'delete-dailytaskevaluation');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        if (!($hasPermission || $isOwner)) {
            return Response::deny('You do not have permission to delete this evaluation.');
        }
        if ($user->company_id !== $evaluation->dailyTask->company_id) {
            return Response::deny('You do not have permission to delete this evaluation.');
        }
        return Response::allow();
    }

    public function viewChartReports(User $user)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'view-chartReports');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        return ($hasPermission || $isOwner) ? Response::allow() : Response::deny('You do not have permission to view chart reports.');
    }
}
