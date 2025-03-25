<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyOwnerController;
use App\Http\Controllers\DailyTaskController;
use App\Http\Controllers\DailyTaskEvaluationController;
use App\Http\Controllers\DailyTaskReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\DigitalCardController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\TaskAttachmentController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\TaskCommentRepliesController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskRevisionController;
use App\Http\Controllers\TaskUserStatusController;
use App\Http\Controllers\UserDepartmentController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;


Route::get('/user', function (Request $request) {
    $user = $request->user()->load([
        'company', 
        'departments', 
        'roles.permissions'
    ]);
    $response = [
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'google_id' => $user->google_id,
            'company' => [
                'id' => $user->company->id,
                'name' => $user->company->name,
            ],
            'departments' => $user->departments->map(function ($department) {
                return [
                    'id' => $department->id,
                    'name' => $department->name,
                ];
            }),
        ],
        'roles' => $user->roles->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
            ];
        }),
        'permissions' => $user->roles->flatMap(function ($role) {
            return $role->permissions->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                ];
            });
        })->unique('id'),
        'token' => $request->bearerToken(),
    ];
    return response()->json($response);
})->middleware('auth:sanctum');

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
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

    Route::post('edit-user/{id}',[AuthController::class,'editUser']);

    Route::post('roles', [RolePermissionController::class, 'createRole']);  
    Route::get('roles', [RolePermissionController::class, 'getRoles']);      
    Route::get('roles/{id}', [RolePermissionController::class, 'getRole']); 
    Route::put('roles/{id}', [RolePermissionController::class, 'updateRole']);
    Route::delete('roles/{id}', [RolePermissionController::class, 'deleteRole']); 
    Route::post('/roles/assign-permissions', [RolePermissionController::class, 'assignPermissions']);
    Route::get('/roles/get-permissions/{id}', [RolePermissionController::class, 'getRolePermissions']);
    Route::post('/users/assign-role', [AuthController::class, 'assignRoleToUser']);
    Route::post('/roles/remove-permissions', [RolePermissionController::class, 'removePermissionsFromRole']);

    Route::post('/users/{userId}/assign-departments', [UserDepartmentController::class, 'assignDepartments']);
    Route::get('/departments-users', [UserDepartmentController::class, 'getUsersInDepartment']);
    Route::post('/unassign-department/{userId}', [UserDepartmentController::class, 'unassignDepartment']);
    Route::put('/department/assign-manager', [UserDepartmentController::class, 'assignManagerToDepartment']);
    Route::get('company-owner', [CompanyOwnerController::class, 'getCompanyOwner']);
    Route::get('isOwner', [CompanyOwnerController::class, 'checkOwner']);
    
    
    Route::get('company-users', [CompanyController::class, 'getCompanyUsers']);
    Route::post('/unassign-role', [AuthController::class, 'unassignRoleFromUser']);
    Route::delete('/delete-user/{id}', [AuthController::class, 'deleteUser']);
    
    
    Route::apiResource('projects', ProjectController::class);
    Route::post('/projects/{id}/status', [ProjectController::class, 'updatestatus']);
    Route::get('/projects/{id}/revisions', [ProjectController::class, 'getRevisions']);


    Route::get('/alldailytask',[DailyTaskController::class, 'allDailyTasks']);
    Route::post('/alldailytaskfilter',[DailyTaskController::class, 'allDailyTasksFiltered']);
    // Route::post('/submitdailytask/{id}',[DailyTaskController::class, 'submitDailyTask']);
    Route::post('/activedailytask/{id}',[DailyTaskController::class,'activeDailyTask']);
    Route::get('dailytask/{id}/revisions', [DailyTaskController::class, 'revisions']);
    Route::apiResource('dailytask', DailyTaskController::class);
    Route::post('/daily-tasks/{id}/submit-report', [DailyTaskReportController::class, 'submitReport']);
    Route::get('/daily-tasks/todays-reports', [DailyTaskReportController::class, 'todaysReports']);
    Route::get('/daily-task-reports', [DailyTaskReportController::class, 'index']);
    Route::get('/daily-task-reports/{date}', [DailyTaskReportController::class, 'notReportedTasks']);

    Route::get('evaluations/{taskId}', [DailyTaskEvaluationController::class, 'index']);
    Route::get('evaluation/{id}', [DailyTaskEvaluationController::class, 'show']);
    Route::post('evaluations/{taskId}', [DailyTaskEvaluationController::class, 'store']);
    Route::put('evaluations/{id}', [DailyTaskEvaluationController::class, 'update']);
    Route::delete('evaluations/{id}', [DailyTaskEvaluationController::class, 'destroy']);
    Route::get('daily-tasks-evaluations/{date?}', [DailyTaskEvaluationController::class, 'tasksOfTheDay']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
    Route::put('tasks/{taskId}/status', [TaskController::class, 'updateStatus']);
    Route::post('/tasks/{id}/comments', [TaskCommentController::class, 'store']);
    Route::post('/tasks/{id}/attachments', [TaskAttachmentController::class, 'store']);
    Route::post('/tasks/{id}/star', [TaskUserStatusController::class, 'toggleStar']);
    Route::post('/tasks/{id}/archive', [TaskUserStatusController::class, 'toggleArchive']);
    Route::get('/tasks/{id}/revisions', [TaskRevisionController::class, 'index']);
    Route::delete('/attachments/{id}', [TaskAttachmentController::class, 'destroy'])->name('attachments.delete');
    Route::get('/attachments/{id}/download', [TaskAttachmentController::class, 'download'])->name('attachments.download');

    Route::post('taskComments/{commentId}/replies', [TaskCommentRepliesController::class, 'addReply']);
    Route::get('taskComments/{commentId}/replies', [TaskCommentRepliesController::class, 'getReplies']);
    Route::put('taskCommentReplies/{replyId}', [TaskCommentRepliesController::class, 'updateReply']);
    Route::delete('taskCommentReplies/{replyId}', [TaskCommentRepliesController::class, 'deleteReply']);
    
});


/**
 * digital users
*/
Route::get('/digital-card/view/{user_code}', [DigitalCardController::class, 'viewDigitalCard']);
Route::post('/digital-card/register', [DigitalCardController::class, 'register']);
Route::post('/digital-card/verify-code', [DigitalCardController::class, 'verifyCode']);
Route::post('/digital-card/login', [DigitalCardController::class, 'login']);
// Route::middleware('auth:digital_card_users')->put('/digital-card/update', [DigitalCardController::class, 'updateDigitalCard']);

Route::middleware('auth:digital_card_users')->group(function () {
            Route::get('/digital-card/user', [DigitalCardController::class, 'getDigitalCard']);
            Route::put('/digital-card/update', [DigitalCardController::class, 'updateDigitalCard']);
            Route::post('/digital-card/delete', [DigitalCardController::class, 'deleteAccount']);
});


Route::middleware('auth:sanctum')->get('/dashboard/{date?}', [DashboardController::class, 'getCounts']);
Route::middleware('auth:sanctum')->get('/getInvitations', [InvitationController::class, 'getInvitations']);
/**
 * Google login
 */
Route::get('/auth/google/redirect', [GoogleController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);