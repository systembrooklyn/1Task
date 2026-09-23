<?php

namespace App\Modules\Task\Repositories\Contracts;

use App\Modules\Task\Models\TaskAttachment;
use Illuminate\Support\Collection;

interface TaskAttachmentRepositoryInterface
{
    public function create(array $data): TaskAttachment;
    public function findOrFail(int $id): TaskAttachment;
    public function findByTaskOrFail(int $taskId, int $attachmentId): TaskAttachment;
    public function findMainByTask(int $taskId, array $ids): Collection;
    public function delete(TaskAttachment $attachment): bool;
}
