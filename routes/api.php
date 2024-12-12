<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyOwnerController;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\RoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\UserDepartmentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])
->middleware('auth:sanctum');
Route::post('forgot-password', [AuthController::class, 'sendPasswordResetLink']);


Route::post('reset-password', [AuthController::class, 'resetPassword']);
Route::post('/check-email', [AuthController::class, 'checkEmailExists']);

Route::middleware('auth:sanctum')->post('/invite', [InvitationController::class, 'invite']);
Route::post('/registerViaInvitation', [AuthController::class, 'registerViaInvitation']);
Route::get('invitation/{token}', [InvitationController::class, 'registerUsingInvitation']);
Route::post('invitation/{token}/register', [InvitationController::class, 'completeRegistration']);
Route::apiResource('departments', DepartmentsController::class)->middleware('auth:sanctum');




Route::middleware('auth:sanctum')->group(function () {
    Route::get('permissions', [RolePermissionController::class, 'getPermissions']);
    Route::get('permissions/{id}', [RolePermissionController::class, 'getPermission']);

    Route::post('roles', [RolePermissionController::class, 'createRole']);  
    Route::get('roles', [RolePermissionController::class, 'getRoles']);      
    Route::get('roles/{id}', [RolePermissionController::class, 'getRole']); 
    Route::put('roles/{id}', [RolePermissionController::class, 'updateRole']);
    Route::delete('roles/{id}', [RolePermissionController::class, 'deleteRole']); 
    Route::post('/roles/assign-permissions', [RolePermissionController::class, 'assignPermissions']);
    Route::get('/roles/get-permissions/{id}', [RolePermissionController::class, 'getRolePermissions']);
    Route::post('/users/assign-role', [AuthController::class, 'assignRoleToUser']);
    Route::post('/roles/remove-permissions', [RolePermissionController::class, 'removePermissionsFromRole']);
});

Route::middleware('auth:sanctum')->post('/users/{userId}/assign-departments', [UserDepartmentController::class, 'assignDepartments']);
Route::middleware('auth:sanctum')->get('/departments-users', [UserDepartmentController::class, 'getUsersInDepartment']);
Route::middleware('auth:sanctum')->post('/unassign-department/{userId}', [UserDepartmentController::class, 'unassignDepartment']);
Route::middleware('auth:sanctum')->put('/department/assign-manager', [UserDepartmentController::class, 'assignManagerToDepartment']);


Route::middleware('auth:sanctum')->get('company-owner/{company_id}', [CompanyOwnerController::class, 'getCompanyOwner']);
Route::middleware('auth:sanctum')->get('isOwner', [CompanyOwnerController::class, 'checkOwner']);


Route::middleware('auth:sanctum')->get('company-users', [CompanyController::class, 'getCompanyUsers']);