<?php

namespace App\Modules\User\Repositories\Eloquent;

use App\Models\Department;
use App\Modules\User\Repositories\Contracts\DepartmentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentDepartmentRepository implements DepartmentRepositoryInterface
{
    public function findById(int $id): ?Department
    {
        return Department::find($id);
    }

    public function getByCompany(int $companyId): Collection
    {
        return Department::where('company_id', $companyId)->get();
    }

    public function getUsersInDepartment(int $departmentId): Collection
    {
        $department = Department::with('users')->find($departmentId);
        return $department ? $department->users : collect();
    }

    public function getFireTokensForDepartment(int $departmentId): array
    {
        $department = Department::with('users')->find($departmentId);
        if (!$department) {
            return [];
        }
        return $department->users()
            ->whereNotNull('fireToken')
            ->pluck('fireToken')
            ->toArray();
    }

    public function assignManager(int $departmentId, int $userId): bool
    {
        $department = Department::find($departmentId);
        if (!$department) {
            return false;
        }
        $department->user_id = $userId;
        return $department->save();
    }
}
