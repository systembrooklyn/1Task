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
        if (!in_array($userId, [$task->creator_user_id, $task->assigned_user_id, $task->supervisor_user_id, $task->consult_user_id, $task->inform_user_id])) {
            abort(403, 'Forbidden');
        }
    }
}
