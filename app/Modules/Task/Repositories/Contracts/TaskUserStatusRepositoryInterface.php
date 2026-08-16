<?php

namespace App\Modules\Task\Repositories\Contracts;

use App\Models\TaskUserStatus;

interface TaskUserStatusRepositoryInterface
{
    public function firstOrCreate(array $attributes, array $values = []): TaskUserStatus;
    public function update(TaskUserStatus $status, array $data): bool;
    public function getForTaskAndUser(int $taskId, int $userId): ?TaskUserStatus;
}
