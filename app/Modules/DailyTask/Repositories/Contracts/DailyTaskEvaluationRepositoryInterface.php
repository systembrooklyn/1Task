<?php

namespace App\Modules\DailyTask\Repositories\Contracts;

use App\Models\DailyTaskEvaluation;
use Illuminate\Database\Eloquent\Collection;

interface DailyTaskEvaluationRepositoryInterface
{
    public function create(array $data): DailyTaskEvaluation;
    public function update(DailyTaskEvaluation $evaluation, array $data): bool;
    public function delete(DailyTaskEvaluation $evaluation): bool;
    public function findById(int $id): ?DailyTaskEvaluation;
    public function getByTaskId(int $taskId): Collection;
    public function getByTaskAndDate(int $taskId, string $date): ?DailyTaskEvaluation;
    public function getByCompanyAndDateRange(int $companyId, string $from, string $to, array $with = []): Collection;
}
