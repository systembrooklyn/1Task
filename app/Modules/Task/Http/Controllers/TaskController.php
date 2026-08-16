<?php

namespace App\Modules\Task\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Task\Http\Requests\StoreTaskRequest;
use App\Modules\Task\Http\Requests\UpdateTaskRequest;
use App\Modules\Task\Http\Requests\UpdateTaskStatusRequest;
use App\Modules\Task\Services\TaskService;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    protected TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function index(): JsonResponse
    {
        $userId = Auth::id();
        $tasks = $this->taskService->getTasksForUser($userId);
        return response()->json($tasks, 200);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $user = Auth::user();
        $this->authorize('create', Task::class);
        $task = $this->taskService->createTask($request->validated(), $user->company_id, $user->id);
        return response()->json(
            $task->load([
                'creator:id,name,last_name',
                'supervisor:id,name,last_name',
                'assignedUsers:id,name,last_name',
                'consultUsers:id,name,last_name',
                'informerUsers:id,name,last_name',
            ]),
            201
        );
    }

    public function show(int $id): JsonResponse
    {
        $currentUserId = Auth::id();
        $with = [
            'company',
            'project',
            'department',
            'attachments.uploadedBy',
            'revisions.user',
            'creator',
            'supervisor',
            'assignedUsers',
            'consultUsers',
            'informerUsers',
            'comments.user',
            'comments.users',
            'comments.replies.user',
            'comments.replies.users'
        ];
        $task = $this->taskService->getTaskWithRelations($id, $with);
        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }
        $this->authorizeUserForTask($task);

        DB::table('task_comment_user')
            ->where('user_id', $currentUserId)
            ->whereIn('task_comment_id', $task->comments->pluck('id'))
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $task->comments->each(function ($comment) use ($currentUserId) {
            $comment->replies_count = $comment->replies->count();
            $comment->seen_by = $comment->users->filter(fn($u) => $u->id !== $comment->user_id && !is_null($u->pivot->read_at))
                ->map(fn($u) => [
                    'user_id' => $u->id,
                    'name' => $u->name,
                    'last_name' => $u->last_name ?? null,
                    'ppUrl' => $u->ppUrl ?? null,
                    'read_at' => $u->pivot->read_at,
                ])->values();
            $comment->is_seen = $comment->users->contains(fn($u) => $u->id === $currentUserId && !is_null($u->pivot->read_at));
            unset($comment->users);
            $comment->replies->each(function ($reply) use ($currentUserId) {
                $reply->seen_by = $reply->users->filter(fn($u) => $u->id !== $reply->user_id && !is_null($u->pivot->read_at))
                    ->map(fn($u) => [
                        'user_id' => $u->id,
                        'name' => $u->name,
                        'last_name' => $u->last_name ?? null,
                        'ppUrl' => $u->ppUrl ?? null,
                        'read_at' => $u->pivot->read_at,
                    ])->values();
                $reply->is_seen = $reply->users->contains(fn($u) => $u->id === $currentUserId && !is_null($u->pivot->read_at));
                unset($reply->users);
            });
        });

        $task->makeHidden([
            'company_id',
            'department_id',
            'project_id',
            'creator_user_id',
            'assigned_user_id',
            'supervisor_user_id',
            'consult_user_id',
            'inform_user_id'
        ]);

        return response()->json($task, 200);
    }

    public function update(UpdateTaskRequest $request, int $id): JsonResponse
    {
        $task = Task::findOrFail($id);
        $user = Auth::user();

        if ($user->id !== $task->creator_user_id) {
            return response()->json(['message' => 'Only creator can update the task'], 403);
        }

        $this->authorize('update', $task);
        $data = $request->validated();

        if (isset($data['status'])) {
            if ($data['status'] === 'done' && $user->id !== $task->creator_user_id) {
                return response()->json(['error' => 'Only creator can mark done'], 403);
            }
            if ($data['status'] === 'rework' && !in_array($user->id, [$task->creator_user_id, $task->supervisor_user_id])) {
                return response()->json(['error' => 'Only creator or supervisor can mark rework'], 403);
            }
        }

        $updatedTask = $this->taskService->updateTask($task, $data, $user->id);
        return response()->json(
            $updatedTask->load([
                'creator:id,name,last_name',
                'supervisor:id,name,last_name',
                'assignedUsers:id,name,last_name',
                'consultUsers:id,name,last_name',
                'informerUsers:id,name,last_name',
            ]),
            200
        );
    }

    // public function destroy(int $id): JsonResponse
    // {
    //     $task = Task::findOrFail($id);
    //     if (Auth::id() !== $task->creator_user_id) {
    //         return response()->json(['error' => 'Forbidden'], 403);
    //     }
    //     $this->authorize('delete', $task);
    //     $this->taskService->deleteTask($task);
    //     return response()->json(['message' => 'Task and its attachments deleted successfully'], 200);
    // }

    public function updateStatus(UpdateTaskStatusRequest $request, int $taskId): JsonResponse
    {
        $task = Task::with([
            'creator:id,name,last_name',
            'supervisor:id,name,last_name',
            'assignedUsers:id,name,last_name',
            'consultUsers:id,name,last_name',
            'informerUsers:id,name,last_name',
        ])->findOrFail($taskId);

        $user = Auth::user();
        $this->authorizeUserForTask($task);

        $this->taskService->updateStatus($task, $request->input('status'), $user->id);
        return response()->json(['message' => 'Task status updated successfully'], 200);
    }

    public function getRevisions(int $id): JsonResponse
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
