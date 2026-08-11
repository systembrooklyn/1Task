<?php

namespace App\Modules\DailyTask\Services;

use App\Models\DailyTask;
use App\Models\DailyTaskEvaluation;
use App\Models\DailyTaskEvaluationRevision;
use App\Models\Department;
use App\Modules\DailyTask\Repositories\Contracts\DailyTaskEvaluationRepositoryInterface;
use App\Services\PlanLimitService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DailyTaskEvaluationService
{
    public function __construct(
        protected DailyTaskEvaluationRepositoryInterface $evalRepo,
        protected PlanLimitService $planService
    ) {}

    public function getEvaluationByTaskAndDate(int $taskId, ?string $date): ?DailyTaskEvaluation
    {
        if (!$date) {
            return null;
        }
        return $this->evalRepo->getByTaskAndDate($taskId, $date);
    }
    public function createEvaluation(DailyTask $task, array $data, int $userId): DailyTaskEvaluation
    {
        $this->planService->checkFeatureAccess(Auth::user()->company_id, 'limit_evaluation');

        return $this->evalRepo->create([
            'daily_task_id' => $task->id,
            'user_id'       => $userId,
            'comment'       => $data['comment'] ?? null,
            'rating'        => $data['rating'],
            'label'         => $data['label'] ?? null,
            'task_for'      => $data['task_for'] ?? null,
        ]);
    }

    public function updateEvaluation(DailyTaskEvaluation $evaluation, array $data, int $userId): DailyTaskEvaluation
    {
        $original = $evaluation->getOriginal();
        $this->evalRepo->update($evaluation, $data);

        $changes = $evaluation->getChanges();
        $trackableFields = ['comment', 'rating'];
        foreach ($changes as $field => $newValue) {
            if (!in_array($field, $trackableFields)) continue;
            $oldValue = $original[$field] ?? null;
            if (is_array($oldValue)) $oldValue = json_encode($oldValue);
            if (is_array($newValue)) $newValue = json_encode($newValue);

            DailyTaskEvaluationRevision::create([
                'field_name' => $field,
                'old_value'  => $oldValue,
                'new_value'  => $newValue,
                'user_id'    => $userId,
                'daily_task_evaluation_id' => $evaluation->id,
                'created_at' => now(),
            ]);
        }

        return $evaluation;
    }

    public function deleteEvaluation(DailyTaskEvaluation $evaluation): void
    {
        $this->evalRepo->delete($evaluation);
    }

    public function getEvaluationsByTask(int $taskId): Collection
    {
        return $this->evalRepo->getByTaskId($taskId);
    }

    public function getTasksOfTheDay(int $companyId, ?string $date = null): array
    {
        try {
            $selectedDate = Carbon::parse($date)->toDateString();
        } catch (\Exception $e) {
            $selectedDate = Carbon::today()->toDateString();
        }

        $currentDayOfWeek = Carbon::parse($selectedDate)->dayOfWeek;
        $currentDayOfMonth = Carbon::parse($selectedDate)->day;

        $tasks = DailyTask::where('company_id', $companyId)
            ->where('active', 1)
            ->where(function ($query) use ($selectedDate, $currentDayOfWeek, $currentDayOfMonth) {
                $query->orWhere(function ($q) use ($selectedDate) {
                    $q->where('task_type', 'daily')
                        ->whereDate('start_date', '<=', $selectedDate);
                })
                    ->orWhere(function ($q) use ($selectedDate, $currentDayOfWeek) {
                        $q->where('task_type', 'weekly')
                            ->whereDate('start_date', '<=', $selectedDate)
                            ->whereJsonContains('recurrent_days', $currentDayOfWeek);
                    })
                    ->orWhere(function ($q) use ($selectedDate, $currentDayOfMonth) {
                        $q->where('task_type', 'monthly')
                            ->whereDate('start_date', '<=', $selectedDate)
                            ->where('day_of_month', $currentDayOfMonth);
                    })
                    ->orWhere(function ($q) use ($selectedDate) {
                        $q->where('task_type', 'single')
                            ->whereDate('start_date', $selectedDate);
                    })
                    ->orWhere(function ($q) use ($selectedDate) {
                        $q->where('task_type', 'last_day_of_month')
                            ->whereDate('start_date', $selectedDate)
                            ->whereRaw('DAY(LAST_DAY(start_date)) = ?', [Carbon::parse($selectedDate)->day]);
                    });
            })
            ->whereHas('evaluations', function ($q) use ($selectedDate) {
                $q->whereDate('task_for', $selectedDate);
            })
            ->with([
                'department:id,name',
                'reports' => function ($q) use ($selectedDate) {
                    $q->whereDate('created_at', '=', $selectedDate)
                        ->with('submittedBy:id,name,last_name');
                },
                'evaluations' => function ($q) use ($selectedDate) {
                    $q->whereDate('task_for', $selectedDate)
                        ->with('evaluator:id,name,last_name');
                },
            ])
            ->select([
                'id',
                'task_no',
                'task_name',
                'description',
                'start_date',
                'task_type',
                'recurrent_days',
                'day_of_month',
                'active',
                'from',
                'to',
                'dept_id',
                'priority'
            ])
            ->get();

        $result = $tasks->map(function ($task) use ($selectedDate) {
            $report = $task->reports->first();
            $evaluation = $task->evaluations->first();
            return [
                'daily_task_id' => $task->id,
                'daily_task'    => [
                    'task_no'        => $task->task_no,
                    'task_name'      => $task->task_name,
                    'description'    => $task->description,
                    'start_date'     => $task->start_date,
                    'task_type'      => $task->task_type,
                    'recurrent_days' => $task->recurrent_days,
                    'day_of_month'   => $task->day_of_month,
                    'active'         => $task->active,
                    'from'           => $task->from,
                    'to'             => $task->to,
                    'priority'       => $task->priority
                ],
                'report' => $report ? (object) [
                    'id' => $report->id,
                    'daily_task_id' => $report->daily_task_id,
                    'notes' => $report->notes,
                    'status' => $report->status,
                    'created_at' => $report->created_at->toDateTimeString(),
                    'updated_at' => $report->updated_at->toDateTimeString(),
                    'task_found' => $report->task_found,
                    'user' => (object) [
                        'id' => $report->submittedBy->id,
                        'name' => $report->submittedBy->name,
                        'last_name' => $report->submittedBy->last_name ?? null
                    ]
                ] : null,
                'department' => $task->department ? [
                    'id'   => $task->department->id,
                    'name' => $task->department->name,
                ] : null,
                'has_evaluation' => true,
                'evaluation' => $evaluation ? [
                    'id'         => $evaluation->id,
                    'comment'    => $evaluation->comment,
                    'rating'     => $evaluation->rating,
                    'label'      => $evaluation->label,
                    'task_for'   => $evaluation->task_for,
                    'created_at' => $evaluation->created_at->toDateTimeString(),
                    'evaluator'  => $evaluation->evaluator ? [
                        'id'   => $evaluation->evaluator->id,
                        'name' => $evaluation->evaluator->name,
                        'last_name' => $evaluation->evaluator->last_name ?? null,
                    ] : null,
                ] : null
            ];
        });

        return ['date' => $selectedDate, 'data' => $result];
    }

    public function getDeptPerformance(int $companyId, ?string $from, ?string $to): array
    {
        if (!$from && !$to) {
            $from = Carbon::now()->startOfMonth()->toDateString();
            $to = Carbon::now()->toDateString();
        } elseif ($from && !$to) {
            $to = $from;
        }

        $departments = Department::where('company_id', $companyId)->pluck('name', 'id');
        if ($departments->isEmpty()) {
            return ['evaluations_by_department' => []];
        }

        $taskIds = DailyTask::where('company_id', $companyId)->pluck('id');
        if ($taskIds->isEmpty()) {
            return ['evaluations_by_department' => []];
        }

        $evaluations = DailyTaskEvaluation::whereIn('daily_task_id', $taskIds)
            ->whereBetween('task_for', [$from, $to])
            ->with('dailyTask:id,dept_id')
            ->get(['id', 'daily_task_id', 'rating']);

        $deptStats = [];
        foreach ($evaluations as $evaluation) {
            $deptId = $evaluation->dailyTask->dept_id;
            if (!isset($deptStats[$deptId])) {
                $deptStats[$deptId] = [
                    'department_name' => $departments[$deptId],
                    'sum_rating' => 0,
                    'count' => 0,
                ];
            }
            $deptStats[$deptId]['sum_rating'] += $evaluation->rating;
            $deptStats[$deptId]['count'] += 1;
        }

        $result = [];
        foreach ($deptStats as $deptId => $stats) {
            $totalRate = $stats['count'] > 0 ? round(($stats['sum_rating'] / ($stats['count'] * 10)) * 100, 2) : 0;
            $result[] = ['department_name' => $stats['department_name'], 'total_rate' => round($totalRate)];
        }

        if (empty($result)) {
            return ['message' => 'No evaluations found for the selected period.', 'data' => [], 'range' => compact('from', 'to')];
        }

        $overallPerformance = round(collect($result)->avg('total_rate'), 2);
        return [
            'message' => "Performance Retrieved Successfully between $from to $to",
            'data' => [
                'company_performance' => $overallPerformance,
                'range' => compact('from', 'to'),
                'dept_performance' => $result,
            ]
        ];
    }

    public function getUserPerformance(int $userId, ?string $from, ?string $to): array
    {
        if (!$from && !$to) {
            $from = Carbon::now()->startOfMonth()->toDateString();
            $to = Carbon::now()->toDateString();
        } elseif ($from && !$to) {
            $to = $from;
        }

        $targetUser = \App\Models\User::where('id', $userId)->first(['name', 'email']);
        $reports = \App\Models\DailyTaskReport::where('submitted_by', $userId)
            ->whereBetween('created_at', [Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay()])
            ->select('daily_task_id', DB::raw('DATE(created_at) as report_date'))
            ->get()
            ->groupBy('daily_task_id');

        if ($reports->isEmpty()) {
            return ['message' => 'No reports found for the selected period.', 'data' => []];
        }

        $taskDateMap = [];
        foreach ($reports as $taskId => $reportGroup) {
            $taskDateMap[$taskId] = $reportGroup->pluck('report_date')->unique()->toArray();
        }

        $evaluations = DailyTaskEvaluation::whereIn('daily_task_id', array_keys($taskDateMap))
            ->whereBetween('task_for', [Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay()])
            ->join('daily_tasks', 'daily_tasks.id', '=', 'daily_task_evaluations.daily_task_id')
            ->select('daily_task_evaluations.*', 'daily_tasks.dept_id')
            ->get();

        if ($evaluations->isEmpty()) {
            return ['message' => 'No evaluations found for your reports in the selected period.', 'data' => []];
        }

        $deptStats = [];
        foreach ($evaluations as $evaluation) {
            $deptId = $evaluation->dept_id;
            if (!isset($deptStats[$deptId])) {
                $department = \App\Models\Department::find($deptId);
                $deptStats[$deptId] = [
                    'department_name' => optional($department)->name ?? 'Unknown',
                    'sum_rating' => 0,
                    'count' => 0,
                ];
            }
            $rating = data_get($evaluation, 'rating');
            if ($rating !== null) {
                $deptStats[$deptId]['sum_rating'] += $rating;
                $deptStats[$deptId]['count'] += 1;
            }
        }

        $result = [];
        foreach ($deptStats as $deptId => $stats) {
            $totalRate = $stats['count'] > 0 ? round(($stats['sum_rating'] / ($stats['count'] * 10)) * 100, 2) : 0;
            $result[] = [
                'department_name' => $stats['department_name'],
                'total_rate' => $totalRate,
                'evaluation_count' => $stats['count']
            ];
        }

        $overallPerformance = round(collect($result)->avg('total_rate'), 2);
        return [
            'message' => "Performance Retrieved Successfully between $from to $to",
            'data' => [
                'user' => $targetUser,
                'user_performance' => $overallPerformance,
                'range' => compact('from', 'to'),
                'performance_by_department' => $result,
            ]
        ];
    }
}
