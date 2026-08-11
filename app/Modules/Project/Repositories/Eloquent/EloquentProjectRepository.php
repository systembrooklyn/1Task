<?php

namespace App\Modules\Project\Repositories\Eloquent;

use App\Models\Project;
use App\Modules\Project\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentProjectRepository implements ProjectRepositoryInterface
{
    public function create(array $data): Project
    {
        return Project::create($data);
    }

    public function update(Project $project, array $data): bool
    {
        return $project->update($data);
    }

    public function delete(Project $project): bool
    {
        return $project->delete();
    }

    public function findById(int $id, array $with = []): ?Project
    {
        return Project::with($with)->find($id);
    }

    public function getByCompany(int $companyId, array $with = []): Collection
    {
        return Project::where('company_id', $companyId)
            ->with($with)
            ->get();
    }

    public function getByCompanyAndDepartments(int $companyId, array $departmentIds, array $with = []): Collection
    {
        return Project::where('company_id', $companyId)
            ->whereHas('departments', function ($q) use ($departmentIds) {
                $q->whereIn('department_id', $departmentIds);
            })
            ->with($with)
            ->get();
    }
}
