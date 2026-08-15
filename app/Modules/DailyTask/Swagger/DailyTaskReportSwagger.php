<?php

namespace App\Modules\DailyTask\Swagger;

/**
 * @OA\Tag(
 *     name="Daily Task Report",
 *     description="Daily task reporting and tracking endpoints"
 * )
 */
class DailyTaskReportSwagger
{
    /**
     * @OA\Post(
     *     path="/api/daily-tasks/{id}/submit-report",
     *     summary="Submit report for a task",
     *     tags={"Daily Task Report"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent()),
     *     @OA\Response(response=201, description="Report submitted successfully"),
     *     @OA\Response(response=409, description="Report already exists")
     * )
     */
    public function submitReport() {}

    /**
     * @OA\Get(
     *     path="/api/daily-tasks/todays-reports",
     *     summary="Get today's reports overview",
     *     tags={"Daily Task Report"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function todaysReports() {}

    /**
     * @OA\Get(
     *     path="/api/daily-tasks-reports/{date?}",
     *     summary="Get daily task reports by date",
     *     tags={"Daily Task Report"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="date", in="path", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function index() {}

    /**
     * @OA\Get(
     *     path="/api/daily-task-reports/{date}",
     *     summary="Get un-reported tasks list for a date",
     *     tags={"Daily Task Report"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="date", in="path", required=true, @OA\Schema(type="string", format="date")),
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function notReportedTasks() {}
}
