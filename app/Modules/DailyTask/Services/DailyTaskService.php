<?php

namespace App\Modules\DailyTask\Services;

use App\Models\DailyTask;
use App\Models\DailyTaskRevision;
use App\Models\Department;
use App\Models\Project;
use App\Modules\DailyTask\Repositories\Contracts\DailyTaskRepositoryInterface;
use App\Services\PlanLimitService;
use App\Helpers\TaskNumberGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class DailyTaskService
{
    public function __construct(
        protected DailyTaskRepositoryInterface $taskRepo,
        protected PlanLimitService $planService
    ) {}

    public function create(array $data, int $companyId, int $userId): DailyTask
    {
        $this->planService->checkFeatureAccess($companyId, 'limit_dailyTask');

        if ($data['task_type'] === 'daily') {
            $data['recurrent_days'] = null;
            $data['day_of_month'] = null;
        } elseif ($data['task_type'] === 'weekly') {
            $data['day_of_month'] = null;
        } elseif ($data['task_type'] === 'monthly') {
            $data['recurrent_days'] = null;
        }

        return DB::transaction(function () use ($data, $companyId, $userId) {
            return $this->taskRepo->create([
                'task_name'     => $data['task_name'],
                'description'   => $data['description'] ?? null,
                'start_date'    => $data['start_date'],
                'task_type'     => $data['task_type'],
                'recurrent_days' => $data['recurrent_days'] ?? null,
                'day_of_month'  => $data['day_of_month'] ?? null,
                'from'          => $data['from'],
                'to'            => $data['to'],
                'priority'      => $data['priority'] ?? 'normal',
                'company_id'    => $companyId,
                'dept_id'       => $data['dept_id'],
                'project_id'    => $data['project_id'] ?? null,
                'created_by'    => $userId,
                'assigned_to'   => $data['assigned_to'] ?? null,
                'active'        => true,
                'updated_by'    => null,
            ]);
        });
    }

    public function update(DailyTask $task, array $data, int $userId): DailyTask
    {
        $original = $task->getOriginal();

        if ($data['task_type'] === 'daily') {
            $data['recurrent_days'] = null;
            $data['day_of_month'] = null;
        } elseif ($data['task_type'] === 'weekly') {
            $data['day_of_month'] = null;
        } elseif ($data['task_type'] === 'monthly') {
            $data['recurrent_days'] = null;
        }

        $updateData = [
            'task_name'     => $data['task_name'] ?? $task->task_name,
            'dept_id'       => $data['dept_id'] ?? $task->dept_id,
            'description'   => $data['description'] ?? $task->description,
            'start_date'    => $data['start_date'] ?? $task->start_date,
            'task_type'     => $data['task_type'] ?? $task->task_type,
            'recurrent_days' => $data['recurrent_days'] ?? null,
            'day_of_month'  => $data['day_of_month'] ?? null,
            'from'          => $data['from'] ?? $task->from,
            'to'            => $data['to'] ?? $task->to,
            'priority'      => $data['priority'] ?? $task->priority,
            'assigned_to'   => $data['assigned_to'] ?? $task->assigned_to,
            'project_id'    => array_key_exists('project_id', $data) ? $data['project_id'] : $task->project_id,
            'updated_by'    => $userId,
        ];

        $this->taskRepo->update($task, $updateData);

        $this->createRevisions($task, $original, $userId);

        return $task;
    }

    protected function createRevisions(DailyTask $task, array $original, int $userId): void
    {
        $changes = $task->getChanges();
        $trackableFields = [
            'task_name',
            'status',
            'description',
            'start_date',
            'task_type',
            'recurrent_days',
            'day_of_month',
            'from',
            'to',
            'assigned_to',
            'note',
            'project_id',
            'dept_id'
        ];

        foreach ($changes as $field => $newValue) {
            if (!in_array($field, $trackableFields)) continue;

            $oldValue = $original[$field] ?? null;
            if (is_array($oldValue)) $oldValue = json_encode($oldValue);
            if (is_array($newValue)) $newValue = json_encode($newValue);

            if ($field === 'dept_id') {
                $oldValue = $oldValue ? optional(Department::find($oldValue))->name : null;
                $newValue = $newValue ? optional(Department::find($newValue))->name : null;
            }
            if ($field === 'project_id') {
                $oldValue = $oldValue ? optional(Project::find($oldValue))->name : null;
                $newValue = $newValue ? optional(Project::find($newValue))->name : null;
            }

            DailyTaskRevision::create([
                'daily_task_id' => $task->id,
                'user_id'       => $userId,
                'field_name'    => $field,
                'old_value'     => $oldValue,
                'new_value'     => $newValue,
                'created_at'    => now(),
            ]);
        }
    }

    public function delete(DailyTask $task): void
    {
        $this->taskRepo->delete($task);
    }

    public function toggleActive(DailyTask $task, int $userId): void
    {
        $originalActive = $task->active;
        $task->active = !$task->active;
        $this->taskRepo->update($task, ['active' => $task->active]);

        if ($originalActive !== $task->active) {
            DailyTaskRevision::create([
                'daily_task_id' => $task->id,
                'user_id'       => $userId,
                'field_name'    => 'active',
                'old_value'     => $originalActive ? '1' : '0',
                'new_value'     => $task->active ? '1' : '0',
                'created_at'    => now(),
            ]);
        }
    }

    public function getRevisions(int $taskId): array
    {
        $task = $this->taskRepo->findById($taskId);
        if (!$task) return [];

        return $task->revisions->map(function ($revision) {
            return [
                'id'          => $revision->id,
                'field_name'  => $revision->field_name,
                'old_value'   => $revision->old_value,
                'new_value'   => $revision->new_value,
                'user'        => [
                    'id'        => $revision->user->id,
                    'name'      => $revision->user->name,
                    'last_name' => $revision->user->last_name ?? null,
                    'email'     => $revision->user->email,
                ],
                'created_at'  => $revision->created_at,
                'updated_at'  => $revision->updated_at,
            ];
        })->toArray();
    }

    public function getTasksForUser(\App\Models\User $user): Collection
    {
        $companyId = $user->company_id;
        $today = now()->toDateString();
        $currentDayOfWeek = now()->dayOfWeek;
        $currentDayOfMonth = now()->day;
        $departmentIds = $user->departments()->pluck('departments.id')->toArray();

        $tasksQuery = DailyTask::query()
            ->where('company_id', $companyId)
            ->where('active', 1)
            ->whereIn('dept_id', $departmentIds)
            ->where(function ($query) use ($today, $currentDayOfWeek, $currentDayOfMonth) {
                $query->orWhere(function ($q) use ($today) {
                    $q->where('task_type', 'daily')
                        ->whereDate('start_date', '<=', $today);
                })
                    ->orWhere(function ($q) use ($today, $currentDayOfWeek) {
                        $q->where('task_type', 'weekly')
                            ->whereDate('start_date', '<=', $today)
                            ->whereJsonContains('recurrent_days', $currentDayOfWeek);
                    })
                    ->orWhere(function ($q) use ($today, $currentDayOfMonth) {
                        $q->where('task_type', 'monthly')
                            ->whereDate('start_date', '<=', $today)
                            ->where('day_of_month', $currentDayOfMonth);
                    })
                    ->orWhere(function ($q) use ($today) {
                        $q->where('task_type', 'single')
                            ->whereDate('start_date', $today);
                    })
                    ->orWhere(function ($q) use ($today) {
                        $q->where('task_type', 'last_day_of_month')
                            ->whereDate('start_date', $today)
                            ->whereRaw('DAY(LAST_DAY(start_date)) = ?', [now()->day]);
                    });
            })
            ->leftJoin('daily_task_reports', function ($join) use ($today) {
                $join->on('daily_task_reports.daily_task_id', '=', 'daily_tasks.id')
                    ->whereDate('daily_task_reports.created_at', '=', $today);
            })
            ->orderByRaw("CASE
                WHEN daily_task_reports.daily_task_id IS NULL THEN 1
                WHEN daily_task_reports.status = 'done' THEN 2
                WHEN daily_task_reports.status = 'not_done' THEN 3
                ELSE 4 END")
            ->orderBy('to', 'asc')
            ->select('daily_tasks.*', 'daily_task_reports.status as today_report_status')
            ->with([
                'department:id,name',
                'creator:id,name',
                'assignee:id,name',
                'updatedBy:id,name',
                'todayReport:id,daily_task_id,notes,task_found,status,submitted_by,created_at',
                'todayReport.submittedBy:id,name',
                'project:id,name,status'
            ]);

        return $tasksQuery->get();
    }

    public function getAllTasksForCompany(int $companyId): Collection
    {
        return $this->taskRepo->getAllByCompany($companyId, [
            'department:id,name',
            'creator:id,name,last_name',
            'assignee:id,name,last_name',
            'updatedBy:id,name,last_name',
            'todayReport:id,daily_task_id,notes,task_found,status,submitted_by,created_at',
            'todayReport.submittedBy:id,name,last_name',
            'project:id,name,status'
        ]);
    }

    public function getFilteredTasks(int $companyId, array $filters): Collection
    {
        return $this->taskRepo->getFiltered($companyId, $filters, [
            'department',
            'creator',
            'assignee',
            'updatedBy',
            'submittedBy',
            'project:id,name,status'
        ]);
    }

    public function getYesterdayEvaluationTasks(int $companyId): array
    {
        $today = Carbon::now();
        $yesterday = $today->copy()->subDay();
        $formattedDate = $yesterday->format('Y-m-d');
        $cacheKey = "evaluation_tasks_{$companyId}_" . $yesterday->format('Y-m-d');
        $cached = Cache::get($cacheKey);

        if ($cached) {
            return $cached['data'];
        }

        for ($i = 1; $i <= 15; $i++) {
            $oldDate = $today->copy()->subDays($i);
            Cache::forget("evaluation_tasks_{$companyId}_" . $oldDate->format('Y-m-d'));
        }

        $tasks = DailyTask::where('company_id', $companyId)
            ->where('active', true)
            ->select('id', 'start_date', 'task_type', 'recurrent_days', 'day_of_month', 'company_id', 'dept_id')
            ->get();

        $validTasks = $tasks->filter(function ($task) use ($yesterday) {
            return $this->shouldTaskAppearOnDate($task, $yesterday);
        });

        $numTasksPerDept = TaskNumberGenerator::getRandomDailyTaskNum($companyId);

        $groupedTasks = $validTasks
            ->groupBy('dept_id')
            ->map(function ($deptTasks) use ($numTasksPerDept) {
                return $deptTasks->random(min($numTasksPerDept, $deptTasks->count()));
            })
            ->filter()
            ->flatten(1);

        $taskIds = $groupedTasks->pluck('id')->values()->toArray();
        $responseData = [
            'date'          => $formattedDate,
            'dailytask_ids' => $taskIds,
            'count'         => count($taskIds),
        ];

        Cache::put($cacheKey, ['data' => $responseData], now()->addDay());

        return $responseData;
    }

    protected function shouldTaskAppearOnDate(DailyTask $task, Carbon $date): bool
    {
        switch ($task->task_type) {
            case 'daily':
                return true;
            case 'weekly':
                return is_array($task->recurrent_days) && in_array($date->format('l'), $task->recurrent_days);
            case 'monthly':
                return $date->day == $task->day_of_month;
            default:
                return false;
        }
    }

    public function setRandomTaskCount(int $companyId, int $count): void
    {
        TaskNumberGenerator::setRandomDailyTaskNum($companyId, $count);
    }
}
