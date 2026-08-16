<?php

namespace App\Modules\Task\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Task\Services\TaskUserStatusService;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TaskUserStatusController extends Controller
{
    protected TaskUserStatusService $statusService;

    public function __construct(TaskUserStatusService $statusService)
    {
        $this->statusService = $statusService;
    }

    public function toggleStar(int $id): JsonResponse
    {
        $task = Task::findOrFail($id);
        $this->authorizeUserForTask($task);
        $status = $this->statusService->toggleStar($task, Auth::id());
        return response()->json($status, 200);
    }

    public function toggleArchive(int $id): JsonResponse
    {
        $task = Task::findOrFail($id);
        $this->authorizeUserForTask($task);
        $status = $this->statusService->toggleArchive($task, Auth::id());
        return response()->json($status, 200);
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
