<?php

namespace App\Modules\Task\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Task\Http\Requests\StoreCommentRequest;
use App\Modules\Task\Http\Requests\MarkCommentReadRequest;
use App\Modules\Task\Services\TaskCommentService;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TaskCommentController extends Controller
{
    protected TaskCommentService $commentService;

    public function __construct(TaskCommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    public function store(StoreCommentRequest $request, int $taskId): JsonResponse
    {
        $task = Task::with([
            'creator',
            'supervisor',
            'assignedUsers',
            'consultUsers',
            'informerUsers'
        ])->findOrFail($taskId);

        $this->authorizeUserForTask($task);

        $comment = $this->commentService->createComment($task, $request->input('comment_text'), Auth::id());
        return response()->json($comment->load('user:id,name,last_name'), 201);
    }

    public function markCommentAsRead(MarkCommentReadRequest $request): JsonResponse
    {
        $this->commentService->markCommentAsRead($request->input('comment_id'), Auth::id());
        return response()->json(['message' => 'Comment marked as read successfully'], 200);
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
