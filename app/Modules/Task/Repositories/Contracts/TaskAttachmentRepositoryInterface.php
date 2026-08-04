<?php

namespace App\Modules\Task\Repositories\Contracts;

use App\Modules\Task\Models\TaskAttachment;

interface TaskAttachmentRepositoryInterface
{
    public function create(array $data): TaskAttachment;
    public function findOrFail(int $id): TaskAttachment;
    public function findByTaskOrFail(int $taskId, int $attachmentId): TaskAttachment;
    public function delete(TaskAttachment $attachment): bool;
}
