<?php

use Illuminate\Support\Facades\Route;
use App\Modules\DigitalCard\Http\Controllers\AuthController;
use App\Modules\DigitalCard\Http\Controllers\DigitalCardController;


Route::prefix('api')->group(function () {
    Route::post('/digital-card/register', [AuthController::class, 'register']);
    Route::post('/digital-card/verify-code', [AuthController::class, 'verifyCode']);
    Route::post('/digital-card/login', [AuthController::class, 'login']);

    Route::middleware('auth:digital_card_users')->group(function () {
        Route::get('/digital-card/user', [DigitalCardController::class, 'getDigitalCard']);
        Route::put('/digital-card/update', [DigitalCardController::class, 'updateDigitalCard']);
        // (Delete account route is commented out in original; if needed, uncomment)
        // Route::post('/digital-card/delete', [DigitalCardController::class, 'deleteAccount']);
    });

    Route::get('/digital-card/view/{user_code}', [DigitalCardController::class, 'viewDigitalCard']);
});
