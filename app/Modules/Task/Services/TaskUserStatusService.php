<?php

namespace App\Modules\Task\Services;

use App\Models\Task;
use App\Models\TaskUserStatus;
use App\Modules\Task\Repositories\Contracts\TaskUserStatusRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class TaskUserStatusService
{
    protected TaskUserStatusRepositoryInterface $statusRepo;

    public function __construct(TaskUserStatusRepositoryInterface $statusRepo)
    {
        $this->statusRepo = $statusRepo;
    }

    public function toggleStar(Task $task, int $userId): TaskUserStatus
    {
        $status = $this->statusRepo->firstOrCreate([
            'task_id' => $task->id,
            'user_id' => $userId
        ]);

        $status->is_starred = !$status->is_starred;
        $this->statusRepo->update($status, ['is_starred' => $status->is_starred]);

        return $status;
    }

    public function toggleArchive(Task $task, int $userId): TaskUserStatus
    {
        $status = $this->statusRepo->firstOrCreate([
            'task_id' => $task->id,
            'user_id' => $userId
        ]);

        $status->is_archived = !$status->is_archived;
        $this->statusRepo->update($status, ['is_archived' => $status->is_archived]);

        return $status;
    }
}
