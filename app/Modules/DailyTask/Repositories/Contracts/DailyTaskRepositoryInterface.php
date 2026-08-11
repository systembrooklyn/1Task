<?php

namespace App\Modules\DailyTask\Repositories\Contracts;

use App\Models\DailyTask;
use Illuminate\Database\Eloquent\Collection;

interface DailyTaskRepositoryInterface
{
    public function create(array $data): DailyTask;
    public function update(DailyTask $task, array $data): bool;
    public function delete(DailyTask $task): bool;
    public function findById(int $id): ?DailyTask;
    public function getByCompany(int $companyId, array $with = []): Collection;
    public function getActiveForDepartment(int $companyId, array $departmentIds, string $date, array $with = []): Collection;
    public function getAllByCompany(int $companyId, array $with = []): Collection;
    public function getFiltered(int $companyId, array $filters, array $with = []): Collection;
}
