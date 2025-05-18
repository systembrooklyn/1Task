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
    private function attachUsersWithRole($task, $userIds, $role)
    {
        collect($userIds)->each(function ($userId) use ($task, $role) {
            if ($userId) {
                $task->users()->attach($userId, ['role' => $role]);
            }
        });
    }
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

        $tasks = Task::select([
            'id',
            'company_id',
            'project_id',
            'department_id',
            'creator_user_id',
            'supervisor_user_id',
            'title',
            'description',
            'start_date',
            'deadline',
            'priority',
            'status',
            'created_at',
            'updated_at'
        ])
            ->where(function ($query) use ($userId) {
                $query->where('creator_user_id', $userId)
                    ->orWhereHas('assignedUsers', function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    })
                    ->orWhereHas('consultUsers', function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    })
                    ->orWhereHas('informerUsers', function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    });
            })
            ->orWhere('supervisor_user_id', $userId)
            ->withCount('comments')
            ->with([
                'creator:id,name',
                'assignedUsers:id,name',
                'supervisor:id,name',
                'consultUsers:id,name',
                'informerUsers:id,name',
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
            $task->assignedUser = $task->assignedUsers ?? [];
            $task->consult = $task->consultUsers ?? [];
            $task->informer = $task->informerUsers ?? [];
            $task->makeHidden([
                'company_id',
                'department_id',
                'project_id',
                'creator_user_id',
                'supervisor_user_id',
                'assignedUsers',
                'consultUsers',
                'informerUsers',
                'userStatuses',
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
        $validated = $request->validate([
            'assigned_user_id' => 'required|array',
            'assigned_user_id.*' => 'exists:users,id',
            'supervisor_user_id' => 'nullable|exists:users,id',
            'consult_user_id' => 'nullable|array',
            'consult_user_id.*' => 'exists:users,id',
            'inform_user_id' => 'nullable|array',
            'inform_user_id.*' => 'exists:users,id',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'start_date' => 'sometimes|date',
            'deadline' => 'nullable|date|after_or_equal:start_date',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'project_id' => 'nullable|exists:projects,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);
        $this->authorize('create', Task::class);
        $data = $request->only([
            'title',
            'description',
            'start_date',
            'deadline',
            'priority',
            'project_id',
            'department_id'
        ]);
        $data['creator_user_id'] = Auth::id();
        $data['company_id'] = Auth::user()->company_id;
        $data['status'] = 'pending';
        $data['supervisor_user_id'] = $request->input('supervisor_user_id');

        if (!$request->filled('start_date')) {
            $data['start_date'] = now();
        }

        if (!$request->filled('priority')) {
            $data['priority'] = 'normal';
        }
        $task = Task::create($data);
        $task->users()->attach(
            collect($request->input('assigned_user_id', []))->mapWithKeys(fn($userId) => [
                $userId => ['role' => 'assigned']
            ])
        );
        $task->users()->attach(
            collect($request->input('consult_user_id', []))->mapWithKeys(fn($userId) => [
                $userId => ['role' => 'consult']
            ])
        );
        $task->users()->attach(
            collect($request->input('inform_user_id', []))->mapWithKeys(fn($userId) => [
                $userId => ['role' => 'informer']
            ])
        );
        return response()->json($task->load([
            'creator:id,name',
            'supervisor:id,name',
            'assignedUsers:id,name',
            'consultUsers:id,name',
            'informerUsers:id,name',
        ]), 201);
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
        $user = Auth::user();

        if ($user->id !== $task->creator_user_id) {
            return response()->json(['message' => 'Only creator can update the task'], 403);
        }


        $request->validate([
            'title' => 'sometimes|string|nullable',
            'description' => 'sometimes|string|nullable',
            'start_date' => 'sometimes|date',
            'deadline' => 'sometimes|date|nullable|after_or_equal:start_date',
            'priority' => 'sometimes|in:low,normal,high,urgent',
            'status' => 'sometimes|in:pending,rework,done,review,inProgress',
            'assigned_user_id' => 'sometimes|array|nullable',
            'assigned_user_id.*' => 'exists:users,id',
            'supervisor_user_id' => 'sometimes|exists:users,id|nullable',
            'consult_user_id' => 'sometimes|array|nullable',
            'consult_user_id.*' => 'exists:users,id',
            'inform_user_id' => 'sometimes|array|nullable',
            'inform_user_id.*' => 'exists:users,id',
            'project_id' => 'sometimes|exists:projects,id|nullable',
            'department_id' => 'sometimes|exists:departments,id|nullable',
        ]);
        $fieldLabels = [
            'assigned_user_id' => 'Assigned',
            'supervisor_user_id' => 'Supervisor',
            'consult_user_id' => 'Consult',
            'inform_user_id' => 'Informer',
            'title' => 'Title',
            'description' => 'Description',
            'start_date' => 'Start Date',
            'deadline' => 'Deadline',
            'priority' => 'Priority',
            'status' => 'Status',
            'project_id' => 'Project',
            'department_id' => 'Department'
        ];

        $original = $task->getOriginal();
        $original['assigned_user_id'] = $task->assignedUsers->pluck('id')->toArray();
        $original['consult_user_id'] = $task->consultUsers->pluck('id')->toArray();
        $original['inform_user_id'] = $task->informerUsers->pluck('id')->toArray();
        $newAssignedUserIds = $request->input('assigned_user_id', []);
        $newConsultUserIds = $request->input('consult_user_id', []);
        $newInformerUserIds = $request->input('inform_user_id', []);
        $data = collect($request->all())->map(fn($value) => $value === '' ? null : $value)->toArray();
        if (isset($data['status'])) {
            if ($data['status'] === 'done' && $user->id !== $task->creator_user_id) {
                return response()->json(['error' => 'Only creator can mark done'], 403);
            }
            if ($data['status'] === 'rework' && !in_array($user->id, [$task->creator_user_id, $task->supervisor_user_id])) {
                return response()->json(['error' => 'Only creator or supervisor can mark rework'], 403);
            }
        }
        $task->users()->detach();
        $this->attachUsersWithRole($task, $newAssignedUserIds, Task::ROLE_ASSIGNED);
        $this->attachUsersWithRole($task, $newConsultUserIds, Task::ROLE_CONSULT);
        $this->attachUsersWithRole($task, $newInformerUserIds, Task::ROLE_INFORMER);
        $task->update($data);
        $changes = collect($task->getChanges());
        if (
            !empty(array_diff($newAssignedUserIds, $original['assigned_user_id'])) ||
            !empty(array_diff($original['assigned_user_id'], $newAssignedUserIds))
        ) {
            $changes['assigned_user_id'] = $newAssignedUserIds;
        }

        if (
            !empty(array_diff($newConsultUserIds, $original['consult_user_id'])) ||
            !empty(array_diff($original['consult_user_id'], $newConsultUserIds))
        ) {
            $changes['consult_user_id'] = $newConsultUserIds;
        }

        if (
            !empty(array_diff($newInformerUserIds, $original['inform_user_id'])) ||
            !empty(array_diff($original['inform_user_id'], $newInformerUserIds))
        ) {
            $changes['inform_user_id'] = $newInformerUserIds;
        }
        foreach ($changes as $field => $newValue) {
            if (!array_key_exists($field, $fieldLabels)) continue;

            $label = $fieldLabels[$field];
            $oldValue = $original[$field] ?? null;

            if (in_array($field, ['assigned_user_id', 'consult_user_id', 'inform_user_id'])) {
                $oldNames = is_array($oldValue)
                    ? collect($oldValue)->map(fn($id) => optional(User::find($id))->name)->filter()->toArray()
                    : [];

                $newNames = is_array($newValue)
                    ? collect($newValue)->map(fn($id) => optional(User::find($id))->name)->filter()->toArray()
                    : [];

                $oldValue = implode(', ', $oldNames);
                $newValue = implode(', ', $newNames);
            } elseif ($field === 'supervisor_user_id') {
                $field = 'supervisor';
                $oldValue = optional(User::find($oldValue))->name;
                $newValue = optional(User::find($newValue))->name;
            } elseif ($field === 'department_id') {
                $field = 'department';
                $oldValue = optional(Department::find($oldValue))->name;
                $newValue = optional(Department::find($newValue))->name;
            } elseif ($field === 'project_id') {
                $field = 'project';
                $oldValue = optional(Project::find($oldValue))->name;
                $newValue = optional(Project::find($newValue))->name;
            }

            TaskRevision::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'field' => $label,
                'old_value' => $oldValue ?? null,
                'new_value' => $newValue,
                'created_at' => now()
            ]);

            $commentText = "<p><span class='text-danger cst-cmnt'>System Log:</span> $user->name changed '$label' from '$oldValue' to '$newValue'</p>";

            $comment = TaskComment::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'comment_text' => $commentText,
                'created_at' => now()
            ]);

            $relatedUsers = collect([
                $task->supervisor,
                $task->creator,
                ...$task->assignedUsers->all(),
                ...$task->consultUsers->all(),
                ...$task->informerUsers->all(),
            ])->filter();

            foreach ($relatedUsers as $u) {
                if ($u->id !== $user->id) {
                    $comment->users()->attach($u->id, ['read_at' => null]);
                } else {
                    $comment->users()->attach($u->id, ['read_at' => now()]);
                }
            }
        }

        return response()->json($task->load([
            'creator:id,name',
            'supervisor:id,name',
            'assignedUsers:id,name',
            'consultUsers:id,name',
            'informerUsers:id,name',
        ]), 200);
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
        $task = Task::with([
            'creator:id,name',
            'supervisor:id,name',
            'assignedUsers:id,name',
            'consultUsers:id,name',
            'informerUsers:id,name'
        ])->findOrFail($taskId);

        $user = Auth::user();
        $relatedUserIds = collect([
            $task->creator?->id,
            $task->supervisor?->id,
            ...$task->assignedUsers->pluck('id')->toArray(),
            ...$task->consultUsers->pluck('id')->toArray(),
            ...$task->informerUsers->pluck('id')->toArray(),
        ])->filter()->unique()->toArray();
        if (!in_array($user->id, $relatedUserIds)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $oldValue = $task->status;
        $newValue = $validated['status'];
        $task->status = $newValue;
        $task->save();
        TaskRevision::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'field' => 'status',
            'old_value' => $oldValue ?? null,
            'new_value' => $newValue,
            'created_at' => now()
        ]);
        $commentText = "<p><span class='text-danger cst-cmnt'>System Log:</span> $user->name changed status from '$oldValue' to '$newValue'</p>";

        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'comment_text' => $commentText,
            'created_at' => now()
        ]);
        $allRelatedUsers = collect([
            $task->creator,
            $task->supervisor,
            ...$task->assignedUsers->all(),
            ...$task->consultUsers->all(),
            ...$task->informerUsers->all(),
        ])->filter();

        foreach ($allRelatedUsers as $relatedUser) {
            $readAt = $relatedUser->id === $user->id ? now() : null;
            $comment->users()->attach($relatedUser->id, ['read_at' => $readAt]);
        }

        return response()->json([
            'message' => 'Task status updated successfully',
        ], 200);
    }

    protected function authorizeUserForTask(Task $task)
    {
        $userId = Auth::id();
        $relatedUserIds = collect([
            $task->creator_user_id,
            $task->supervisor_user_id,
            ...$task->assignedUsers->pluck('id')->toArray(),
            ...$task->consultUsers->pluck('id')->toArray(),
            ...$task->informerUsers->pluck('id')->toArray(),
        ])->filter()->unique();

        if (!$relatedUserIds->contains($userId)) {
            abort(403, 'Forbidden: You are not authorized to perform this action.');
        }
    }
}
