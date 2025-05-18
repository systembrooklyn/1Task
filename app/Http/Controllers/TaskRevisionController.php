<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskRevisionController extends Controller
{
    public function index($id)
    {
        $task = Task::with('revisions.user')->findOrFail($id);
        $this->authorizeUserForTask($task);

        return response()->json($task->revisions, 200);
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
