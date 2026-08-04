<?php

namespace App\Modules\Task\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Task; // Will update when Task model is moved to module
use App\Modules\Task\Http\Requests\StoreTaskAttachmentRequest;
use App\Modules\Task\Http\Resources\TaskAttachmentResource;
use App\Modules\Task\Http\Traits\AuthorizesTaskAccess;
use App\Modules\Task\Repositories\Contracts\TaskAttachmentRepositoryInterface;
use App\Modules\Task\Services\TaskAttachmentService;
use App\Services\PlanLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TaskAttachmentController extends Controller
{
    use AuthorizesTaskAccess;

    public function __construct(
        protected PlanLimitService $planService,
        protected TaskAttachmentService $attachmentService,
        protected TaskAttachmentRepositoryInterface $attachments,
    ) {}

    public function store(StoreTaskAttachmentRequest $request, $id): JsonResponse
    {
        ini_set('max_execution_time', 10000);
        ini_set('upload_max_filesize', '100M');
        ini_set('post_max_size', '105M');

        $task = Task::with([
            'creator',
            'supervisor',
            'assignedUsers',
            'consultUsers',
            'informerUsers',
        ])->findOrFail($id);

        $this->authorizeUserForTask($task);

        $file = $request->file('file');
        $fileSizeKB = $file->getSize() / 1024;

        $this->planService->checkFeatureAccess(Auth::user()->company_id, 'limit_storage', $fileSizeKB);

        $result = $this->attachmentService->uploadAndAttach($task, $file, $request->input('comment_text'));

        return response()->json([
            'attachment'   => new TaskAttachmentResource($result['attachment']),
            'file_size'    => $result['file_size'],
            'download_url' => $result['download_url'],
        ], 201);
    }

    public function download($id, $attachmentId)
    {
        $task = Task::findOrFail($id);
        $this->authorizeUserForTask($task);

        $attachment = $this->attachments->findByTaskOrFail((int) $task->id, (int) $attachmentId);

        // Preserves your exact streaming download logic but uses the clean S3 driver
        if (!Storage::disk('spaces')->exists($attachment->file_path)) {
            return response()->json(['error' => 'File download failed'], 500);
        }

        $fileName = $task->title . ' ' . $attachment->created_at;

        return Storage::disk('spaces')->download($attachment->file_path, $fileName);
    }

    public function destroy($id): JsonResponse
    {
        $attachment = $this->attachments->findOrFail((int) $id);
        $this->authorizeUserForTask($attachment->task);

        // Deletes from Spaces
        $this->attachmentService->deleteFromStorage($attachment);

        // Deletes from DB
        $this->attachments->delete($attachment);

        return response()->json(['message' => 'File deleted successfully'], 200);
    }
}
