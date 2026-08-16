<?php

namespace App\Modules\Task\Repositories\Contracts;

use App\Models\TaskCommentReply;
use Illuminate\Database\Eloquent\Collection;

interface TaskCommentReplyRepositoryInterface
{
    public function create(array $data): TaskCommentReply;
    public function update(TaskCommentReply $reply, array $data): bool;
    public function delete(TaskCommentReply $reply): bool;
    public function findById(int $id, array $with = []): ?TaskCommentReply;
    public function getByCommentId(int $commentId, array $with = []): Collection;
    public function markAsRead(int $replyId, int $userId): void;
    public function attachUsers(TaskCommentReply $reply, array $userIds, array $pivotData = []): void;
}
