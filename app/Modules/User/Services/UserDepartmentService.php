<?php

namespace App\Modules\User\Services;

use App\Models\User;
use App\Models\Department;
use App\Modules\User\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Modules\User\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;

class UserDepartmentService
{
    public function __construct(
        protected DepartmentRepositoryInterface $deptRepo,
        protected UserRepositoryInterface $userRepo
    ) {}

    public function assignDepartments(int $userId, array $departmentIds): bool
    {
        $user = $this->userRepo->findById($userId);
        if (!$user) {
            return false;
        }

        $departments = Department::whereIn('id', $departmentIds)->get();
        foreach ($departments as $dept) {
            if ($dept->company_id !== $user->company_id) {
                return false;
            }
        }

        $user->departments()->sync($departmentIds);
        return true;
    }

    public function unassignDepartment(int $userId, int $departmentId): bool
    {
        $user = $this->userRepo->findById($userId);
        if (!$user) {
            return false;
        }

        $department = $this->deptRepo->findById($departmentId);
        if (!$department) {
            return false;
        }

        if (!$user->departments->contains($department)) {
            return false;
        }

        $user->departments()->detach($departmentId);
        return true;
    }

    public function assignManager(int $departmentId, int $userId): bool
    {
        $department = $this->deptRepo->findById($departmentId);
        if (!$department) {
            return false;
        }

        $user = $this->userRepo->findById($userId);
        if (!$user) {
            return false;
        }

        if ($user->company_id !== $department->company_id) {
            return false;
        }

        return $this->deptRepo->assignManager($departmentId, $userId);
    }

    public function getUsersInDepartment(int $departmentId): array
    {
        $users = $this->deptRepo->getUsersInDepartment($departmentId);
        return $users->map(function ($user) {
            return [
                'id'         => $user->id,
                'name'       => $user->name,
                'last_name'  => $user->last_name,
                'email'      => $user->email,
                'fireToken'  => $user->fireToken,
            ];
        })->toArray();
    }

    public function getFireTokensForDepartment(int $departmentId): array
    {
        return $this->deptRepo->getFireTokensForDepartment($departmentId);
    }
}
