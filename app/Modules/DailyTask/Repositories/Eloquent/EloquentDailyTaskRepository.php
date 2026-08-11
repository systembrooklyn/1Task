<?php

namespace App\Modules\DailyTask\Repositories\Eloquent;

use App\Models\DailyTask;
use App\Modules\DailyTask\Repositories\Contracts\DailyTaskRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class EloquentDailyTaskRepository implements DailyTaskRepositoryInterface
{
    public function create(array $data): DailyTask
    {
        return DailyTask::create($data);
    }

    public function update(DailyTask $task, array $data): bool
    {
        return $task->update($data);
    }

    public function delete(DailyTask $task): bool
    {
        return $task->delete();
    }

    public function findById(int $id): ?DailyTask
    {
        return DailyTask::find($id);
    }

    public function getByCompany(int $companyId, array $with = []): Collection
    {
        return DailyTask::where('company_id', $companyId)->with($with)->get();
    }

    public function getActiveForDepartment(int $companyId, array $departmentIds, string $date, array $with = []): Collection
    {
        $currentDayOfWeek = Carbon::parse($date)->dayOfWeek;
        $currentDayOfMonth = Carbon::parse($date)->day;

        return DailyTask::query()
            ->where('company_id', $companyId)
            ->where('active', 1)
            ->whereIn('dept_id', $departmentIds)
            ->where(function ($query) use ($date, $currentDayOfWeek, $currentDayOfMonth) {
                $query->orWhere(function ($q) use ($date) {
                    $q->where('task_type', 'daily')
                        ->whereDate('start_date', '<=', $date);
                })
                    ->orWhere(function ($q) use ($date, $currentDayOfWeek) {
                        $q->where('task_type', 'weekly')
                            ->whereDate('start_date', '<=', $date)
                            ->whereJsonContains('recurrent_days', $currentDayOfWeek);
                    })
                    ->orWhere(function ($q) use ($date, $currentDayOfMonth) {
                        $q->where('task_type', 'monthly')
                            ->whereDate('start_date', '<=', $date)
                            ->where('day_of_month', $currentDayOfMonth);
                    })
                    ->orWhere(function ($q) use ($date) {
                        $q->where('task_type', 'single')
                            ->whereDate('start_date', $date);
                    })
                    ->orWhere(function ($q) use ($date) {
                        $q->where('task_type', 'last_day_of_month')
                            ->whereDate('start_date', $date)
                            ->whereRaw('DAY(LAST_DAY(start_date)) = ?', [\Carbon\Carbon::parse($date)->day]);
                    });
            })
            ->with($with)
            ->get();
    }

    public function getAllByCompany(int $companyId, array $with = []): Collection
    {
        return DailyTask::where('company_id', $companyId)
            ->orderBy('task_no', 'desc')
            ->with($with)
            ->get();
    }

    public function getFiltered(int $companyId, array $filters, array $with = []): Collection
    {
        $query = DailyTask::where('company_id', $companyId)->with($with);

        if (!empty($filters['dept_ids'])) {
            $query->whereIn('dept_id', $filters['dept_ids']);
        }
        if (!empty($filters['task_type'])) {
            $query->where('task_type', $filters['task_type']);
        }
        if (isset($filters['active'])) {
            $query->where('active', filter_var($filters['active'], FILTER_VALIDATE_BOOLEAN));
        }
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->get();
    }
}
