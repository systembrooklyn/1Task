<?php

namespace App\Modules\Task\Repositories\Eloquent;

use App\Models\TaskRevision;
use App\Modules\Task\Repositories\Contracts\TaskRevisionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentTaskRevisionRepository implements TaskRevisionRepositoryInterface
{
    public function create(array $data): TaskRevision
    {
        return TaskRevision::create($data);
    }

    public function getByTaskId(int $taskId, array $with = []): Collection
    {
        return TaskRevision::where('task_id', $taskId)->with($with)->get();
    }
}
