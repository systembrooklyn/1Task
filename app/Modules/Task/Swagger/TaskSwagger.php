<?php

namespace App\Modules\Task\Swagger;

/**
 * @OA\Tag(
 *     name="Tasks",
 *     description="Operations for managing tasks, status, and metadata"
 * )
 */
class TaskSwagger
{
    /**
     * @OA\Get(
     *     path="/api/tasks",
     *     summary="Get all tasks",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Tasks retrieved successfully"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index() {}

    /**
     * @OA\Post(
     *     path="/api/tasks",
     *     summary="Create a new task",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"assigned_user_id", "title"},
     *             @OA\Property(property="title", type="string", example="Fix bug in login"),
     *             @OA\Property(property="description", type="string", example="Resolve the token expiration issue"),
     *             @OA\Property(property="start_date", type="string", format="date", example="2026-08-16"),
     *             @OA\Property(property="deadline", type="string", format="date", example="2026-08-20"),
     *             @OA\Property(property="priority", type="string", enum={"low", "normal", "high", "urgent"}, example="high"),
     *             @OA\Property(property="assigned_user_id", type="array", @OA\Items(type="integer"), example={2, 3}),
     *             @OA\Property(property="supervisor_user_id", type="integer", example=1),
     *             @OA\Property(property="consult_user_id", type="array", @OA\Items(type="integer"), example={4}),
     *             @OA\Property(property="inform_user_id", type="array", @OA\Items(type="integer"), example={5}),
     *             @OA\Property(property="project_id", type="integer", example=1),
     *             @OA\Property(property="department_id", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Task created successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store() {}

    /**
     * @OA\Get(
     *     path="/api/tasks/{id}",
     *     summary="Get a specific task",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Task retrieved successfully"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function show() {}

    /**
     * @OA\Put(
     *     path="/api/tasks/{id}",
     *     summary="Update a task",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Title"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="priority", type="string", enum={"low", "normal", "high", "urgent"}, example="normal"),
     *             @OA\Property(property="status", type="string", enum={"pending", "rework", "done", "review", "inProgress"}, example="inProgress"),
     *             @OA\Property(property="assigned_user_id", type="array", @OA\Items(type="integer"), example={2, 3}),
     *             @OA\Property(property="supervisor_user_id", type="integer", example=1),
     *             @OA\Property(property="consult_user_id", type="array", @OA\Items(type="integer"), example={4}),
     *             @OA\Property(property="inform_user_id", type="array", @OA\Items(type="integer"), example={5}),
     *             @OA\Property(property="project_id", type="integer", example=1),
     *             @OA\Property(property="department_id", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Task updated successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update() {}

    /**
     * @OA\Put(
     *     path="/api/tasks/{taskId}/status",
     *     summary="Update task status",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="taskId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"pending", "rework", "done", "review", "inProgress"}, example="done")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Status updated successfully")
     * )
     */
    public function updateStatus() {}

    /**
     * @OA\Post(
     *     path="/api/tasks/{id}/star",
     *     summary="Toggle star on a task",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Star toggled successfully")
     * )
     */
    public function toggleStar() {}

    /**
     * @OA\Post(
     *     path="/api/tasks/{id}/archive",
     *     summary="Toggle archive on a task",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Archive toggled successfully")
     * )
     */
    public function toggleArchive() {}
}
