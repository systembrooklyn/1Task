<?php

namespace App\Modules\Task\Services;

use App\Models\Task;
use App\Models\TaskComment;
use App\Modules\Task\Repositories\Contracts\TaskCommentRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class TaskCommentService
{
    protected TaskCommentRepositoryInterface $commentRepo;

    public function __construct(TaskCommentRepositoryInterface $commentRepo)
    {
        $this->commentRepo = $commentRepo;
    }

    public function createComment(Task $task, string $text, int $userId): TaskComment
    {
        $comment = $this->commentRepo->create([
            'task_id' => $task->id,
            'user_id' => $userId,
            'comment_text' => $text,
        ]);

        $relatedUsers = collect([
            $task->creator,
            $task->supervisor,
            ...$task->assignedUsers->all(),
            ...$task->consultUsers->all(),
            ...$task->informerUsers->all(),
        ])->filter()->unique('id');

        foreach ($relatedUsers as $user) {
            $comment->users()->attach($user->id, ['read_at' => $user->id === $userId ? now() : null]);
        }

        return $comment;
    }

    public function markCommentAsRead(int $commentId, int $userId): void
    {
        $this->commentRepo->markAsRead($commentId, $userId);
    }

    public function getCommentWithRelations(int $commentId): ?TaskComment
    {
        return $this->commentRepo->findById($commentId, ['user:id,name,last_name']);
    }
}
