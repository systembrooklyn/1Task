<?php

namespace App\Modules\Task\Http\Traits;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;

trait AuthorizesTaskAccess
{
    protected function authorizeUserForTask(Task $task): void
    {
        $userId = Auth::id();

        $relatedUserIds = collect([
            $task->creator_user_id,
            $task->supervisor_user_id,
            ...$task->assignedUsers->pluck('id')->toArray(),
            ...$task->consultUsers->pluck('id')->toArray(),
            ...$task->informerUsers->pluck('id')->toArray(),
        ])->filter()->unique();

        if (!$relatedUserIds->contains($userId)) {
            abort(403, 'Forbidden: You are not authorized to perform this action.');
        }
    }
}
