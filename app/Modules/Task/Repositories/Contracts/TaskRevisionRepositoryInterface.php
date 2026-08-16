<?php

namespace App\Modules\Task\Repositories\Contracts;

use App\Models\TaskRevision;
use Illuminate\Database\Eloquent\Collection;

interface TaskRevisionRepositoryInterface
{
    public function create(array $data): TaskRevision;
    public function getByTaskId(int $taskId, array $with = []): Collection;
}
