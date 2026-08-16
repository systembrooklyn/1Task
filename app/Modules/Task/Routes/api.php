<?php

use App\Modules\Task\Http\Controllers\TaskAttachmentController;
use App\Modules\Task\Http\Controllers\TaskCommentController;
use App\Modules\Task\Http\Controllers\TaskCommentReplyController;
use App\Modules\Task\Http\Controllers\TaskController;
use App\Modules\Task\Http\Controllers\TaskRevisionController;
use App\Modules\Task\Http\Controllers\TaskUserStatusController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('api')->group(function () {
    Route::post('/tasks/{id}/attachments', [TaskAttachmentController::class, 'store']);
    Route::get('/tasks/{id}/attachments/{attachmentId}/download', [TaskAttachmentController::class, 'download'])
        ->name('attachments.download');

    // kept commented exactly as in your current routes/api.php
    // Route::delete('/attachments/{id}', [TaskAttachmentController::class, 'destroy'])->name('attachments.delete');

    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::put('tasks/{taskId}/status', [TaskController::class, 'updateStatus']);
    Route::post('/tasks/{id}/comments', [TaskCommentController::class, 'store']);
    Route::post('/tasks/{id}/star', [TaskUserStatusController::class, 'toggleStar']);
    Route::post('/tasks/{id}/archive', [TaskUserStatusController::class, 'toggleArchive']);
    Route::get('/tasks/{id}/revisions', [TaskRevisionController::class, 'index']);

    Route::post('taskComments/{commentId}/replies', [TaskCommentReplyController::class, 'addReply']);
    Route::get('taskComments/{commentId}/replies', [TaskCommentReplyController::class, 'getReplies']);
    Route::put('taskCommentReplies/{replyId}', [TaskCommentReplyController::class, 'updateReply']);

    Route::post('taskComments/read', [TaskCommentController::class, 'markCommentAsRead']);
    Route::post('taskReplies/read', [TaskCommentReplyController::class, 'markReplyAsRead']);
});
