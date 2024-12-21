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
        if (!in_array($userId, [$task->creator_user_id, $task->assigned_user_id, $task->supervisor_user_id])) {
            abort(403, 'Forbidden');
        }
    }
}
