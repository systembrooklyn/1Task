<?php

use Illuminate\Support\Facades\Route;
use App\Modules\DailyTask\Http\Controllers\DailyTaskController;
use App\Modules\DailyTask\Http\Controllers\DailyTaskReportController;
use App\Modules\DailyTask\Http\Controllers\DailyTaskEvaluationController;

Route::middleware('auth:sanctum')->prefix('api')->group(function () {
    Route::get('/alldailytask', [DailyTaskController::class, 'allDailyTasks']);
    Route::post('/alldailytaskfilter', [DailyTaskController::class, 'allDailyTasksFiltered']);
    Route::post('/activedailytask/{id}', [DailyTaskController::class, 'activeDailyTask']);
    Route::get('dailytask/{id}/revisions', [DailyTaskController::class, 'revisions']); // auth company
    Route::apiResource('dailytask', DailyTaskController::class)->except(['create', 'edit']);

    Route::post('/daily-tasks/{id}/submit-report', [DailyTaskReportController::class, 'submitReport']);
    Route::get('/daily-tasks/todays-reports', [DailyTaskReportController::class, 'todaysReports']);
    Route::get('/daily-tasks-reports/{date?}', [DailyTaskReportController::class, 'index']);
    Route::get('/daily-task-reports/{date}', [DailyTaskReportController::class, 'notReportedTasks']);
    Route::get('evaluations/{taskId}', [DailyTaskEvaluationController::class, 'index']);
    Route::get('evaluation/{id}', [DailyTaskEvaluationController::class, 'show']);
    Route::post('evaluations/{taskId}', [DailyTaskEvaluationController::class, 'store']);
    Route::put('evaluations/{id}', [DailyTaskEvaluationController::class, 'update']);
    Route::get('daily-tasks-evaluations/{date?}', [DailyTaskEvaluationController::class, 'tasksOfTheDay']);

    Route::post('deptPerformance', [DailyTaskEvaluationController::class, 'getDeptPerformance']);
    Route::post('userPerformance', [DailyTaskEvaluationController::class, 'getUserPerformance']);

    Route::get('dailyTasks/yesterday', [DailyTaskController::class, 'getYesterdayEvaluationTasks']);
    Route::post('dailyTasks/setRandomCount', [DailyTaskController::class, 'updateRandomTaskCount']);
});
