<?php

namespace App\Modules\Task\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Task\Http\Requests\StoreReplyRequest;
use App\Modules\Task\Http\Requests\UpdateReplyRequest;
use App\Modules\Task\Http\Requests\MarkReplyReadRequest;
use App\Modules\Task\Services\TaskCommentReplyService;
use App\Models\TaskComment;
use App\Models\TaskCommentReply;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TaskCommentReplyController extends Controller
{
    protected TaskCommentReplyService $replyService;

    public function __construct(TaskCommentReplyService $replyService)
    {
        $this->replyService = $replyService;
    }

    public function addReply(StoreReplyRequest $request, int $commentId): JsonResponse
    {
        $comment = TaskComment::with('task')->findOrFail($commentId);
        $userId = Auth::id();

        $reply = $this->replyService->createReply($comment, $request->input('reply_text'), $userId);
        return response()->json([
            'message' => 'Reply created successfully',
            'data' => $reply->load('user:id,name,last_name'),
        ], 201);
    }

    public function getReplies(int $commentId): JsonResponse
    {
        $comment = TaskComment::findOrFail($commentId);
        $replies = $this->replyService->getRepliesForComment($commentId, ['user:id,name,last_name']);
        return response()->json([
            'message' => 'Replies Retrieved Successfully',
            'data' => $replies,
        ], 200);
    }

    public function updateReply(UpdateReplyRequest $request, int $replyId): JsonResponse
    {
        $reply = TaskCommentReply::findOrFail($replyId);
        $userId = Auth::id();
        if ($userId !== $reply->user_id) {
            return response()->json(['error' => 'You can only update your own replies'], 403);
        }
        $this->replyService->updateReply($reply, $request->input('reply_text'));
        return response()->json([
            'message' => 'Reply updated successfully',
            'data' => $reply->load('user:id,name,last_name'),
        ], 200);
    }

    public function deleteReply(int $replyId): JsonResponse
    {
        $reply = TaskCommentReply::findOrFail($replyId);
        $userId = Auth::id();
        if ($userId !== $reply->user_id) {
            return response()->json(['error' => 'You can only delete your own replies'], 403);
        }
        $this->replyService->deleteReply($reply);
        return response()->json(['message' => 'Reply deleted successfully'], 200);
    }

    public function markReplyAsRead(MarkReplyReadRequest $request): JsonResponse
    {
        $this->replyService->markReplyAsRead($request->input('reply_id'), Auth::id());
        return response()->json(['message' => 'Reply marked as read successfully'], 200);
    }
}
