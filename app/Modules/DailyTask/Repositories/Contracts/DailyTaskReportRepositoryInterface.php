<?php

namespace App\Modules\DailyTask\Repositories\Contracts;

use App\Models\DailyTaskReport;
use Illuminate\Database\Eloquent\Collection;

interface DailyTaskReportRepositoryInterface
{
    public function create(array $data): DailyTaskReport;
    public function getByTaskAndDate(int $taskId, string $date): ?DailyTaskReport;
    public function getByCompanyAndDate(int $companyId, string $date, array $with = []): Collection;
    public function getByUserIdAndDateRange(int $userId, string $from, string $to, array $with = []): Collection;
}
