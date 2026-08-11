<?php

namespace App\Modules\DailyTask\Repositories\Eloquent;

use App\Models\DailyTaskEvaluation;
use App\Modules\DailyTask\Repositories\Contracts\DailyTaskEvaluationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentDailyTaskEvaluationRepository implements DailyTaskEvaluationRepositoryInterface
{
    public function create(array $data): DailyTaskEvaluation
    {
        return DailyTaskEvaluation::create($data);
    }

    public function update(DailyTaskEvaluation $evaluation, array $data): bool
    {
        return $evaluation->update($data);
    }

    public function delete(DailyTaskEvaluation $evaluation): bool
    {
        return $evaluation->delete();
    }

    public function findById(int $id): ?DailyTaskEvaluation
    {
        return DailyTaskEvaluation::find($id);
    }

    public function getByTaskId(int $taskId): Collection
    {
        return DailyTaskEvaluation::where('daily_task_id', $taskId)->get();
    }

    public function getByTaskAndDate(int $taskId, string $date): ?DailyTaskEvaluation
    {
        return DailyTaskEvaluation::where('daily_task_id', $taskId)
            ->whereDate('task_for', $date)
            ->first();
    }

    public function getByCompanyAndDateRange(int $companyId, string $from, string $to, array $with = []): Collection
    {
        return DailyTaskEvaluation::whereHas('dailyTask', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })
            ->whereBetween('task_for', [$from, $to])
            ->with($with)
            ->get();
    }
}
