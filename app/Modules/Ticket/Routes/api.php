<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Ticket\Http\Controllers\TicketController;
use App\Modules\Ticket\Http\Controllers\TicketActionController;

Route::middleware('auth:sanctum')->prefix('api')->group(function () {
    Route::prefix('tickets')->group(function () {
        Route::post('/', [TicketController::class, 'store']);
        Route::get('/categories', [TicketController::class, 'ticketCategories']);
        Route::post('/{ticket}/actions', [TicketActionController::class, 'store']);
    });
});
