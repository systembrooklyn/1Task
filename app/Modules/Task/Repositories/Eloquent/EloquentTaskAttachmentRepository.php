<?php

namespace App\Modules\Task\Repositories\Eloquent;

use App\Modules\Task\Models\TaskAttachment;
use App\Modules\Task\Repositories\Contracts\TaskAttachmentRepositoryInterface;

class EloquentTaskAttachmentRepository implements TaskAttachmentRepositoryInterface
{
    public function create(array $data): TaskAttachment
    {
        return TaskAttachment::create($data);
    }

    public function findOrFail(int $id): TaskAttachment
    {
        return TaskAttachment::findOrFail($id);
    }

    public function findByTaskOrFail(int $taskId, int $attachmentId): TaskAttachment
    {
        return TaskAttachment::where('task_id', $taskId)->findOrFail($attachmentId);
    }

    public function delete(TaskAttachment $attachment): bool
    {
        return $attachment->delete();
    }
}
