<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TaskAttachmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request, $id)
    // {
    //     $request->validate([
    //         'file' => 'required|file'
    //     ]);

    //     $task = Task::findOrFail($id);
    //     $this->authorizeUserForTask($task);

    //     $file = $request->file('file');
    //     $path = $file->store('task_attachments', 'public');

    //     $attachment = TaskAttachment::create([
    //         'task_id' => $task->id,
    //         'uploaded_by_user_id' => Auth::id(),
    //         'file_path' => $path
    //     ]);

    //     return response()->json($attachment, 201);
    // }

    public function store(Request $request, $id)
    {
        ini_set('max_execution_time', 10000);
        $request->validate([
            'file' => 'required|file',
            'comment_text' => 'nullable|string',
        ]);
        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $fileOriginalName = $file->getClientOriginalName();
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'jfif'];
        $dataType = '';
        $isImage = in_array($extension, $imageExtensions);
        $task = Task::findOrFail($id);
        $user = Auth::user();
        $company = $user->company;
        $this->authorizeUserForTask($task);
        $file = $request->file('file');
        $firebaseConfig = [
            'apiKey' => "AIzaSyC8p6mRMJEuv0y4AFA6GP0fVPlQyyRAWhQ",
            'authDomain' => "brooklyn-chat.firebaseapp.com",
            'databaseURL' => "https://brooklyn-chat-default-rtdb.europe-west1.firebasedatabase.app",
            'projectId' => "brooklyn-chat",
            'storageBucket' => "brooklyn-chat.appspot.com",
            'messagingSenderId' => "450185737947",
            'appId' => "1:450185737947:web:a7dce19db9e0b37478fefe"
        ];
        $storageBucket = $firebaseConfig['storageBucket'];
        $filePath = "1Task/{$company->name}/task_attachments/{$file->hashName()}";
        $firebaseStorageUrl = "https://firebasestorage.googleapis.com/v0/b/{$storageBucket}/o/" . urlencode($filePath) . "?uploadType=media";
        $uploadToken = "YOUR_UPLOAD_TOKEN";
        $fileContent = fopen($file->getPathname(), 'r');
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$uploadToken}",
            'Content-Type' => $file->getMimeType(),
        ])
        ->timeout(300)
        ->withBody($fileContent, $file->getMimeType())
        ->post($firebaseStorageUrl);
        fclose($fileContent);
        if ($response->successful()) {
            $fileMetadata = $response->json();
            $fileName = basename($fileMetadata['name']);
            $fileSize = $fileMetadata['size'] / 1024;
            $downloadToken = $fileMetadata['downloadTokens'];
            $downloadUrl = "https://firebasestorage.googleapis.com/v0/b/{$storageBucket}/o/" .
                urlencode($filePath) . "?alt=media&token={$downloadToken}";
            $attachment = TaskAttachment::create([
                'task_id' => $task->id,
                'uploaded_by_user_id' => Auth::id(),
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_size' => $fileSize,
                'download_url' => $downloadUrl,
            ]);
            if ($isImage) {
                $dataType = "<div class='w-50 imgComment'>
                    <a href='{$downloadUrl}' target='blank' download='{$fileOriginalName}' style='text-decoration: none; display: inline-block;'>
                    <img src='{$downloadUrl}' alt='{$fileOriginalName}' style='max-width: 100%; height: auto; cursor: pointer;'/>
                    </a>
                    $request->comment_text
                </div>";
            } else {
                $dataType = "<div class='fileComment'>
                    <a href='{$downloadUrl}' download='{$fileOriginalName}' style='display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px;'>Download File: {$fileOriginalName}</a>
                    $request->comment_text
                </div>";
            }
            $comment = TaskComment::create([
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'comment_text' => $dataType,
                'created_at' => now()
            ]);
            $relatedUsers = collect([
                $task->assignedUser,
                $task->supervisor,
                $task->creator,
                $task->consult,
                $task->informer,
            ])->filter();
            foreach ($relatedUsers as $user) {
                if ($user->id !== Auth::id()) {
                    $comment->users()->attach($user->id, ['read_at' => null]);
                } else {
                    $comment->users()->attach($user->id, ['read_at' => now()]);
                }
            }
            return response()->json([
                'attachment' => $attachment,
                'file_size' => $fileSize,
                'download_url' => $downloadUrl
            ], 201);
        }
        return response()->json(['error' => 'File upload failed', 'details' => $response->body()], 500);
    }

    protected function authorizeUserForTask(Task $task)
    {
        $userId = Auth::id();
        if (!in_array($userId, [$task->creator_user_id, $task->assigned_user_id, $task->supervisor_user_id])) {
            abort(403, 'Forbidden');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(string $id)
    // {
    //     $attachment = TaskAttachment::findOrFail($id);
    // $this->authorizeUserForTask($attachment->task);
    // if (Storage::disk('public')->exists($attachment->file_path)) {
    //     Storage::disk('public')->delete($attachment->file_path);
    // }
    // $attachment->delete();

    // return response()->json(['message' => 'Attachment deleted successfully'], 200);
    // }
    public function destroy($id)
    {
        $attachment = TaskAttachment::findOrFail($id);
        $this->authorizeUserForTask($attachment->task);
        $firebaseConfig = [
            'storageBucket' => "brooklyn-chat.appspot.com",
        ];
        $storageBucket = $firebaseConfig['storageBucket'];
        $filePath = parse_url($attachment->file_path, PHP_URL_PATH);
        $filePath = ltrim($filePath, '/');
        $firebaseDeletionUrl = "https://firebasestorage.googleapis.com/v0/b/{$storageBucket}/o/" . urlencode($filePath);
        $deleteToken = "YOUR_DELETE_TOKEN";
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$deleteToken}",
        ])->delete($firebaseDeletionUrl);
        if ($response->successful()) {
            $attachment->delete();
            return response()->json(['message' => 'File deleted successfully'], 200);
        }
        return response()->json([
            'error' => 'File deletion failed',
            'details' => $response->body(),
        ], 500);
    }




    // public function download($id)
    // {
    //     $attachment = TaskAttachment::findOrFail($id);
    //     $this->authorizeUserForTask($attachment->task);
    //     if (!Storage::disk('public')->exists($attachment->file_path)) {
    //         return response()->json(['message' => 'File not found'], 404);
    //     }
    //     return Storage::disk('public')->download($attachment->file_path);
    // }
    public function download($id, $attachmentId)
    {
        $task = Task::findOrFail($id);
        $this->authorizeUserForTask($task);
        $attachment = TaskAttachment::where('task_id', $task->id)
            ->findOrFail($attachmentId);
        $downloadUrl = $attachment->download_url;
        $response = Http::withHeaders([
            'Authorization' => "Bearer YOUR_UPLOAD_TOKEN",
        ])->get($downloadUrl);
        if ($response->successful()) {
            $fileContent = $response->body();
            $contentType = $response->header('Content-Type');
            return response()->stream(
                function () use ($fileContent) {
                    echo $fileContent;
                },
                200,
                [
                    'Content-Type' => $contentType,
                    'Content-Disposition' => 'attachment; filename="' . $task->title . $attachment->created_at . '"',
                ]
            );
        }
        return response()->json(['error' => 'File download failed'], 500);
    }
}
