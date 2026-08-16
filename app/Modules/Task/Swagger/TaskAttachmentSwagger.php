<?php

namespace App\Modules\Task\Swagger;

/**
 * @OA\Tag(
 *     name="Task Attachments",
 *     description="Manage file uploads and downloads for tasks"
 * )
 */
class TaskAttachmentSwagger
{
    /**
     * @OA\Post(
     *     path="/api/tasks/{id}/attachments",
     *     summary="Upload an attachment to a task",
     *     tags={"Task Attachments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(property="file", type="string", format="binary", description="File to attach"),
     *                 @OA\Property(property="comment_text", type="string", example="Here is the document reference.")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Attachment created successfully"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function store() {}

    /**
     * @OA\Get(
     *     path="/api/tasks/{id}/attachments/{attachmentId}/download",
     *     summary="Download a task attachment",
     *     tags={"Task Attachments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="attachmentId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="File stream response"),
     *     @OA\Response(response=500, description="Download failed")
     * )
     */
    public function download() {}
}
