<?php

namespace App\Modules\Department\Repositories\Eloquent;

use App\Models\Department;
use App\Modules\Department\Repositories\Contracts\DepartmentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentDepartmentRepository implements DepartmentRepositoryInterface
{
    public function create(array $data): Department
    {
        return Department::create($data);
    }

    public function update(Department $department, array $data): bool
    {
        return $department->update($data);
    }

    public function delete(Department $department): bool
    {
        return $department->delete();
    }

    public function findById(int $id): ?Department
    {
        return Department::find($id);
    }

    public function getByCompany(int $companyId, array $with = []): Collection
    {
        return Department::where('company_id', $companyId)
            ->with($with)
            ->get();
    }

    public function findByNameAndCompany(string $name, int $companyId): ?Department
    {
        return Department::where('name', $name)
            ->where('company_id', $companyId)
            ->first();
    }
}
