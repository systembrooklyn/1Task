<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Plan\Http\Controllers\PlanController;
use App\Modules\Plan\Http\Controllers\FeatureController;

Route::prefix('api')->group(function () {

    Route::get('plans/all', [PlanController::class, 'allPlans']);
    Route::prefix('features')->group(function () {
        Route::get('/', [FeatureController::class, 'index']);
        // Route::post('/', [FeatureController::class, 'store']);
    });

    Route::middleware('auth:sanctum')->get('plans', [PlanController::class, 'index']);

    Route::prefix('plans')->middleware('admin.token')->group(function () {
        Route::get('adminPlans', [PlanController::class, 'adminPlans']);
        Route::post('adminPlans', [PlanController::class, 'store']);
        Route::post('{plan}/features', [PlanController::class, 'attachFeatures']);
    });
});
