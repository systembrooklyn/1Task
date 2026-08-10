<?php

use Illuminate\Support\Facades\Route;
use App\Modules\User\Http\Controllers\Auth\LoginController;
use App\Modules\User\Http\Controllers\Auth\RegisterController;
use App\Modules\User\Http\Controllers\Auth\PasswordResetController;
use App\Modules\User\Http\Controllers\Auth\LogoutController;
use App\Modules\User\Http\Controllers\UserController;
use App\Modules\User\Http\Controllers\UserManagementController;
use App\Modules\User\Http\Controllers\UserProfileController;
use App\Http\Controllers\CompanyOwnerController;
use App\Http\Controllers\UserDepartmentController;
use App\Modules\User\Http\Controllers\InvitationController;

Route::prefix('api')->group(function () {

    Route::post('/register', [RegisterController::class, 'register']);                                                      // check ||
    Route::post('/login', [LoginController::class, 'login']);                                                               // check ||
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);                                     // check ||
    Route::post('/reset-password', [PasswordResetController::class, 'reset']);                                              // check ||
    Route::post('/check-email', [RegisterController::class, 'checkEmail']);                                                 // check ||

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [UserController::class, 'showAuthenticated']);                                                  // check ||
        Route::post('/logout', [LogoutController::class, 'logout']);                                                        // check ||

        // Profile
        Route::get('/userProfile', [UserProfileController::class, 'index']);                                                // check ||
        Route::get('/userProfile/{id}', [UserProfileController::class, 'show']);                                            // check ||
        Route::put('/userProfile', [UserProfileController::class, 'update']);                                               // check ||
        Route::post('/user/upload-profile-picture', [UserProfileController::class, 'uploadProfilePicture']);                // check ||

        // Management
        Route::post('/edit-user/{id}', [UserManagementController::class, 'edit']);                                          // check ||
        Route::post('/fireToken', [UserManagementController::class, 'updateFireToken']);                                    // check ||
        Route::post('/users/assign-role', [UserManagementController::class, 'assignRole']);                                 // check ||
        Route::post('/unassign-role', [UserManagementController::class, 'unassignRole']);                                   // check ||
        Route::delete('/delete-user/{id}', [UserManagementController::class, 'delete']);                                    // check ||

        // Invitations
        // Route::post('/invite', [InvitationController::class, 'invite']);                                                    // check ||
        // Route::get('/invitations', [InvitationController::class, 'getInvitations']);
        Route::post('/registerViaInvitation', [RegisterController::class, 'registerViaInvitation']);

        // Company users & owners
        Route::get('/company-users', [UserController::class, 'getCompanyUsers']);                                           // check ||
        Route::get('/company-owner', [CompanyOwnerController::class, 'getCompanyOwner']);                                   // check ||
        Route::get('/isOwner', [CompanyOwnerController::class, 'checkOwner']);                                              // check ||

        // Departments assignment (these controllers remain in App\Http\Controllers)
        Route::post('/users/{userId}/assign-departments', [UserDepartmentController::class, 'assignDepartments']);          // check ||
        Route::post('/unassign-department/{userId}', [UserDepartmentController::class, 'unassignDepartment']);              // check ||
        Route::put('/department/assign-manager', [UserDepartmentController::class, 'assignManagerToDepartment']);           // check ||
        Route::get('/deptUsersFireToken/{id}', [UserDepartmentController::class, 'getUsersFireTokensInAnyDepartment']);     // check ||
    });
    // Route::post('/registerViaInvitation', [InvitationController::class, 'registerViaInvitation']);

    // Public invitation routes (no auth)
    // Route::get('/invitation/{token}', [InvitationController::class, 'registerUsingInvitation']);
    // Route::post('/invitation/{token}/register', [InvitationController::class, 'completeRegistration']);
});
