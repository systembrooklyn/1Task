<?php

namespace App\Modules\User\Services;

use App\Models\DailyTask;
use App\Models\DailyTaskEvaluation;
use App\Models\Department;
use App\Models\Invitation;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    protected User $user;
    protected int $companyId;
    protected array $departmentIds;
    protected bool $isOwner;
    protected bool $hasOwnerPermission;
    protected bool $hasDashboardPermission;

    public function __construct()
    {
        $this->user = auth('sanctum')->user();
        if (!$this->user) {
            abort(401, 'Unauthorized');
        }
        $this->companyId = $this->user->company_id;
        $this->departmentIds = $this->user->departments()->pluck('departments.id')->toArray();
        $permissions = $this->user->getAllPermissions()->pluck('name')->toArray();
        $this->hasOwnerPermission = in_array('view-dashboard-owner', $permissions);
        $this->hasDashboardPermission = in_array('view-dashboard', $permissions);
        $this->isOwner = $this->user->companies()->wherePivot('company_id', $this->companyId)->exists();
    }

    public function getCounts($date = null): array
    {
        $selectedDate = $date ? Carbon::parse($date) : now();

        if ($this->isOwner || $this->hasOwnerPermission) {
            $countEmps = $this->countOwnerEmps($selectedDate);
            $countProjects = $this->countOwnerProjects($selectedDate);
            $countDepartments = $this->countOwnerDepts($selectedDate);
            $countDailyTasks = $this->countOwnerDeptDailyTasks($selectedDate);
            $countAllDailyTasks = $this->countOwnerAllDailyTasks($selectedDate);
            $countEvaluations = $this->countOwnerEvaluations($selectedDate);
            $taskStats = $this->getUserTaskStats();
        } elseif ($this->hasDashboardPermission) {
            $countEmps = $this->countDeptEmps($selectedDate);
            $countProjects = $this->countProjectsEmps($selectedDate);
            $countDepartments = $this->counDept($selectedDate);
            $countDailyTasks = $this->countDeptDailyTasks($selectedDate);
            $countAllDailyTasks = null;
            $countEvaluations = $this->countDeptEvaluations($selectedDate);
            $taskStats = $this->getUserTaskStats();
        } else {
            throw new AuthorizationException('You Dont have permission to view Dashboard');
        }

        return [
            'Emps'           => $countEmps,
            'Projects'       => $countProjects,
            'AllDailyTasks'  => $countAllDailyTasks,
            'DailyTasks'     => $countDailyTasks,
            'Departments'    => $countDepartments,
            'Evaluations'    => $countEvaluations,
            'Tasks'          => $taskStats,
        ];
    }

    // ========== Owner-level counts (optimized) ==========

    protected function countOwnerEmps(Carbon $date): array
    {
        $total = User::where('company_id', $this->companyId)
            ->whereDate('created_at', '<=', $date)
            ->where('is_deleted', 0)
            ->count();

        $invited = Invitation::where('company_id', $this->companyId)
            ->whereDate('created_at', '<=', $date)
            ->where('is_accepted', 1)
            ->count();

        $pending = Invitation::where('company_id', $this->companyId)
            ->whereDate('created_at', '<=', $date)
            ->where('is_accepted', 0)
            ->where('expires_at', '>=', now())
            ->count();

        return ['total' => max(0, $total - 1), 'invited' => $invited, 'pending' => $pending];
    }

    protected function countOwnerProjects(Carbon $date): array
    {
        $query = Project::where('company_id', $this->companyId)
            ->whereDate('created_at', '<=', $date ?? now());

        $total = $query->count();
        $active = (clone $query)->where('status', 1)->count();
        $inActive = $total - $active;

        return ['total' => $total, 'active' => $active, 'inActive' => $inActive];
    }

    protected function countOwnerDepts(Carbon $date): array
    {
        $departments = Department::where('company_id', $this->companyId)
            ->whereDate('created_at', '<=', $date)
            ->withCount('users')
            ->get();

        return [
            'total' => $departments->count(),
            'Departments' => $departments->map(fn($dept) => [
                'department_name' => $dept->name,
                'total_users' => $dept->users_count,
            ]),
        ];
    }

    protected function countOwnerDeptDailyTasks(Carbon $date): array
    {
        $selectedDate = $date;
        $dayOfWeek = $selectedDate->dayOfWeek;
        $dayOfMonth = $selectedDate->day;

        $tasks = DailyTask::select([
            'id',
            'dept_id',
            'task_type',
            'recurrent_days',
            'day_of_month',
            'active',
            'company_id'
        ])
            ->where('company_id', $this->companyId)
            ->where('active', 1)
            ->where(function ($query) use ($dayOfWeek, $dayOfMonth) {
                $query->where('task_type', 'daily')
                    ->orWhere(function ($q) use ($dayOfWeek) {
                        $q->where('task_type', 'weekly')
                            ->whereJsonContains('recurrent_days', $dayOfWeek);
                    })
                    ->orWhere(function ($q) use ($dayOfMonth) {
                        $q->where('task_type', 'monthly')
                            ->where('day_of_month', $dayOfMonth);
                    });
            })
            ->with([
                'department:id,name',
                'todayReport' => function ($q) use ($selectedDate) {
                    $q->select('id', 'daily_task_id', 'status')
                        ->whereDate('created_at', $selectedDate);
                }
            ])
            ->get();

        $departmentData = $tasks->groupBy('dept_id')->map(function ($deptTasks) {
            $dept = $deptTasks->first()->department;
            $deptName = $dept->name ?? 'Unknown';

            $withReports = $deptTasks->filter(fn($t) => $t->todayReport);
            $done = $withReports->filter(fn($t) => $t->todayReport->status === 'done')->count();

            return [
                'department_name'  => $deptName,
                'total_tasks'      => $deptTasks->count(),
                'total_reports'    => $withReports->count(),
                'done_reports'     => $done,
                'not_done_reports' => $withReports->count() - $done,
            ];
        });

        $tasksWithReports = $tasks->filter(fn($t) => $t->todayReport);
        $totalReports = $tasksWithReports->count();
        $doneReports = $tasksWithReports->filter(fn($t) => $t->todayReport->status === 'done')->count();

        return [
            'today_total_daily_tasks' => $tasks->count(),
            'total_reports'           => $totalReports,
            'done_reports'            => $doneReports,
            'not_done_reports'        => $totalReports - $doneReports,
            'DailyTaskDepts'          => $departmentData->values(),
        ];
    }

    protected function countOwnerAllDailyTasks(Carbon $date): array
    {
        $base = DailyTask::where('company_id', $this->companyId)
            ->whereDate('created_at', '<=', $date);

        $total = (clone $base)->count();
        $active = (clone $base)->where('active', 1)->count();
        $inActive = $total - $active;

        return ['total' => $total, 'active' => $active, 'inActive' => $inActive];
    }

    protected function countOwnerEvaluations(Carbon $date): array
    {
        $selectedDate = $date ? $date->toDateString() : now()->toDateString();

        $evaluations = DailyTaskEvaluation::join('daily_tasks', 'daily_tasks.id', '=', 'daily_task_evaluations.daily_task_id')
            ->where('daily_tasks.company_id', $this->companyId)
            ->whereDate('daily_task_evaluations.created_at', $selectedDate)
            ->select(
                'daily_tasks.dept_id',
                DB::raw('avg(daily_task_evaluations.rating) as average_rating'),
                DB::raw('count(daily_task_evaluations.id) as total_evaluations')
            )
            ->groupBy('daily_tasks.dept_id')
            ->get()
            ->keyBy('dept_id');

        $total = (int) $evaluations->sum('total_evaluations');

        $departments = Department::where('company_id', $this->companyId)
            ->get(['id', 'name'])
            ->map(function ($dept) use ($evaluations) {
                $data = $evaluations->get($dept->id);
                $avg = $data ? (float) $data->average_rating : 0;
                return [
                    'department_name' => $dept->name,
                    'total_evaluations' => (int) ($data ? $data->total_evaluations : 0),
                    'average_rating' => round($avg, 2),
                    'percentage' => round($avg * 10, 2),
                ];
            });

        return [
            'total_evaluations' => $total,
            'evaluations_by_department' => $departments,
        ];
    }

    // ========== Department-level counts (optimized) ==========

    protected function countDeptEmps(Carbon $date): array
    {
        $departmentIds = $this->departmentIds;

        $total = User::where('company_id', $this->companyId)
            ->whereDate('created_at', '<=', $date)
            ->where('is_deleted', 0)
            ->whereHas('departments', fn($q) => $q->whereIn('departments.id', $departmentIds))
            ->count();

        return ['total' => $total, 'invited' => null, 'pending' => null];
    }

    protected function countProjectsEmps(Carbon $date): array
    {
        $departmentIds = $this->departmentIds;

        $query = Project::where('company_id', $this->companyId)
            ->whereDate('created_at', '<=', $date ?? now())
            ->whereHas('departments', fn($q) => $q->whereIn('departments.id', $departmentIds));

        $total = $query->count();
        $active = (clone $query)->where('status', 1)->count();
        $inActive = $total - $active;

        return ['total' => $total, 'active' => $active, 'inActive' => $inActive];
    }

    protected function countDeptDailyTasks(Carbon $date): array
    {
        $selectedDate = $date;
        $dayOfWeek = $selectedDate->dayOfWeek;
        $dayOfMonth = $selectedDate->day;
        $departmentIds = is_array($this->departmentIds) ? $this->departmentIds : [$this->departmentIds];

        $tasks = DailyTask::select([
            'id',
            'dept_id',
            'task_type',
            'recurrent_days',
            'day_of_month',
            'active',
            'company_id'
        ])
            ->where('company_id', $this->companyId)
            ->where('active', 1)
            ->whereIn('dept_id', $departmentIds)
            ->where(function ($query) use ($dayOfWeek, $dayOfMonth) {
                $query->where('task_type', 'daily')
                    ->orWhere(function ($q) use ($dayOfWeek) {
                        $q->where('task_type', 'weekly')
                            ->whereJsonContains('recurrent_days', $dayOfWeek);
                    })
                    ->orWhere(function ($q) use ($dayOfMonth) {
                        $q->where('task_type', 'monthly')
                            ->where('day_of_month', $dayOfMonth);
                    });
            })
            ->with([
                'department:id,name',
                'todayReport' => function ($q) use ($selectedDate) {
                    $q->select('id', 'daily_task_id', 'status')
                        ->whereDate('created_at', $selectedDate);
                }
            ])
            ->get();

        $departmentData = $tasks->groupBy('dept_id')->map(function ($deptTasks) {
            $dept = $deptTasks->first()->department;
            $deptName = $dept->name ?? 'Unknown';

            $withReports = $deptTasks->filter(fn($t) => $t->todayReport);
            $done = $withReports->filter(fn($t) => $t->todayReport->status === 'done')->count();

            return [
                'department_name'  => $deptName,
                'total_tasks'      => $deptTasks->count(),
                'total_reports'    => $withReports->count(),
                'done_reports'     => $done,
                'not_done_reports' => $withReports->count() - $done,
            ];
        });

        $tasksWithReports = $tasks->filter(fn($t) => $t->todayReport);
        $totalReports = $tasksWithReports->count();
        $doneReports = $tasksWithReports->filter(fn($t) => $t->todayReport->status === 'done')->count();

        return [
            'today_total_daily_tasks' => $tasks->count(),
            'total_reports'           => $totalReports,
            'done_reports'            => $doneReports,
            'not_done_reports'        => $totalReports - $doneReports,
            'DailyTaskDepts'          => $departmentData->values(),
        ];
    }

    protected function countDeptEvaluations(Carbon $date): array
    {
        $selectedDate = $date ? $date->toDateString() : now()->toDateString();
        $departmentIds = $this->departmentIds;

        $evaluations = DailyTaskEvaluation::join('daily_tasks', 'daily_tasks.id', '=', 'daily_task_evaluations.daily_task_id')
            ->where('daily_tasks.company_id', $this->companyId)
            ->whereIn('daily_tasks.dept_id', $departmentIds)
            ->whereDate('daily_task_evaluations.created_at', $selectedDate)
            ->select(
                'daily_tasks.dept_id',
                DB::raw('avg(daily_task_evaluations.rating) as average_rating'),
                DB::raw('count(daily_task_evaluations.id) as total_evaluations')
            )
            ->groupBy('daily_tasks.dept_id')
            ->get()
            ->keyBy('dept_id');

        $total = $evaluations->sum('total_evaluations');

        $departments = Department::whereIn('id', $departmentIds)
            ->get(['id', 'name'])
            ->map(function ($dept) use ($evaluations) {
                $data = $evaluations->get($dept->id);
                $avg = $data ? $data->average_rating : 0;
                return [
                    'department_name' => $dept->name,
                    'total_evaluations' => $data ? $data->total_evaluations : 0,
                    'average_rating' => round($avg, 2),
                    'percentage' => round($avg * 10, 2),
                ];
            });

        return [
            'total_evaluations' => $total,
            'evaluations_by_department' => $departments,
        ];
    }

    protected function counDept($date): array
    {
        $departments = $this->user->departments()
            ->where('company_id', $this->companyId)
            ->withCount('users')
            ->get();

        return [
            'total' => $departments->count(),
            'Departments' => $departments->map(fn($dept) => [
                'department_name' => $dept->name,
                'total_users' => $dept->users_count,
            ]),
        ];
    }

    protected function getUserTaskStats(): array
    {
        $userId = $this->user->id;
        $today = now()->startOfDay();
        $start = now()->addDay()->startOfDay();
        $end = now()->addDays(2)->endOfDay();

        $tasksQuery = Task::where('company_id', $this->companyId)
            ->where(function ($q) use ($userId) {
                $q->where('creator_user_id', $userId)
                    ->orWhere('supervisor_user_id', $userId)
                    ->orWhereHas('users', function ($sub) use ($userId) {
                        $sub->where('user_id', $userId)
                            ->whereIn('role', ['assigned', 'consult', 'informer']);
                    });
            });

        $stats = (clone $tasksQuery)->selectRaw('
        sum(case when status = "pending" then 1 else 0 end) as pending,
        sum(case when status = "inProgress" then 1 else 0 end) as inProgress,
        sum(case when status = "done" then 1 else 0 end) as done,
        sum(case when status = "review" then 1 else 0 end) as review
    ')->first();

        $pending = (int) ($stats->pending ?? 0);
        $inProgress = (int) ($stats->inProgress ?? 0);
        $done = (int) ($stats->done ?? 0);
        $review = (int) ($stats->review ?? 0);

        $urgent = (clone $tasksQuery)->where('priority', 'urgent')
            ->whereNotIn('status', ['done', 'review'])
            ->count();

        $overdue = (clone $tasksQuery)->where('deadline', '<', $today)
            ->whereNotIn('status', ['done', 'review'])
            ->count();

        $dueSoon = (clone $tasksQuery)->whereBetween('deadline', [$start, $end])
            ->whereNotIn('status', ['done', 'review'])
            ->count();

        $dueToday = (clone $tasksQuery)->where('deadline', '=', $today)
            ->whereNotIn('status', ['done', 'review'])
            ->count();

        return [
            'pending'     => $pending,
            'inProgress'  => $inProgress,
            'urgent'      => $urgent,
            'done'        => $done,
            'review'      => $review,
            'overdue'     => $overdue,
            'dueSoon'     => $dueSoon,
            'dueToday'    => $dueToday,
        ];
    }
}
