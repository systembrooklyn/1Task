<?php

namespace App\Modules\User\Repositories\Contracts;

use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;

interface DepartmentRepositoryInterface
{
    public function findById(int $id): ?Department;
    public function getByCompany(int $companyId): Collection;
    public function getUsersInDepartment(int $departmentId): Collection;
    public function getFireTokensForDepartment(int $departmentId): array;
    public function assignManager(int $departmentId, int $userId): bool;
}
