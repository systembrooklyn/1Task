<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskRevision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $tasks = Task::select(
            'id',
            'company_id',
            'project_id',
            'department_id',
            'creator_user_id',
            'assigned_user_id',
            'supervisor_user_id',
            'title',
            'description',
            'start_date',
            'deadline',
            'is_urgent',
            'priority',
            'status',
            'created_at',
            'updated_at'
        )
        ->where('creator_user_id', $userId)
        ->orWhere('assigned_user_id', $userId)
        ->orWhere('supervisor_user_id', $userId)
        ->withCount('comments')
        ->with([
            'creator:id,name',
            'assignedUser:id,name',
            'supervisor:id,name',
            'project:id,name',
            'department:id,name',
            'userStatuses' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            },
        ])
        ->get();
        $tasks->each(function ($task) {
            $status = $task->userStatuses->first();
            $task->is_starred = $status ? $status->is_starred : false;
            $task->is_archived = $status ? $status->is_archived : false;
            $task->makeHidden(['company_id', 'department_id', 'project_id', 'creator_user_id', 'assigned_user_id', 'supervisor_user_id', 'userStatuses']);
            if ($task->project) {
                $task->project->setAppends([]);
            }
        });

        return response()->json($tasks, 200);
    }

    // public function index(Request $request)
    // {
    //     $user = $request->user();
    //     if (Gate::allows('view-any', Task::class)) {
    //         $tasks = Task::with([
    //                 'project:id,name', 
    //                 'department:id,name', 
    //                 'creator:id,name', 
    //                 'assignedUser:id,name', 
    //                 'supervisor:id,name',
    //                 'userStatuses' => function ($query) use ($user) {
    //                     $query->where('user_id', $user->id);
    //                 }
    //             ])
    //             ->where('company_id', $user->company_id)
    //             ->get()
    //             ->map(function($task) {
    //                 $status = $task->userStatuses->first();
    //                 $task->is_starred = $status ? $status->is_starred : false;
    //                 $task->is_archived = $status ? $status->is_archived : false;
    //                 $task->makeHidden(['company_id', 'department_id', 'project_id', 'creator_user_id', 'assigned_user_id', 'supervisor_user_id', 'userStatuses']);
    
    //                 if ($task->project) {
    //                     $task->project->setAppends([]);
    //                 }
    
    //                 return [
    //                     'id' => $task->id,
    //                     'title' => $task->title,
    //                     'description' => $task->description,
    //                     'start_date' => $task->start_date,
    //                     'deadline' => $task->deadline,
    //                     'is_urgent' => $task->is_urgent,
    //                     'priority' => $task->priority,
    //                     'status' => $task->status,
    //                     'is_starred' => $task->is_starred,
    //                     'is_archived' => $task->is_archived,
    //                     'project' => $task->project->only(['id', 'name']),
    //                     'department' => $task->department->only(['id', 'name']),
    //                     'creator' => $task->creator->only(['id', 'name']),
    //                     'assignedUser' => $task->assignedUser->only(['id', 'name']),
    //                     'supervisor' => $task->supervisor->only(['id', 'name']),
    //                 ];
    //             });
    //     } else {
    //         $tasks = Task::with([
    //                 'project:id,name', 
    //                 'department:id,name', 
    //                 'creator:id,name', 
    //                 'assignedUser:id,name', 
    //                 'supervisor:id,name',
    //                 'userStatuses' => function ($query) use ($user) {
    //                     $query->where('user_id', $user->id);
    //                 }
    //             ])
    //             ->where(function($query) use ($user) {
    //                 $query->where('assigned_user_id', $user->id)
    //                       ->orWhere('creator_user_id', $user->id)
    //                       ->orWhere('supervisor_user_id', $user->id);
    //             })
    //             ->get()
    //             ->map(function($task) {
    //                 $status = $task->userStatuses->first();
    //                 $task->is_starred = $status ? $status->is_starred : false;
    //                 $task->is_archived = $status ? $status->is_archived : false;
    //                 $task->makeHidden(['company_id', 'department_id', 'project_id', 'creator_user_id', 'assigned_user_id', 'supervisor_user_id', 'userStatuses']);
    
    //                 if ($task->project) {
    //                     $task->project->setAppends([]);
    //                 }
    
    //                 return [
    //                     'id' => $task->id,
    //                     'title' => $task->title,
    //                     'description' => $task->description,
    //                     'start_date' => $task->start_date,
    //                     'deadline' => $task->deadline,
    //                     'is_urgent' => $task->is_urgent,
    //                     'priority' => $task->priority,
    //                     'status' => $task->status,
    //                     'is_starred' => $task->is_starred,
    //                     'is_archived' => $task->is_archived,
    //                     'project' => $task->project->only(['id', 'name']),
    //                     'department' => $task->department->only(['id', 'name']),
    //                     'creator' => $task->creator->only(['id', 'name']),
    //                     'assignedUser' => $task->assignedUser->only(['id', 'name']),
    //                     'supervisor' => $task->supervisor->only(['id', 'name']),
    //                 ];
    //             });
    //     }
    //     return response()->json($tasks);
    // }

    public function store(Request $request)
    {
        $request->validate([
            'assigned_user_id' => 'required|exists:users,id',
            'supervisor_user_id' => 'required|exists:users,id',
            'title' => 'required|string',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'deadline' => 'required|date|after_or_equal:start_date',
            'is_urgent' => 'boolean',
            'priority' => 'in:low,normal,high',
            'project_id' => 'nullable|exists:projects,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $data = $request->all();
        $data['creator_user_id'] = Auth::id();
        $data['company_id'] = Auth::user()->company_id;
        $data['status'] = 'pending';

        $task = Task::create($data);

        return response()->json($task, 201);
    }

    public function show($id)
    {
        $task = Task::with(['comments.user','comments.replies','attachments.uploadedBy','revisions.user','company','project','department','creator','assignedUser','supervisor'])
                    ->findOrFail($id);
        $task->comments->each(function ($comment) {
            $comment->replies_count = $comment->replies->count();
            });
        $task->makeHidden(['company_id', 'department_id','project_id','creator_user_id','assigned_user_id','supervisor_user_id']);    
        $this->authorizeUserForTask($task);

        return response()->json($task, 200);
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $this->authorizeUserForTask($task);
        $original = $task->getOriginal();
        $request->validate([
            'title' => 'sometimes|string',
            'description' => 'sometimes|string',
            'start_date' => 'sometimes|date',
            'deadline' => 'sometimes|date|after_or_equal:start_date',
            'is_urgent' => 'sometimes|boolean',
            'priority' => 'sometimes|in:low,normal,high',
            'status' => 'sometimes|in:pending,rework,done,review,inProgress',
            'assigned_user_id' => 'sometimes|exists:users,id',
            'supervisor_user_id' => 'sometimes|exists:users,id',
        ]);
        $data = $request->all();
        if (isset($data['status'])) {
            if ($data['status'] === 'done' && Auth::id() !== $task->creator_user_id) {
                return response()->json(['error' => 'Only creator can mark done'], 403);
            }
            if ($data['status'] === 'rework' && !in_array(Auth::id(), [$task->creator_user_id, $task->supervisor_user_id])) {
                return response()->json(['error' => 'Only creator or supervisor can mark rework'], 403);
            }
        }

        $task->update($data);
        $changes = $task->getChanges();
        foreach ($changes as $field => $newValue) {
            if (in_array($field, ['deadline','status','title','description','assigned_user_id','supervisor_user_id','priority','is_urgent'])) {
                TaskRevision::create([
                    'task_id' => $task->id,
                    'user_id' => Auth::id(),
                    'field' => $field,
                    'old_value' => $original[$field] ?? null,
                    'new_value' => $newValue,
                    'created_at' => now()
                ]);
            }
        }

        return response()->json($task, 200);
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        if (Auth::id() !== $task->creator_user_id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted'], 200);
    }

    public function updateStatus(Request $request, $taskId)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,rework,done,review,inProgress',
        ]);
        $task = Task::findOrFail($taskId);
        $userId = Auth::id();
        if (!in_array($userId, [$task->creator_user_id, $task->assigned_user_id, $task->supervisor_user_id])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $task->status = $validated['status'];
        $task->save();
        return response()->json([
            'message' => 'Task status updated successfully',
        ], 200);
    }

    protected function authorizeUserForTask(Task $task)
    {
        $userId = Auth::id();
        if (!in_array($userId, [$task->creator_user_id, $task->assigned_user_id, $task->supervisor_user_id])) {
            abort(403, 'Forbidden');
        }
    }
}
