<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\InvitationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('departments', DepartmentsController::class)->middleware('auth:sanctum');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])
->middleware('auth:sanctum');
Route::post('forgot-password', [AuthController::class, 'sendPasswordResetLink']);


// Route for resetting the password
Route::post('reset-password', [AuthController::class, 'resetPassword']);
Route::post('/check-email', [AuthController::class, 'checkEmailExists']);

Route::middleware('auth:sanctum')->post('/invite', [InvitationController::class, 'invite']);
Route::post('/registerViaEmail', [AuthController::class, 'registerViaEmail']);
Route::get('invitation/{token}', [InvitationController::class, 'registerUsingInvitation']);
Route::post('invitation/{token}/register', [InvitationController::class, 'completeRegistration']);