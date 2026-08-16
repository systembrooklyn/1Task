<?php

namespace App\Modules\Task\Repositories\Eloquent;

use App\Models\TaskCommentReply;
use App\Modules\Task\Repositories\Contracts\TaskCommentReplyRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentTaskCommentReplyRepository implements TaskCommentReplyRepositoryInterface
{
    public function create(array $data): TaskCommentReply
    {
        return TaskCommentReply::create($data);
    }

    public function update(TaskCommentReply $reply, array $data): bool
    {
        return $reply->update($data);
    }

    public function delete(TaskCommentReply $reply): bool
    {
        return $reply->delete();
    }

    public function findById(int $id, array $with = []): ?TaskCommentReply
    {
        return TaskCommentReply::with($with)->find($id);
    }

    public function getByCommentId(int $commentId, array $with = []): Collection
    {
        return TaskCommentReply::where('task_comment_id', $commentId)->with($with)->get();
    }

    public function markAsRead(int $replyId, int $userId): void
    {
        $reply = TaskCommentReply::find($replyId);
        if ($reply) {
            $pivot = $reply->users()->where('user_id', $userId)->first();
            if ($pivot && is_null($pivot->pivot->read_at)) {
                $reply->users()->updateExistingPivot($userId, ['read_at' => now()]);
            }
        }
    }

    public function attachUsers(TaskCommentReply $reply, array $userIds, array $pivotData = []): void
    {
        foreach ($userIds as $userId) {
            $reply->users()->attach($userId, $pivotData);
        }
    }
}
