<?php

namespace App\Modules\DailyTask\Services;

use App\Models\DailyTask;
use App\Models\DailyTaskReport;
use App\Modules\DailyTask\Repositories\Contracts\DailyTaskReportRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class DailyTaskReportService
{
    public function __construct(
        protected DailyTaskReportRepositoryInterface $reportRepo
    ) {}

    public function submitReport(DailyTask $task, array $data, int $userId): DailyTaskReport
    {
        $reportDate = now()->toDateString();
        $existing = $this->reportRepo->getByTaskAndDate($task->id, $reportDate);
        if ($existing) {
            return $existing;
        }

        return $this->reportRepo->create([
            'daily_task_id' => $task->id,
            'submitted_by'  => $userId,
            'notes'         => $data['notes'] ?? null,
            'status'        => $data['status'],
            'task_found'    => $data['task_found'] ?? null,
        ]);
    }

    public function getReportsByDate(int $companyId, string $date, array $with = []): Collection
    {
        return $this->reportRepo->getByCompanyAndDate($companyId, $date, $with);
    }

    public function getNotReportedTasks(int $companyId, string $date): Collection
    {
        $selectedDate = Carbon::parse($date);
        $currentDayOfWeek = $selectedDate->dayOfWeek;
        $currentDayOfMonth = $selectedDate->day;

        return DailyTask::query()
            ->where('company_id', $companyId)
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
            ->whereDoesntHave('reports', function ($q) use ($selectedDate) {
                $q->whereDate('created_at', $selectedDate);
            })
            ->with([
                'department:id,name',
                'reports.submittedBy:id,name,last_name'
            ])
            ->select('id', 'task_name', 'task_no', 'start_date', 'task_type', 'recurrent_days', 'day_of_month', 'active', 'from', 'to', 'description', 'dept_id', 'priority')
            ->get();
    }

    public function getTodaysReports(int $companyId): Collection
    {
        $today = now()->toDateString();
        return DailyTask::with(['reports' => function ($q) use ($today) {
            $q->whereDate('created_at', $today);
        }])
            ->where('company_id', $companyId)
            ->get();
    }
}
