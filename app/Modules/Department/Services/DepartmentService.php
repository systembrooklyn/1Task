<?php

namespace App\Modules\Department\Services;

use App\Models\Department;
use App\Modules\Department\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Services\PlanLimitService;
use Illuminate\Support\Facades\Auth;

class DepartmentService
{
    public function __construct(
        protected DepartmentRepositoryInterface $departmentRepo,
        protected PlanLimitService $planService
    ) {}

    public function getCompanyDepartments(int $companyId): array
    {
        $departments = $this->departmentRepo->getByCompany($companyId, ['manager']);
        return $departments->map(function ($department) {
            return [
                'id'           => $department->id,
                'name'         => $department->name,
                'manager_name' => $department->manager ? $department->manager->name : 'No manager assigned',
            ];
        })->toArray();
    }

    public function createDepartment(array $data, int $companyId, int $userId): Department
    {
        $this->planService->checkFeatureAccess($companyId, 'limit_department');

        $existing = $this->departmentRepo->findByNameAndCompany($data['name'], $companyId);
        if ($existing) {
            throw new \Exception('Department already exists for this company.', 400);
        }

        return $this->departmentRepo->create([
            'name'       => $data['name'],
            'company_id' => $companyId,
            'user_id'    => $userId,
        ]);
    }

    public function updateDepartment(Department $department, array $data): bool
    {
        return $this->departmentRepo->update($department, $data);
    }

    public function deleteDepartment(Department $department): bool
    {
        return $this->departmentRepo->delete($department);
    }

    public function findDepartment(int $id): ?Department
    {
        return $this->departmentRepo->findById($id);
    }
}
