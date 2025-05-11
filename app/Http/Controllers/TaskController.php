<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\TaskRevision;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;

class TaskController extends Controller
{
    // public function index()
    // {
    //     $userId = Auth::id();
    //     $tasks = Task::select(
    //         'id',
    //         'company_id',
    //         'project_id',
    //         'department_id',
    //         'creator_user_id',
    //         'assigned_user_id',
    //         'supervisor_user_id',
    //         'title',
    //         'description',
    //         'start_date',
    //         'deadline',
    //         'priority',
    //         'status',
    //         'created_at',
    //         'updated_at'
    //     )
    //     ->where('creator_user_id', $userId)
    //     ->orWhere('assigned_user_id', $userId)
    //     ->orWhere('supervisor_user_id', $userId)
    //     ->withCount('comments')
    //     ->with([
    //         'creator:id,name',
    //         'assignedUser:id,name',
    //         'supervisor:id,name',
    //         'project:id,name',
    //         'department:id,name',
    //         'userStatuses' => function ($query) use ($userId) {
    //             $query->where('user_id', $userId);
    //         },
    //     ])
    //     ->get();
    //     $tasks->each(function ($task) {
    //         $status = $task->userStatuses->first();
    //         $task->is_starred = $status ? $status->is_starred : false;
    //         $task->is_archived = $status ? $status->is_archived : false;
    //         $task->makeHidden(['company_id', 'department_id', 'project_id', 'creator_user_id', 'assigned_user_id', 'supervisor_user_id', 'userStatuses']);
    //         if ($task->project) {
    //             $task->project->setAppends([]);
    //         }
    //     });

    //     return response()->json($tasks, 200);
    // }

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
            'consult_user_id',
            'inform_user_id',
            'title',
            'description',
            'start_date',
            'deadline',
            'priority',
            'status',
            'created_at',
            'updated_at'
        )
            ->where('creator_user_id', $userId)
            ->orWhere('assigned_user_id', $userId)
            ->orWhere('supervisor_user_id', $userId)
            ->orWhere('consult_user_id', $userId)
            ->orWhere('inform_user_id', $userId)
            ->withCount('comments')
            ->with([
                'creator:id,name',
                'assignedUser:id,name',
                'supervisor:id,name',
                'consult:id,name',
                'informer:id,name',
                'project:id,name',
                'department:id,name',
                'userStatuses' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                }
            ])
            ->get();
        $taskIds = $tasks->pluck('id')->toArray();
        $tasksWithUnreadComments = DB::table('task_comments as tc')
            ->join('task_comment_user as tcu', 'tc.id', '=', 'tcu.task_comment_id')
            ->whereIn('tc.task_id', $taskIds)
            ->where('tcu.user_id', $userId)
            ->whereNull('tcu.read_at')
            ->distinct()
            ->pluck('tc.task_id')
            ->toArray();
        $tasksWithUnreadReplies = DB::table('task_comment_replies as tcr')
            ->join('task_comments as tc', 'tcr.task_comment_id', '=', 'tc.id')
            ->join('task_comment_reply_user as tcru', 'tcr.id', '=', 'tcru.task_comment_reply_id')
            ->whereIn('tc.task_id', $taskIds)
            ->where('tcru.user_id', $userId)
            ->whereNull('tcru.read_at')
            ->distinct()
            ->pluck('tc.task_id')
            ->toArray();
        $tasks->each(function ($task) use ($tasksWithUnreadComments, $tasksWithUnreadReplies, $userId) {
            $hasUnreadComment = in_array($task->id, $tasksWithUnreadComments);
            $hasUnreadReply = in_array($task->id, $tasksWithUnreadReplies);
            $task->read_comments = !($hasUnreadComment || $hasUnreadReply);
            $status = $task->userStatuses->first();
            $task->is_starred = $status ? $status->is_starred : false;
            $task->is_archived = $status ? $status->is_archived : false;
            $task->makeHidden([
                'company_id',
                'department_id',
                'project_id',
                'creator_user_id',
                'assigned_user_id',
                'supervisor_user_id',
                'userStatuses'
            ]);
            if ($task->project) {
                $task->project->setAppends([]);
            }
        });

        return response()->json($tasks, 200);
    }


    // public function index()
    // {
    //     $userId = Auth::id();

    //     // Fetch tasks related to the logged-in user
    //     $tasks = Task::select(
    //         'id',
    //         'company_id',
    //         'project_id',
    //         'department_id',
    //         'creator_user_id',
    //         'assigned_user_id',
    //         'supervisor_user_id',
    //         'title',
    //         'description',
    //         'start_date',
    //         'deadline',
    //         'priority',
    //         'status',
    //         'created_at',
    //         'updated_at'
    //     )
    //     ->where('creator_user_id', $userId)
    //     ->orWhere('assigned_user_id', $userId)
    //     ->orWhere('supervisor_user_id', $userId)
    //     ->withCount('comments')
    //     ->with([
    //         'creator:id,name',
    //         'assignedUser:id,name',
    //         'supervisor:id,name',
    //         'project:id,name',
    //         'department:id,name',
    //         'userStatuses' => function ($query) use ($userId) {
    //             $query->where('user_id', $userId);
    //         },
    //     ])
    //     ->get();

    //     // Process each task
    //     $tasks->each(function ($task) use ($userId) {
    //         // Check if there are any unread comments for the logged-in user
    //         $hasUnreadComments = DB::table('task_comment_user')
    //             ->join('task_comments', 'task_comment_user.task_comment_id', '=', 'task_comments.id')
    //             ->where('task_comments.task_id', $task->id)
    //             ->where('task_comment_user.user_id', $userId)
    //             ->whereNull('task_comment_user.read_at')
    //             ->exists();

    //         // Check if there are any unread replies for the logged-in user
    //         $hasUnreadReplies = DB::table('task_comment_reply_user')
    //             ->join('task_comment_replies', 'task_comment_reply_user.task_comment_reply_id', '=', 'task_comment_replies.id')
    //             ->join('task_comments', 'task_comment_replies.task_comment_id', '=', 'task_comments.id')
    //             ->where('task_comments.task_id', $task->id)
    //             ->where('task_comment_reply_user.user_id', $userId)
    //             ->whereNull('task_comment_reply_user.read_at')
    //             ->exists();

    //         // Add the `is_read` property
    //         $task->is_read = !($hasUnreadComments || $hasUnreadReplies);

    //         // Add starred and archived status
    //         $status = $task->userStatuses->first();
    //         $task->is_starred = $status ? $status->is_starred : false;
    //         $task->is_archived = $status ? $status->is_archived : false;

    //         // Hide unnecessary fields
    //         $task->makeHidden(['company_id', 'department_id', 'project_id', 'creator_user_id', 'assigned_user_id', 'supervisor_user_id', 'userStatuses']);

    //         // Ensure project attributes are not appended
    //         if ($task->project) {
    //             $task->project->setAppends([]);
    //         }
    //     });

    //     return response()->json($tasks, 200);
    // }

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
            'supervisor_user_id' => 'nullable|exists:users,id',
            'consult_user_id' => 'nullable|exists:users,id',
            'inform_user_id' => 'nullable|exists:users,id',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'start_date' => 'sometimes|date',
            'deadline' => 'nullable|date|after_or_equal:start_date',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'project_id' => 'nullable|exists:projects,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);
        $this->authorize('create', Task::class);
        $data = $request->all();
        if (empty($data['start_date'])) {
            $data['start_date'] = today();
        }
        if (!$data['priority']) {
            $data['priority'] = "normal";
        }
        $data['creator_user_id'] = Auth::id();
        $data['company_id'] = Auth::user()->company_id;
        $data['status'] = 'pending';

        $task = Task::create($data);

        return response()->json($task, 201);
    }

    // public function show($id)
    // {
    //     $task = Task::with(['comments.user','comments.replies','comments.replies.user','attachments.uploadedBy','revisions.user','company','project','department','creator','assignedUser','supervisor'])
    //                 ->findOrFail($id);
    //     $task->comments->each(function ($comment) {
    //         $comment->replies_count = $comment->replies->count();
    //         });
    //     $task->makeHidden(['company_id', 'department_id','project_id','creator_user_id','assigned_user_id','supervisor_user_id']);    
    //     $this->authorizeUserForTask($task);
    //     return response()->json($task, 200);
    // }

    public function show($id)
    {
        $task = Task::with([
            'comments.user',
            'comments.users',
            'comments.replies.user',
            'comments.replies.users',
            'attachments.uploadedBy',
            'revisions.user',
            'company',
            'project',
            'department',
            'creator',
            'assignedUser',
            'supervisor',
            'consult',
            'informer'
        ])->findOrFail($id);
        $currentUserId = Auth::id();
        $task->comments->each(function ($comment) use ($currentUserId) {
            $comment->replies_count = $comment->replies->count();
            $comment->seen_by = $comment->users->filter(function ($user) use ($comment) {
                return !is_null($user->pivot->read_at) && $user->id !== $comment->user_id;
            })->map(function ($user) {
                return [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'read_at' => $user->pivot->read_at,
                ];
            })->values();
            $comment->is_seen = $comment->users->contains(function ($user) use ($currentUserId) {
                return $user->id === $currentUserId && !is_null($user->pivot->read_at);
            });
            unset($comment->users);
            $comment->replies->each(function ($reply) use ($currentUserId) {
                $reply->seen_by = $reply->users->filter(function ($user) use ($reply) {
                    return !is_null($user->pivot->read_at) && $user->id !== $reply->user_id;
                })->map(function ($user) {
                    return [
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'read_at' => $user->pivot->read_at,
                    ];
                })->values();
                $reply->is_seen = $reply->users->contains(function ($user) use ($currentUserId) {
                    return $user->id === $currentUserId && !is_null($user->pivot->read_at);
                });
                unset($reply->users);
            });
        });
        $task->makeHidden([
            'company_id',
            'department_id',
            'project_id',
            'creator_user_id',
            'assigned_user_id',
            'supervisor_user_id',
            'consult_user_id',
            'inform_user_id'
        ]);
        $task->comments->each(function ($comment) use ($currentUserId) {
            $comment->users()->updateExistingPivot($currentUserId, [
                'read_at' => now(),
            ]);
        });
        $this->authorizeUserForTask($task);
        return response()->json($task, 200);
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        // $this->authorizeUserForTask($task);
        $user = Auth::user();
        if ($user->id !== $task->creator_user_id) return response()->json(['message' => 'Only creator can update the task'], 403);
        $original = $task->getOriginal();
        $request->validate([
            'title' => 'sometimes|string|nullable',
            'description' => 'sometimes|string|nullable',
            'start_date' => 'sometimes|date',
            'deadline' => 'sometimes|date|nullable|after_or_equal:start_date',
            'priority' => 'sometimes|in:low,normal,high,urgent',
            'status' => 'sometimes|in:pending,rework,done,review,inProgress',
            'assigned_user_id' => 'sometimes|exists:users,id|nullable',
            'supervisor_user_id' => 'sometimes|exists:users,id|nullable',
            'consult_user_id' => 'sometimes|exists:users,id|nullable',
            'inform_user_id' => 'sometimes|exists:users,id|nullable',
            'project_id' => 'sometimes|exists:projects,id|nullable',
            'department_id' => 'sometimes|exists:departments,id|nullable',
        ]);
        $data = collect($request->all())->map(function ($value) {
            return $value === '' ? null : $value;
        })->toArray();
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
            if (in_array($field, [
                'deadline',
                'status',
                'title',
                'description',
                'assigned_user_id',
                'supervisor_user_id',
                'priority',
                'consult_user_id',
                'inform_user_id',
                'start_date',
                'project_id',
                'department_id'
            ])) {
                $oldValue = $original[$field] ?? null;
                if (in_array($field, ['assigned_user_id', 'supervisor_user_id', 'consult_user_id', 'inform_user_id'])) {
                    $oldValue = $oldValue ? optional(User::find($oldValue))->name : null;
                    $newValue = $newValue ? optional(User::find($newValue))->name : null;
                }
                if ($field === 'department_id') {
                    $oldValue = $oldValue ? optional(Department::find($oldValue))->name : null;
                    $newValue = $newValue ? optional(Department::find($newValue))->name : null;
                }

                // For project-related fields, fetch the project's name instead of its ID
                if ($field === 'project_id') {
                    $oldValue = $oldValue ? optional(Project::find($oldValue))->name : null;
                    $newValue = $newValue ? optional(Project::find($newValue))->name : null;
                }
                TaskRevision::create([
                    'task_id' => $task->id,
                    'user_id' => Auth::id(),
                    'field' => $field,
                    'old_value' => $oldValue ?? null,
                    'new_value' => $newValue,
                    'created_at' => now()
                ]);
                $comment = TaskComment::create([
                    'task_id' => $task->id,
                    'user_id' => Auth::id(),
                    'comment_text' => "<p><span class='text-danger cst-cmnt'>System Log:</span> $user->name changed '$field' from '$oldValue' to '$newValue'</p>",
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
        $attachments = $task->attachments;
        $firebaseConfig = [
            'storageBucket' => "brooklyn-chat.appspot.com",
        ];
        $storageBucket = $firebaseConfig['storageBucket'];
        $deleteToken = "YOUR_DELETE_TOKEN";
        foreach ($attachments as $attachment) {
            $filePath = parse_url($attachment->file_path, PHP_URL_PATH);
            $filePath = ltrim($filePath, '/');
            $filePath = urlencode($filePath);
            $firebaseDeletionUrl = "https://firebasestorage.googleapis.com/v0/b/{$storageBucket}/o/{$filePath}";
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$deleteToken}",
            ])->delete($firebaseDeletionUrl);
            $attachment->delete();
        }
        $task->delete();
        return response()->json(['message' => 'Task and its attachments deleted successfully'], 200);
    }

    public function updateStatus(Request $request, $taskId)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,rework,done,review,inProgress',
        ]);
        $task = Task::findOrFail($taskId);
        $user = Auth::user();
        $oldValue = $task->status;
        $newValue = $request->status;
        if (!in_array($user->id, [$task->creator_user_id, $task->assigned_user_id, $task->supervisor_user_id, $task->consult_user_id, $task->inform_user_id])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $task->status = $validated['status'];
        $task->save();
        TaskRevision::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'field' => 'status',
            'old_value' => $oldValue ?? null,
            'new_value' => $newValue,
            'created_at' => now()
        ]);
        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'comment_text' => "<p><span class='text-danger cst-cmnt'>System Log:</span> $user->name changed status from '$oldValue' to '$newValue'</p>",
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
