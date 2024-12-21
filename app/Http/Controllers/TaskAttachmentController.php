<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    public function store(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file'
        ]);

        $task = Task::findOrFail($id);
        $this->authorizeUserForTask($task);

        $file = $request->file('file');
        $path = $file->store('task_attachments', 'public');

        $attachment = TaskAttachment::create([
            'task_id' => $task->id,
            'uploaded_by_user_id' => Auth::id(),
            'file_path' => $path
        ]);

        return response()->json($attachment, 201);
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
    public function destroy(string $id)
    {
        //
    }
}
