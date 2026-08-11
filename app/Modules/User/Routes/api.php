<?php

use Illuminate\Support\Facades\Route;
use App\Modules\User\Http\Controllers\Auth\LoginController;
use App\Modules\User\Http\Controllers\Auth\RegisterController;
use App\Modules\User\Http\Controllers\Auth\PasswordResetController;
use App\Modules\User\Http\Controllers\Auth\LogoutController;
use App\Modules\User\Http\Controllers\CompanyOwnerController;
use App\Modules\User\Http\Controllers\UserController;
use App\Modules\User\Http\Controllers\UserManagementController;
use App\Modules\User\Http\Controllers\UserProfileController;
use App\Modules\User\Http\Controllers\InvitationController;
use App\Modules\User\Http\Controllers\UserDepartmentController;

Route::prefix('api')->group(function () {

    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
    Route::post('/reset-password', [PasswordResetController::class, 'reset']);
    Route::post('/check-email', [RegisterController::class, 'checkEmail']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [UserController::class, 'showAuthenticated']);
        Route::post('/logout', [LogoutController::class, 'logout']);

        // Profile
        Route::get('/userProfile', [UserProfileController::class, 'index']);
        Route::get('/userProfile/{id}', [UserProfileController::class, 'show']);
        Route::put('/userProfile', [UserProfileController::class, 'update']);
        Route::post('/user/upload-profile-picture', [UserProfileController::class, 'uploadProfilePicture']);

        // Management
        Route::post('/edit-user/{id}', [UserManagementController::class, 'edit']);
        Route::post('/fireToken', [UserManagementController::class, 'updateFireToken']);
        Route::post('/users/assign-role', [UserManagementController::class, 'assignRole']);
        Route::post('/unassign-role', [UserManagementController::class, 'unassignRole']);
        Route::delete('/delete-user/{id}', [UserManagementController::class, 'delete']);

        // Company users & owners
        Route::get('/company-users', [UserController::class, 'getCompanyUsers']);
        Route::get('/company-owner', [CompanyOwnerController::class, 'getCompanyOwner']);
        Route::get('/isOwner', [CompanyOwnerController::class, 'checkOwner']);

        // Department assignments
        Route::post('/users/{userId}/assign-departments', [UserDepartmentController::class, 'assignDepartments']);
        Route::post('/unassign-department/{userId}', [UserDepartmentController::class, 'unassignDepartment']);
        Route::put('/department/assign-manager', [UserDepartmentController::class, 'assignManagerToDepartment']);
        Route::get('/deptUsersFireToken/{id}', [UserDepartmentController::class, 'getUsersFireTokensInAnyDepartment']);
        Route::get('/departments-users', [UserDepartmentController::class, 'getUsersInDepartment']);
    });

    // Invitations
    Route::post('/registerViaInvitation', [InvitationController::class, 'registerViaInvitation']);
    Route::get('/invitation/{token}', [InvitationController::class, 'registerUsingInvitation']);
    /** quick registration without attaching any roles to the user */
    Route::post('/invitation/{token}/register', [InvitationController::class, 'completeRegistration']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/invite', [InvitationController::class, 'invite']);
        Route::get('/getInvitations', [InvitationController::class, 'getInvitations']);
    });
});
