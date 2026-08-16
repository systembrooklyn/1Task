<?php

namespace App\Modules\Task\Services;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\TaskRevision;
use App\Models\Department;
use App\Models\Project;
use App\Models\User;
use App\Modules\Task\Repositories\Contracts\TaskRepositoryInterface;
use App\Services\PlanLimitService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class TaskService
{
    protected PlanLimitService $planService;
    protected TaskRepositoryInterface $taskRepo;

    public function __construct(
        TaskRepositoryInterface $taskRepo,
        PlanLimitService $planService
    ) {
        $this->taskRepo = $taskRepo;
        $this->planService = $planService;
    }

    public function getTasksForUser(int $userId): Collection
    {
        $with = [
            'creator:id,name,last_name',
            'creator.profile',
            'assignedUsers:id,name,last_name',
            'supervisor:id,name,last_name',
            'consultUsers:id,name,last_name',
            'informerUsers:id,name,last_name',
            'project:id,name',
            'department:id,name',
            'userStatuses' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }
        ];

        $tasks = $this->taskRepo->getTasksForUser($userId, $with);

        $taskIds = $tasks->pluck('id')->toArray();
        $unreadComments = [];
        $unreadReplies = [];

        if (!empty($taskIds)) {
            $unreadComments = DB::table('task_comments as tc')
                ->join('task_comment_user as tcu', 'tc.id', '=', 'tcu.task_comment_id')
                ->whereIn('tc.task_id', $taskIds)
                ->where('tcu.user_id', $userId)
                ->whereNull('tcu.read_at')
                ->distinct()
                ->pluck('tc.task_id')
                ->toArray();

            $unreadReplies = DB::table('task_comment_replies as tcr')
                ->join('task_comments as tc', 'tcr.task_comment_id', '=', 'tc.id')
                ->join('task_comment_reply_user as tcru', 'tcr.id', '=', 'tcru.task_comment_reply_id')
                ->whereIn('tc.task_id', $taskIds)
                ->where('tcru.user_id', $userId)
                ->whereNull('tcru.read_at')
                ->distinct()
                ->pluck('tc.task_id')
                ->toArray();
        }

        $tasks->each(function (Task $task) use ($unreadComments, $unreadReplies) {
            $task->creator->ppUrl = $task->creator->profile?->ppUrl ?? null;
            $task->read_comments = !(in_array($task->id, $unreadComments) || in_array($task->id, $unreadReplies));

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
                'userStatuses'
            ]);
            $task->creator->makeHidden(['profile']);
            if ($task->project) {
                $task->project->setAppends([]);
            }
        });

        return $tasks;
    }

    public function createTask(array $data, int $companyId, int $userId): Task
    {
        $this->planService->checkFeatureAccess($companyId, 'limit_task');
        $this->authorize('create', Task::class);

        $taskData = [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'start_date' => $data['start_date'] ?? now(),
            'deadline' => $data['deadline'] ?? null,
            'priority' => $data['priority'] ?? 'normal',
            'project_id' => $data['project_id'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'creator_user_id' => $userId,
            'company_id' => $companyId,
            'status' => 'pending',
            'supervisor_user_id' => $data['supervisor_user_id'] ?? null,
        ];

        $task = $this->taskRepo->create($taskData);

        $this->attachUsersWithRole($task, $data['assigned_user_id'] ?? [], 'assigned');
        $this->attachUsersWithRole($task, $data['consult_user_id'] ?? [], 'consult');
        $this->attachUsersWithRole($task, $data['inform_user_id'] ?? [], 'informer');

        return $task;
    }

    public function updateTask(Task $task, array $data, int $userId): Task
    {
        $original = $task->getOriginal();
        $original['assigned_user_id'] = $task->assignedUsers->pluck('id')->toArray();
        $original['consult_user_id'] = $task->consultUsers->pluck('id')->toArray();
        $original['inform_user_id'] = $task->informerUsers->pluck('id')->toArray();

        $task->users()->detach();

        $this->attachUsersWithRole($task, $data['assigned_user_id'] ?? [], 'assigned');
        $this->attachUsersWithRole($task, $data['consult_user_id'] ?? [], 'consult');
        $this->attachUsersWithRole($task, $data['inform_user_id'] ?? [], 'informer');

        $updateData = collect($data)->map(fn($value) => $value === '' ? null : $value)->toArray();
        $this->taskRepo->update($task, $updateData);

        $this->createTaskRevisions($task, $original, $data, $userId);

        return $task;
    }

    // public function deleteTask(Task $task): void
    // {
    //     $attachments = $task->attachments;
    //     $storageBucket = "brooklyn-chat.appspot.com";
    //     $deleteToken = "YOUR_DELETE_TOKEN";
    //     foreach ($attachments as $attachment) {
    //         $filePath = parse_url($attachment->file_path, PHP_URL_PATH);
    //         $filePath = ltrim($filePath, '/');
    //         $filePath = urlencode($filePath);
    //         $firebaseDeletionUrl = "https://firebasestorage.googleapis.com/v0/b/{$storageBucket}/o/{$filePath}";
    //         Http::withHeaders(['Authorization' => "Bearer {$deleteToken}"])->delete($firebaseDeletionUrl);
    //         $attachment->delete();
    //     }
    //     $this->taskRepo->delete($task);
    // }

    public function updateStatus(Task $task, string $status, int $userId): void
    {
        $oldStatus = $task->status;
        $task->status = $status;
        $this->taskRepo->update($task, ['status' => $status]);

        TaskRevision::create([
            'task_id' => $task->id,
            'user_id' => $userId,
            'field' => 'status',
            'old_value' => $oldStatus ?? null,
            'new_value' => $status,
            'created_at' => now()
        ]);

        $user = Auth::user();
        $commentText = "<p><span class='text-danger cst-cmnt'>System Log:</span> $user->name changed status from '$oldStatus' to '$status'</p>";
        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $userId,
            'comment_text' => $commentText,
            'created_at' => now()
        ]);

        $relatedUsers = collect([
            $task->creator,
            $task->supervisor,
            ...$task->assignedUsers->all(),
            ...$task->consultUsers->all(),
            ...$task->informerUsers->all(),
        ])->filter();

        foreach ($relatedUsers as $user) {
            $comment->users()->attach($user->id, ['read_at' => $user->id === $userId ? now() : null]);
        }
    }

    public function getTaskWithRelations(int $id, array $with = []): ?Task
    {
        return $this->taskRepo->findById($id, $with);
    }

    public function getRevisions(int $taskId): Collection
    {
        return $this->taskRepo->findById($taskId, ['revisions.user'])?->revisions ?? collect();
    }

    // ========== Helper Methods ==========

    protected function createTaskRevisions(Task $task, array $original, array $data, int $userId): void
    {
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

        $changes = collect($task->getChanges());
        $originalAssigned = $original['assigned_user_id'] ?? [];
        $originalConsult = $original['consult_user_id'] ?? [];
        $originalInformer = $original['inform_user_id'] ?? [];
        $newAssigned = $data['assigned_user_id'] ?? [];
        $newConsult = $data['consult_user_id'] ?? [];
        $newInformer = $data['inform_user_id'] ?? [];

        if (!empty(array_diff($newAssigned, $originalAssigned)) || !empty(array_diff($originalAssigned, $newAssigned))) {
            $changes['assigned_user_id'] = $newAssigned;
        }
        if (!empty(array_diff($newConsult, $originalConsult)) || !empty(array_diff($originalConsult, $newConsult))) {
            $changes['consult_user_id'] = $newConsult;
        }
        if (!empty(array_diff($newInformer, $originalInformer)) || !empty(array_diff($originalInformer, $newInformer))) {
            $changes['inform_user_id'] = $newInformer;
        }

        $user = Auth::user();
        foreach ($changes as $field => $newValue) {
            if (!array_key_exists($field, $fieldLabels)) {
                continue;
            }

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
                $oldValue = optional(User::find($oldValue))->name;
                $newValue = optional(User::find($newValue))->name;
            } elseif ($field === 'department_id') {
                $oldValue = optional(Department::find($oldValue))->name;
                $newValue = optional(Department::find($newValue))->name;
            } elseif ($field === 'project_id') {
                $oldValue = optional(Project::find($oldValue))->name;
                $newValue = optional(Project::find($newValue))->name;
            }

            TaskRevision::create([
                'task_id' => $task->id,
                'user_id' => $userId,
                'field' => $label,
                'old_value' => $oldValue ?? null,
                'new_value' => $newValue,
                'created_at' => now()
            ]);

            $commentText = "<p><span class='text-danger cst-cmnt'>System Log:</span> $user->name changed '$label' from '$oldValue' to '$newValue'</p>";
            $comment = TaskComment::create([
                'task_id' => $task->id,
                'user_id' => $userId,
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
                $comment->users()->attach($u->id, ['read_at' => $u->id === $userId ? now() : null]);
            }
        }
    }

    protected function attachUsersWithRole(Task $task, array $userIds, string $role): void
    {
        collect($userIds)->each(function ($userId) use ($task, $role) {
            if ($userId) {
                $task->users()->attach($userId, ['role' => $role]);
            }
        });
    }

    protected function authorize(string $ability, $arguments = []): void
    {
        Gate::authorize($ability, $arguments);
    }
}
