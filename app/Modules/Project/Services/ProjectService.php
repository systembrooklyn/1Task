<?php

namespace App\Modules\Project\Services;

use App\Models\Department;
use App\Models\Project;
use App\Models\ProjectRevision;
use App\Models\User;
use App\Modules\Project\Repositories\Contracts\ProjectRepositoryInterface;
use App\Services\PlanLimitService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class ProjectService
{
    public function __construct(
        protected ProjectRepositoryInterface $projectRepo,
        protected PlanLimitService $planService
    ) {}

    /**
     * Get projects based on user's permissions (owner or department-based).
     */
    public function getProjectsForUser(User $user): Collection
    {
        $companyId = $user->company_id;
        $userDepartmentIds = $user->departments->pluck('id');

        try {
            $this->authorize('viewAllProjects', Project::class);
            $projects = $this->projectRepo->getByCompany($companyId, [
                'company:id,name',
                'departments:id,name',
                'createdBy:id,name,last_name',
                'editedBy:id,name,last_name',
                'leader:id,name,last_name',
            ]);
        } catch (AuthorizationException $e) {
            $this->authorize('viewAny', Project::class);
            $projects = $this->projectRepo->getByCompanyAndDepartments($companyId, $userDepartmentIds->toArray(), [
                'company:id,name',
                'departments:id,name',
                'createdBy:id,name,last_name',
                'editedBy:id,name,last_name',
                'leader:id,name,last_name',
            ]);
        }

        return $projects;
    }

    /**
     * Create a new project.
     */
    public function createProject(array $data, int $companyId, int $userId): Project
    {
        $this->planService->checkFeatureAccess($companyId, 'limit_projects');
        if (!empty($data['leader_id'])) {
            $this->ensureUserInCompany($data['leader_id'], $companyId);
        }

        if (!empty($data['department_ids'])) {
            $this->ensureDepartmentsInCompany($data['department_ids'], $companyId);
        }

        $projectData = [
            'name'        => $data['name'],
            'desc'        => $data['desc'] ?? null,
            'status'      => $data['status'],
            'deadline'    => $data['deadline'] ?? null,
            'start_date'  => $data['start_date'] ?? null,
            'company_id'  => $companyId,
            'created_by'  => $userId,
            'leader_id'   => $data['leader_id'] ?? null,
        ];

        $project = $this->projectRepo->create($projectData);

        if (!empty($data['department_ids'])) {
            $project->departments()->attach($data['department_ids']);
        }

        return $project;
    }

    /**
     * Update an existing project.
     */
    public function updateProject(Project $project, array $data, int $userId): Project
    {
        if (array_key_exists('leader_id', $data) && !empty($data['leader_id'])) {
            $this->ensureUserInCompany($data['leader_id'], $project->company_id);
        }

        if (!empty($data['department_ids'])) {
            $this->ensureDepartmentsInCompany($data['department_ids'], $project->company_id);
        }

        $original = $project->getOriginal();
        $originalDepartmentIds = $project->departments()->pluck('departments.id')->toArray();
        $originalDepartmentNames = Department::whereIn('id', $originalDepartmentIds)->pluck('name', 'id')->toArray();

        $project->name = $data['name'] ?? $project->name;
        $project->desc = $data['desc'] ?? $project->desc;
        $project->status = $data['status'] ?? $project->status;
        $project->deadline = $data['deadline'] ?? $project->deadline;
        $project->start_date = $data['start_date'] ?? $project->start_date;
        $project->leader_id = $data['leader_id'] ?? $project->leader_id;

        if (array_key_exists('department_ids', $data)) {
            $project->departments()->sync($data['department_ids']);
        }

        $project->save();

        $this->createRevisions($project, $original, $originalDepartmentIds, $originalDepartmentNames, $data, $userId);

        return $project;
    }

    /**
     * Toggle project status.
     */
    public function toggleStatus(Project $project): void
    {
        $project->status = !$project->status;
        $project->save();
    }

    /**
     * Delete a project.
     */
    public function deleteProject(Project $project): void
    {
        $this->projectRepo->delete($project);
    }

    /**
     * Get revisions for a project.
     */
    public function getRevisions(int $projectId): array
    {
        $project = $this->projectRepo->findById($projectId, ['revisions.user']);
        if (!$project) {
            return [];
        }

        return $project->revisions->map(function ($revision) {
            return [
                'id'         => $revision->id,
                'field'      => $revision->field,
                'old_value'  => $revision->old_value,
                'new_value'  => $revision->new_value,
                'changed_at' => $revision->created_at,
                'changed_by' => $revision->user ? $revision->user->name : null,
            ];
        })->toArray();
    }

    // ========== Helper Methods ==========

    /**
     * Ensure a user belongs to the given company.
     *
     * @throws \Exception
     */
    protected function ensureUserInCompany(int $userId, int $companyId): void
    {
        $user = User::where('id', $userId)->where('company_id', $companyId)->exists();
        if (!$user) {
            throw new \Exception('The selected leader does not belong to your company.', 422);
        }
    }

    /**
     * Ensure all department IDs belong to the given company.
     *
     * @throws \Exception
     */
    protected function ensureDepartmentsInCompany(array $departmentIds, int $companyId): void
    {
        $count = Department::whereIn('id', $departmentIds)->where('company_id', $companyId)->count();
        if ($count !== count($departmentIds)) {
            throw new \Exception('One or more departments do not belong to your company.', 422);
        }
    }

    /**
     * Create revision entries (exactly as original logic).
     */
    protected function createRevisions(Project $project, array $original, array $originalDepartmentIds, array $originalDepartmentNames, array $data, int $userId): void
    {
        $changes = $project->getChanges();
        $trackableFields = ['name', 'status', 'desc', 'deadline', 'start_date'];

        foreach ($changes as $field => $newValue) {
            if (in_array($field, $trackableFields)) {
                $oldValue = $original[$field] ?? null;
                if ($oldValue !== $newValue) {
                    ProjectRevision::create([
                        'project_id' => $project->id,
                        'user_id'    => $userId,
                        'field'      => $field,
                        'old_value'  => $oldValue,
                        'new_value'  => $newValue,
                        'created_at' => now(),
                    ]);
                }
            }
        }

        if (array_key_exists('department_ids', $data)) {
            $newDepartmentIds = $data['department_ids'] ?? [];
            $newDepartmentNames = Department::whereIn('id', $newDepartmentIds)->pluck('name', 'id');

            $added = array_diff($newDepartmentIds, $originalDepartmentIds);
            foreach ($added as $deptId) {
                ProjectRevision::create([
                    'project_id' => $project->id,
                    'user_id'    => $userId,
                    'field'      => 'departments',
                    'old_value'  => null,
                    'new_value'  => $newDepartmentNames[$deptId] ?? null,
                    'created_at' => now(),
                ]);
            }

            $removed = array_diff($originalDepartmentIds, $newDepartmentIds);
            foreach ($removed as $deptId) {
                ProjectRevision::create([
                    'project_id' => $project->id,
                    'user_id'    => $userId,
                    'field'      => 'departments',
                    'old_value'  => $originalDepartmentNames[$deptId] ?? null,
                    'new_value'  => null,
                    'created_at' => now(),
                ]);
            }
        }
    }

    /**
     * Helper to authorize (used for policy checks).
     */
    protected function authorize(string $ability, $arguments = [])
    {
        return Gate::authorize($ability, $arguments);
    }
}
