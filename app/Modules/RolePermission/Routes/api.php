<?php

use Illuminate\Support\Facades\Route;
use App\Modules\RolePermission\Http\Controllers\RoleController;
use App\Modules\RolePermission\Http\Controllers\PermissionController;

Route::middleware('auth:sanctum')->prefix('api')->group(function () {
    // Permissions
    Route::get('permissions', [PermissionController::class, 'index']);
    Route::get('permissions/{id}', [PermissionController::class, 'show']);

    // Roles
    Route::post('roles', [RoleController::class, 'store']);
    Route::get('roles', [RoleController::class, 'index']);
    Route::get('roles/{id}', [RoleController::class, 'show']);
    Route::put('roles/{id}', [RoleController::class, 'update']);
    Route::delete('roles/{id}', [RoleController::class, 'destroy']);

    // Role-Permission assignments
    Route::post('roles/assign-permissions', [RoleController::class, 'assignPermissions']);
    Route::get('roles/get-permissions/{id}', [RoleController::class, 'getRolePermissions']);
    Route::post('roles/remove-permissions', [RoleController::class, 'removePermissions']);
});
