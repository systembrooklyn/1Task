<?php

namespace App\Modules\Task\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Task\Services\TaskRevisionService;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TaskRevisionController extends Controller
{
    protected TaskRevisionService $revisionService;

    public function __construct(TaskRevisionService $revisionService)
    {
        $this->revisionService = $revisionService;
    }

    public function index(int $id): JsonResponse
    {
        $task = Task::with('revisions.user')->findOrFail($id);
        $this->authorizeUserForTask($task);
        return response()->json($task->revisions, 200);
    }

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
