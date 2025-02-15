<?php

namespace App\Policies;

use App\Models\DailyTaskEvaluation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DailyTaskEvaluationPolicy
{
    /**
     * Determine whether the user can view any evaluations.
     */
    public function viewAny(User $user)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'view-alldailytaskevaluation');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        return ($hasPermission || $isOwner)
            ? Response::allow()
            : Response::deny('You do not have permission to view daily task evaluations.');
    }

    /**
     * Determine whether the user can view a specific evaluation.
     */
    public function view(User $user, DailyTaskEvaluation $evaluation)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'view-dailytaskevaluation');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        if (!($hasPermission || $isOwner)) {
            return Response::deny('You do not have permission to view this evaluation.');
        }

        // Ensure the user belongs to the same company as the evaluation's task
        if ($user->company_id !== $evaluation->dailyTask->company_id) {
            return Response::deny('You do not have permission to view this evaluation.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can create evaluations.
     */
    public function create(User $user)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'create-dailytaskevaluation');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        return ($hasPermission || $isOwner)
            ? Response::allow()
            : Response::deny('You do not have permission to create daily task evaluations.');
    }

    /**
     * Determine whether the user can update an evaluation.
     */
    public function update(User $user, DailyTaskEvaluation $evaluation)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'edit-dailytaskevaluation');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        if (!($hasPermission || $isOwner)) {
            return Response::deny('You do not have permission to edit this evaluation.');
        }

        // Ensure the user belongs to the same company as the evaluation's task
        if ($user->company_id !== $evaluation->dailyTask->company_id) {
            return Response::deny('You do not have permission to update this evaluation.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can delete an evaluation.
     */
    public function delete(User $user, DailyTaskEvaluation $evaluation)
    {
        $hasPermission = $user->assignedPermissions()->contains('name', 'delete-dailytaskevaluation');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        if (!($hasPermission || $isOwner)) {
            return Response::deny('You do not have permission to delete this evaluation.');
        }

        // Ensure the user belongs to the same company as the evaluation's task
        if ($user->company_id !== $evaluation->dailyTask->company_id) {
            return Response::deny('You do not have permission to delete this evaluation.');
        }

        return Response::allow();
    }
}