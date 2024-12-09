<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\RoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])
->middleware('auth:sanctum');
Route::post('forgot-password', [AuthController::class, 'sendPasswordResetLink']);


// Route for resetting the password
Route::post('reset-password', [AuthController::class, 'resetPassword']);
Route::post('/check-email', [AuthController::class, 'checkEmailExists']);

Route::middleware('auth:sanctum')->post('/invite', [InvitationController::class, 'invite']);
Route::post('/registerViaInvitation', [AuthController::class, 'registerViaInvitation']);
Route::get('invitation/{token}', [InvitationController::class, 'registerUsingInvitation']);
Route::post('invitation/{token}/register', [InvitationController::class, 'completeRegistration']);
Route::apiResource('departments', DepartmentsController::class)->middleware('auth:sanctum');



use App\Http\Controllers\RolePermissionController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('permissions', [RolePermissionController::class, 'getPermissions']);
    Route::get('permissions/{id}', [RolePermissionController::class, 'getPermission']);

    // Roles Routes
    Route::post('roles', [RolePermissionController::class, 'createRole']);  // Create Role
    Route::get('roles', [RolePermissionController::class, 'getRoles']);      // Get All Roles
    Route::get('roles/{id}', [RolePermissionController::class, 'getRole']);  // Get Specific Role
    Route::put('roles/{id}', [RolePermissionController::class, 'updateRole']); // Update Role
    Route::delete('roles/{id}', [RolePermissionController::class, 'deleteRole']); // Delete Role
    Route::post('/roles/assign-permissions', [RolePermissionController::class, 'assignPermissions']);
    Route::get('/roles/get-permissions/{id}', [RolePermissionController::class, 'getRolePermissions']);
    Route::post('/users/assign-role', [AuthController::class, 'assignRoleToUser']);
    Route::post('/roles/remove-permissions', [RolePermissionController::class, 'removePermissionsFromRole']);
});
