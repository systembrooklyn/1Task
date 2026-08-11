<?php

namespace App\Modules\Project\Repositories\Contracts;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

interface ProjectRepositoryInterface
{
    public function create(array $data): Project;
    public function update(Project $project, array $data): bool;
    public function delete(Project $project): bool;
    public function findById(int $id, array $with = []): ?Project;
    public function getByCompany(int $companyId, array $with = []): Collection;
    public function getByCompanyAndDepartments(int $companyId, array $departmentIds, array $with = []): Collection;
}