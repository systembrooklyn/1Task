<?php

namespace App\Modules\Task\Swagger;

/**
 * @OA\Tag(
 *     name="Task Revisions",
 *     description="Tracking edits and history logs for tasks"
 * )
 */
class TaskRevisionSwagger
{
    /**
     * @OA\Get(
     *     path="/api/tasks/{id}/revisions",
     *     summary="Get revision logs for a task",
     *     tags={"Task Revisions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Revisions retrieved successfully")
     * )
     */
    public function index() {}
}
