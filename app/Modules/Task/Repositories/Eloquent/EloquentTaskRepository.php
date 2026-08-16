<?php

namespace App\Modules\Task\Repositories\Eloquent;

use App\Models\Task;
use App\Modules\Task\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentTaskRepository implements TaskRepositoryInterface
{
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(Task $task, array $data): bool
    {
        return $task->update($data);
    }

    // public function delete(Task $task): bool
    // {
    //     return $task->delete();
    // }

    public function findById(int $id, array $with = []): ?Task
    {
        return Task::with($with)->find($id);
    }

    public function getTasksForUser(int $userId, array $with = []): Collection
    {
        return Task::select([
            'id',
            'company_id',
            'project_id',
            'department_id',
            'creator_user_id',
            'supervisor_user_id',
            'title',
            'description',
            'start_date',
            'deadline',
            'priority',
            'status',
            'created_at',
            'updated_at'
        ])
            ->where(function ($query) use ($userId) {
                $query->where('creator_user_id', $userId)
                    ->orWhereHas('assignedUsers', fn($q) => $q->where('user_id', $userId))
                    ->orWhereHas('consultUsers', fn($q) => $q->where('user_id', $userId))
                    ->orWhereHas('informerUsers', fn($q) => $q->where('user_id', $userId));
            })
            ->orWhere('supervisor_user_id', $userId)
            ->withCount('comments')
            ->with($with)
            ->get();
    }

    public function getTaskWithRelatedData(int $id, array $with = []): ?Task
    {
        return Task::with($with)->find($id);
    }
}
