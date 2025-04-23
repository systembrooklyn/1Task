<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskCommentController extends Controller
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
            'comment_text' => 'required|string',
        ]);

        $task = Task::findOrFail($id);
        $this->authorizeUserForTask($task);

        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'comment_text' => $request->input('comment_text')
        ]);

        $relatedUsers = collect([
            $task->assignedUser,
            $task->supervisor,
            $task->creator,
            $task->consult_user_id,
            $task->inform_user_id
        ])->filter();
        foreach ($relatedUsers as $user) {
            if ($user->id !== Auth::id()) {
                $comment->users()->attach($user->id, ['read_at' => null]);
            }else{
                $comment->users()->attach($user->id, ['read_at' => now()]);
            }
        }
        return response()->json($comment, 201);
    }


    public function markCommentAsRead(Request $request)
    {
        $validated = $request->validate([
            'comment_id' => 'required|exists:task_comments,id',
        ]);
        $userId = Auth::id();
        $comment = TaskComment::findOrFail($validated['comment_id']);
        $pivot = $comment->users()->where('user_id', $userId)->first();

        if ($pivot && is_null($pivot->pivot->read_at)) {
            $comment->users()->updateExistingPivot($userId, ['read_at' => now()]);
        }

        return response()->json([
            'message' => 'Comment marked as read successfully',
        ], 200);
    }

    protected function authorizeUserForTask(Task $task)
    {
        $userId = Auth::id();
        if (!in_array($userId, [$task->creator_user_id, $task->assigned_user_id, $task->supervisor_user_id, $task->consult_user_id, $task->inform_user_id])) {
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
