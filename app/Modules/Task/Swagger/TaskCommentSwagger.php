<?php

namespace App\Modules\Task\Swagger;

/**
 * @OA\Tag(
 *     name="Task Comments",
 *     description="Endpoints for task comments and threaded replies"
 * )
 */
class TaskCommentSwagger
{
    /**
     * @OA\Post(
     *     path="/api/tasks/{id}/comments",
     *     summary="Add a comment to a task",
     *     tags={"Task Comments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"comment_text"},
     *             @OA\Property(property="comment_text", type="string", example="Working on this right now.")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Comment created successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store() {}

    /**
     * @OA\Post(
     *     path="/api/taskComments/read",
     *     summary="Mark comment as read",
     *     tags={"Task Comments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"comment_id"},
     *             @OA\Property(property="comment_id", type="integer", example=12)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Comment marked as read")
     * )
     */
    public function markCommentAsRead() {}

    /**
     * @OA\Post(
     *     path="/api/taskComments/{commentId}/replies",
     *     summary="Add a reply to a comment",
     *     tags={"Task Comments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="commentId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"reply_text"},
     *             @OA\Property(property="reply_text", type="string", example="I agree with this note.")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Reply created successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function addReply() {}

    /**
     * @OA\Get(
     *     path="/api/taskComments/{commentId}/replies",
     *     summary="Get replies for a comment",
     *     tags={"Task Comments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="commentId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Replies retrieved successfully")
     * )
     */
    public function getReplies() {}

    /**
     * @OA\Put(
     *     path="/api/taskCommentReplies/{replyId}",
     *     summary="Update a comment reply",
     *     tags={"Task Comments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="replyId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"reply_text"},
     *             @OA\Property(property="reply_text", type="string", example="Updated reply content.")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Reply updated successfully")
     * )
     */
    public function updateReply() {}

    /**
     * @OA\Post(
     *     path="/api/taskReplies/read",
     *     summary="Mark reply as read",
     *     tags={"Task Comments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"reply_id"},
     *             @OA\Property(property="reply_id", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Reply marked as read")
     * )
     */
    public function markReplyAsRead() {}
}
