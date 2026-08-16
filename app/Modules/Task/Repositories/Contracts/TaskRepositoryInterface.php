<?php

namespace App\Modules\Task\Repositories\Contracts;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

interface TaskRepositoryInterface
{
    public function create(array $data): Task;
    public function update(Task $task, array $data): bool;
    // public function delete(Task $task): bool;
    public function findById(int $id, array $with = []): ?Task;
    public function getTasksForUser(int $userId, array $with = []): Collection;
    public function getTaskWithRelatedData(int $id, array $with = []): ?Task;
}
