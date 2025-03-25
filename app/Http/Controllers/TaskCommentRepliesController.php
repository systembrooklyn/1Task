<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskCommentReplyResource;
use App\Models\TaskComment;
use App\Models\TaskCommentReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskCommentRepliesController extends Controller
{
    public function addReply(Request $request, $commentId)
    {
        $validated = $request->validate([
            'reply_text' => 'required|string',
        ]);

        $taskComment = TaskComment::findOrFail($commentId);
        $userId = Auth::id();
        // if ($userId !== $taskComment->user_id) {
        //     return response()->json(['error' => 'You can only reply to your own comments'], 403);
        // }
        $reply = new TaskCommentReply([
            'task_comment_id' => $taskComment->id,
            'user_id' => $userId,
            'reply_text' => $validated['reply_text'],
        ]);
        
        $reply->save();
        return response()->json([
            'message' => 'Reply created successfully',
            'data'    => new TaskCommentReplyResource($reply)
        ], 201);
    }
    public function getReplies($commentId)
    {
        $taskComment = TaskComment::findOrFail($commentId);
        $replies = $taskComment->replies;
        return response()->json([
            'message' => 'Replies Retrieved Successfully',
            'data' => TaskCommentReplyResource::collection($replies),
        ], 200);
    }
    public function updateReply(Request $request, $replyId)
    {
        $validated = $request->validate([
            'reply_text' => 'required|string',
        ]);

        $reply = TaskCommentReply::findOrFail($replyId);
        $userId = Auth::id();
        if ($userId !== $reply->user_id) {
            return response()->json(['error' => 'You can only update your own replies'], 403);
        }
        $reply->reply_text = $validated['reply_text'];
        $reply->save();
        return response()->json([
            'message' => 'Reply updated successfully',
            'data'    => new TaskCommentReplyResource($reply),
        ], 200);
    }
    public function deleteReply($replyId)
    {
        $reply = TaskCommentReply::findOrFail($replyId);
        $userId = Auth::id();
        if ($userId !== $reply->user_id) {
            return response()->json(['error' => 'You can only delete your own replies'], 403);
        }
        $reply->delete();
        return response()->json(['message' => 'Reply deleted successfully'], 200);
    }
}
