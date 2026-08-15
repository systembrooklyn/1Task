<?php

namespace App\Modules\DailyTask\Swagger;

/**
 * @OA\Tag(
 *     name="Daily Task Evaluation",
 *     description="Task evaluations and performance analytics endpoints"
 * )
 */
class DailyTaskEvaluationSwagger
{
    /**
     * @OA\Get(
     *     path="/api/evaluations/{taskId}",
     *     summary="Get evaluations for a specific task",
     *     tags={"Daily Task Evaluation"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="taskId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function index() {}

    /**
     * @OA\Post(
     *     path="/api/evaluations/{taskId}",
     *     summary="Store an evaluation for a task",
     *     tags={"Daily Task Evaluation"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="taskId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent()),
     *     @OA\Response(response=201, description="Evaluation created successfully!")
     * )
     */
    public function store() {}

    /**
     * @OA\Get(
     *     path="/api/evaluation/{id}",
     *     summary="Show a single evaluation",
     *     tags={"Daily Task Evaluation"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function show() {}

    /**
     * @OA\Put(
     *     path="/api/evaluations/{id}",
     *     summary="Update an evaluation",
     *     tags={"Daily Task Evaluation"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent()),
     *     @OA\Response(response=200, description="Evaluation updated successfully!")
     * )
     */
    public function update() {}

    /**
     * @OA\Get(
     *     path="/api/daily-tasks-evaluations/{date?}",
     *     summary="Get evaluations/tasks of the day",
     *     tags={"Daily Task Evaluation"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="date", in="path", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function tasksOfTheDay() {}

    /**
     * @OA\Post(
     *     path="/api/deptPerformance",
     *     summary="Get department performance stats",
     *     tags={"Daily Task Evaluation"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=false, @OA\JsonContent()),
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function getDeptPerformance() {}

    /**
     * @OA\Post(
     *     path="/api/userPerformance",
     *     summary="Get user performance stats",
     *     tags={"Daily Task Evaluation"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=false, @OA\JsonContent()),
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function getUserPerformance() {}
}
