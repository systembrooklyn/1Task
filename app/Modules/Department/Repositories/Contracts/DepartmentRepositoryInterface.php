<?php

namespace App\Modules\Department\Repositories\Contracts;

use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;

interface DepartmentRepositoryInterface
{
    public function create(array $data): Department;
    public function update(Department $department, array $data): bool;
    public function delete(Department $department): bool;
    public function findById(int $id): ?Department;
    public function getByCompany(int $companyId, array $with = []): Collection;
    public function findByNameAndCompany(string $name, int $companyId): ?Department;
}
