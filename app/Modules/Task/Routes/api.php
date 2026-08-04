<?php

use App\Modules\Task\Http\Controllers\TaskAttachmentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('api')->group(function () {
    Route::post('/tasks/{id}/attachments', [TaskAttachmentController::class, 'store']);
    Route::get('/tasks/{id}/attachments/{attachmentId}/download', [TaskAttachmentController::class, 'download'])
        ->name('attachments.download');

    // kept commented exactly as in your current routes/api.php
    // Route::delete('/attachments/{id}', [TaskAttachmentController::class, 'destroy'])->name('attachments.delete');
});
