<?php

namespace App\Modules\Task\Repositories\Contracts;

use App\Models\TaskComment;
use Illuminate\Database\Eloquent\Collection;

interface TaskCommentRepositoryInterface
{
    public function create(array $data): TaskComment;
    public function findById(int $id, array $with = []): ?TaskComment;
    public function getByTaskId(int $taskId, array $with = []): Collection;
    public function markAsRead(int $commentId, int $userId): void;
    public function attachUsers(TaskComment $comment, array $userIds, array $pivotData = []): void;
}
