<?php

namespace App\Modules\Task\Repositories\Eloquent;

use App\Models\TaskComment;
use App\Modules\Task\Repositories\Contracts\TaskCommentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentTaskCommentRepository implements TaskCommentRepositoryInterface
{
    public function create(array $data): TaskComment
    {
        return TaskComment::create($data);
    }

    public function findById(int $id, array $with = []): ?TaskComment
    {
        return TaskComment::with($with)->find($id);
    }

    public function getByTaskId(int $taskId, array $with = []): Collection
    {
        return TaskComment::where('task_id', $taskId)->with($with)->get();
    }

    public function markAsRead(int $commentId, int $userId): void
    {
        $comment = TaskComment::find($commentId);
        if ($comment) {
            $pivot = $comment->users()->where('user_id', $userId)->first();
            if ($pivot && is_null($pivot->pivot->read_at)) {
                $comment->users()->updateExistingPivot($userId, ['read_at' => now()]);
            }
        }
    }

    public function attachUsers(TaskComment $comment, array $userIds, array $pivotData = []): void
    {
        foreach ($userIds as $userId) {
            $comment->users()->attach($userId, $pivotData);
        }
    }
}
