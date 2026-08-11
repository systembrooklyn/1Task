<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Department\Http\Controllers\DepartmentController;

Route::middleware('auth:sanctum')->prefix('api')->group(function () {
    Route::apiResource('departments', DepartmentController::class);
});
