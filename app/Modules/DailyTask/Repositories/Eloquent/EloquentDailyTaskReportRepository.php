<?php

namespace App\Modules\DailyTask\Repositories\Eloquent;

use App\Models\DailyTaskReport;
use App\Modules\DailyTask\Repositories\Contracts\DailyTaskReportRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentDailyTaskReportRepository implements DailyTaskReportRepositoryInterface
{
    public function create(array $data): DailyTaskReport
    {
        return DailyTaskReport::create($data);
    }

    public function getByTaskAndDate(int $taskId, string $date): ?DailyTaskReport
    {
        return DailyTaskReport::where('daily_task_id', $taskId)
            ->whereDate('created_at', $date)
            ->first();
    }

    public function getByCompanyAndDate(int $companyId, string $date, array $with = []): Collection
    {
        return DailyTaskReport::whereHas('dailyTask', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })
            ->whereDate('created_at', $date)
            ->with($with)
            ->get();
    }

    public function getByUserIdAndDateRange(int $userId, string $from, string $to, array $with = []): Collection
    {
        return DailyTaskReport::where('submitted_by', $userId)
            ->whereBetween('created_at', [$from, $to])
            ->with($with)
            ->get();
    }
}
