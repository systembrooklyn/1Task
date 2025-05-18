<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskUserStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskUserStatusController extends Controller
{
    public function toggleStar($id)
    {
        $task = Task::findOrFail($id);
        $this->authorizeUserForTask($task);

        $status = TaskUserStatus::firstOrCreate([
            'task_id' => $task->id,
            'user_id' => Auth::id()
        ]);

        $status->is_starred = !$status->is_starred;
        $status->save();

        return response()->json($status, 200);
    }

    public function toggleArchive($id)
    {
        $task = Task::findOrFail($id);
        $this->authorizeUserForTask($task);

        $status = TaskUserStatus::firstOrCreate([
            'task_id' => $task->id,
            'user_id' => Auth::id()
        ]);

        $status->is_archived = !$status->is_archived;
        $status->save();

        return response()->json($status, 200);
    }

    protected function authorizeUserForTask(Task $task)
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
