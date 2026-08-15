<?php

namespace App\Modules\Task\Services;

use App\Models\Task;
use App\Models\TaskComment;
use App\Modules\Task\Models\TaskAttachment;
use App\Modules\Task\Repositories\Contracts\TaskAttachmentRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TaskAttachmentService
{
    protected array $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'jfif'];

    public function __construct(protected TaskAttachmentRepositoryInterface $attachments) {}

    public function uploadAndAttach(Task $task, UploadedFile $file, ?string $commentText): array
    {
        $company = Auth::user()->company;

        $path        = $file->store("1Task/{$company->name}/tasks/{$task->id}/task_attachments", 'spaces');
        $downloadUrl = Storage::disk('spaces')->url($path);
        $fileSizeKB  = $file->getSize() / 1024;

        // 2) Persist attachment record via Contract
        $attachment = $this->attachments->create([
            'task_id'             => $task->id,
            'uploaded_by_user_id' => Auth::id(),
            'file_path'           => $path,
            'file_name'           => basename($path),
            'file_size'           => $fileSizeKB,
            'download_url'        => $downloadUrl,
        ]);

        // 3) Auto-comment + read/unread for related users
        $this->createAttachmentComment($task, $downloadUrl, $file, $commentText);

        return [
            'attachment'   => $attachment,
            'file_size'    => $fileSizeKB,
            'download_url' => $downloadUrl,
        ];
    }

    public function deleteFromStorage(TaskAttachment $attachment): void
    {
        if (Storage::disk('spaces')->exists($attachment->file_path)) {
            Storage::disk('spaces')->delete($attachment->file_path);
        }
    }

    protected function createAttachmentComment(Task $task, string $downloadUrl, UploadedFile $file, ?string $commentText): void
    {
        $comment = TaskComment::create([
            'task_id'      => $task->id,
            'user_id'      => Auth::id(),
            'comment_text' => $this->buildCommentHtml($downloadUrl, $file, $commentText),
            'created_at'   => now(),
        ]);

        $relatedUsers = collect([
            $task->creator,
            $task->supervisor,
            ...$task->assignedUsers->all(),
            ...$task->consultUsers->all(),
            ...$task->informerUsers->all(),
        ])->filter()->unique('id');

        foreach ($relatedUsers as $user) {
            $comment->users()->attach($user->id, [
                'read_at' => $user->id === Auth::id() ? now() : null,
            ]);
        }
    }

    protected function buildCommentHtml(string $downloadUrl, UploadedFile $file, ?string $commentText): string
    {
        $originalName = $file->getClientOriginalName();
        $isImage = in_array(strtolower($file->getClientOriginalExtension()), $this->imageExtensions, true);

        if ($isImage) {
            return "<div class='w-50 imgComment'>
                <a href='{$downloadUrl}' target='blank' download='{$originalName}' style='text-decoration: none; display: inline-block;'>
                <img src='{$downloadUrl}' alt='{$originalName}' style='max-width: 100%; height: auto; cursor: pointer;'/>
                </a>
                {$commentText}
            </div>";
        }

        return "<div class='fileComment'>
            <a href='{$downloadUrl}' download='{$originalName}' style='display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px;'>Download File: {$originalName}</a>
            {$commentText}
        </div>";
    }
}
