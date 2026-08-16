<?php

namespace App\Modules\Task\Services;

use App\Modules\Task\Repositories\Contracts\TaskRevisionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TaskRevisionService
{
    protected TaskRevisionRepositoryInterface $revisionRepo;

    public function __construct(TaskRevisionRepositoryInterface $revisionRepo)
    {
        $this->revisionRepo = $revisionRepo;
    }

    public function getRevisionsForTask(int $taskId, array $with = []): Collection
    {
        return $this->revisionRepo->getByTaskId($taskId, $with);
    }
}
