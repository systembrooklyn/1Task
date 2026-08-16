<?php

namespace App\Modules\Task\Services;

use App\Models\TaskComment;
use App\Models\TaskCommentReply;
use App\Modules\Task\Repositories\Contracts\TaskCommentReplyRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;

class TaskCommentReplyService
{
    protected TaskCommentReplyRepositoryInterface $replyRepo;

    public function __construct(TaskCommentReplyRepositoryInterface $replyRepo)
    {
        $this->replyRepo = $replyRepo;
    }

    public function createReply(TaskComment $comment, string $text, int $userId): TaskCommentReply
    {
        $task = $comment->task;
        $reply = $this->replyRepo->create([
            'task_comment_id' => $comment->id,
            'user_id' => $userId,
            'reply_text' => $text,
        ]);

        $relatedUsers = collect([
            $task->creator,
            $task->supervisor,
            ...$task->assignedUsers->all(),
            ...$task->consultUsers->all(),
            ...$task->informerUsers->all(),
        ])->filter()->unique('id');

        foreach ($relatedUsers as $user) {
            $reply->users()->attach($user->id, ['read_at' => $user->id === $userId ? now() : null]);
        }

        return $reply;
    }

    public function updateReply(TaskCommentReply $reply, string $text): void
    {
        $this->replyRepo->update($reply, ['reply_text' => $text]);
    }

    public function deleteReply(TaskCommentReply $reply): void
    {
        $this->replyRepo->delete($reply);
    }

    public function getRepliesForComment(int $commentId, array $with = []): Collection
    {
        return $this->replyRepo->getByCommentId($commentId, $with);
    }

    public function markReplyAsRead(int $replyId, int $userId): void
    {
        $this->replyRepo->markAsRead($replyId, $userId);
    }

    public function getReplyWithRelations(int $replyId): ?TaskCommentReply
    {
        return $this->replyRepo->findById($replyId, ['user:id,name,last_name']);
    }
}
