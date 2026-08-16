<?php

namespace App\Modules\Task\Repositories\Eloquent;

use App\Models\TaskUserStatus;
use App\Modules\Task\Repositories\Contracts\TaskUserStatusRepositoryInterface;

class EloquentTaskUserStatusRepository implements TaskUserStatusRepositoryInterface
{
    public function firstOrCreate(array $attributes, array $values = []): TaskUserStatus
    {
        return TaskUserStatus::firstOrCreate($attributes, $values);
    }

    public function update(TaskUserStatus $status, array $data): bool
    {
        return $status->update($data);
    }

    public function getForTaskAndUser(int $taskId, int $userId): ?TaskUserStatus
    {
        return TaskUserStatus::where('task_id', $taskId)->where('user_id', $userId)->first();
    }
}
