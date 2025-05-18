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
        $task = $taskComment->task;
        $relatedUsers = collect([
            $task->creator,
            $task->supervisor,
            ...$task->assignedUsers->all(),
            ...$task->consultUsers->all(),
            ...$task->informerUsers->all(),
        ])->filter()->unique('id');
        foreach ($relatedUsers as $user) {
            if ($user->id === $userId) {
                $reply->users()->attach($user->id, ['read_at' => now()]);
            } else {
                $reply->users()->attach($user->id, ['read_at' => null]);
            }
        }

        return response()->json([
            'message' => 'Reply created successfully',
            'data' => new TaskCommentReplyResource($reply->load('user:id,name')),
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


    public function markReplyAsRead(Request $request)
    {
        $validated = $request->validate([
            'reply_id' => 'required|exists:task_comment_replies,id',
        ]);
        $userId = Auth::id();
        $reply = TaskCommentReply::findOrFail($validated['reply_id']);
        $pivot = $reply->users()->where('user_id', $userId)->first();

        if ($pivot && is_null($pivot->pivot->read_at)) {
            $reply->users()->updateExistingPivot($userId, ['read_at' => now()]);
        }
        return response()->json([
            'message' => 'Reply marked as read successfully',
        ], 200);
    }
}
