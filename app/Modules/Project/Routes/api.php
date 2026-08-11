<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Project\Http\Controllers\ProjectController;

Route::middleware('auth:sanctum')->prefix('api')->group(function () {
    Route::apiResource('projects', ProjectController::class);
    Route::post('/projects/{id}/status', [ProjectController::class, 'updatestatus']);
    Route::get('/projects/{id}/revisions', [ProjectController::class, 'getRevisions']);
});
