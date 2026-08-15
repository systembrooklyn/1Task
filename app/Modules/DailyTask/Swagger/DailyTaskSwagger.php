<?php

namespace App\Modules\DailyTask\Swagger;

/**
 * @OA\Tag(
 *     name="Daily Task",
 *     description="Daily task management, filtering, and configuration endpoints"
 * )
 */
class DailyTaskSwagger
{
    /**
     * @OA\Get(
     *     path="/api/alldailytask",
     *     summary="Get all tasks for the company",
     *     tags={"Daily Task"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Tasks retrieved successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function allDailyTasks() {}

    /**
     * @OA\Post(
     *     path="/api/alldailytaskfilter",
     *     summary="Filter daily tasks with pagination",
     *     tags={"Daily Task"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="per_page", type="integer", example=10),
     *             @OA\Property(property="sort_by", type="string", enum={"task_no", "created_at", "start_date"}, example="created_at"),
     *             @OA\Property(property="type_of", type="string", enum={"asc", "desc"}, example="desc"),
     *             @OA\Property(property="dept_ids", type="array", @OA\Items(type="integer"), example={1, 2}),
     *             @OA\Property(property="task_type", type="string", enum={"single", "daily", "weekly", "monthly", "last_day_of_month"}, example="daily"),
     *             @OA\Property(property="active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Filtered tasks retrieved successfully"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function allDailyTasksFiltered() {}

    /**
     * @OA\Post(
     *     path="/api/activedailytask/{id}",
     *     summary="Toggle active status of a task",
     *     tags={"Daily Task"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Task active status toggled successfully")
     * )
     */
    public function activeDailyTask() {}

    /**
     * @OA\Get(
     *     path="/api/dailytask/{id}/revisions",
     *     summary="Get revision history for a task",
     *     tags={"Daily Task"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Revisions retrieved")
     * )
     */
    public function revisions() {}

    /**
     * @OA\Get(
     *     path="/api/dailytask",
     *     summary="List tasks for authenticated user departments",
     *     tags={"Daily Task"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function index() {}

    /**
     * @OA\Post(
     *     path="/api/dailytask",
     *     summary="Create a new daily task",
     *     tags={"Daily Task"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent()),
     *     @OA\Response(response=201, description="Daily Task created successfully.")
     * )
     */
    public function store() {}

    /**
     * @OA\Get(
     *     path="/api/dailytask/{id}",
     *     summary="Show a single daily task",
     *     tags={"Daily Task"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function show() {}

    /**
     * @OA\Put(
     *     path="/api/dailytask/{id}",
     *     summary="Update a task",
     *     tags={"Daily Task"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent()),
     *     @OA\Response(response=200, description="Task updated successfully")
     * )
     */
    public function update() {}

    /**
     * @OA\Delete(
     *     path="/api/dailytask/{id}",
     *     summary="Delete a task",
     *     tags={"Daily Task"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Task deleted successfully.")
     * )
     */
    public function destroy() {}

    /**
     * @OA\Get(
     *     path="/api/dailyTasks/yesterday",
     *     summary="Get yesterday's evaluation tasks",
     *     tags={"Daily Task"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Random Daily Tasks Retrieved Successfully")
     * )
     */
    public function getYesterdayEvaluationTasks() {}

    /**
     * @OA\Post(
     *     path="/api/dailyTasks/setRandomCount",
     *     summary="Set random task count per department",
     *     tags={"Daily Task"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent()),
     *     @OA\Response(response=200, description="Random task count updated successfully")
     * )
     */
    public function updateRandomTaskCount() {}
}
