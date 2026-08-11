<?php

namespace App\Modules\Project\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Project\Http\Requests\StoreProjectRequest;
use App\Modules\Project\Http\Requests\UpdateProjectRequest;
use App\Modules\Project\Http\Requests\UpdateStatusRequest;
use App\Modules\Project\Services\ProjectService;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;

class ProjectController extends Controller
{
    public function __construct(protected ProjectService $projectService) {}

    /**
     * GET /projects – List projects based on permissions.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $projects = $this->projectService->getProjectsForUser($user);
        $projects->each(function ($project) {
            $project->setAppends([
                'company_name',
                'department_name',
                'created_by_name',
                'edited_by_name',
                'leader_name',
            ]);
            $project->departments->each->makeHidden('pivot');
            if ($project->departments->isEmpty()) {
                $project->department_name = 'No Department Assigned';
            }
            $project->makeHidden([
                'company_id',
                'dept_id',
                'created_by',
                'edited_by',
                'editedBy',
                'createdBy',
                'company',
                'leader',
            ]);
        });

        return response()->json($projects);
    }

    /**
     * POST /projects – Create a new project.
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $user = Auth::user();
        $this->authorize('create', Project::class);

        try {
            $project = $this->projectService->createProject(
                $request->validated(),
                $user->company_id,
                $user->id
            );
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Project created successfully'], 201);
    }

    /**
     * GET /projects/{id} – Show a single project.
     */
    public function show(int $id): JsonResponse
    {
        $user = Auth::user();

        $project = Project::with([
            'company:id,name',
            'departments:id,name',
            'createdBy:id,name,last_name',
            'editedBy:id,name,last_name',
            'leader:id,name,last_name',
        ])->find($id);

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        if ($project->company_id !== $user->company_id) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        $this->authorize('view', $project);

        $project->setAppends([
            'company_name',
            'department_name',
            'created_by_name',
            'edited_by_name',
            'leader_name',
        ]);
        if ($project->departments->isEmpty()) {
            $project->department_name = 'No Department Assigned';
        }
        $project->makeHidden([
            'company_id',
            'dept_id',
            'created_by',
            'edited_by',
            'editedBy',
            'createdBy',
            'company',
            'departments',
            'leader',
        ]);

        return response()->json($project);
    }

    /**
     * PUT /projects/{id} – Update a project.
     */
    public function update(UpdateProjectRequest $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        if ($project->company_id !== $user->company_id) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        $this->authorize('update', $project);

        try {
            $this->projectService->updateProject($project, $request->validated(), $user->id);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Project updated successfully']);
    }

    /**
     * DELETE /projects/{id} – Delete a project.
     */
    public function destroy(int $id): JsonResponse
    {
        $user = Auth::user();
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        if ($project->company_id !== $user->company_id) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        $this->authorize('delete', $project);
        $this->projectService->deleteProject($project);

        return response()->json(['message' => 'Project deleted successfully']);
    }

    /**
     * POST /projects/{id}/status – Toggle project status.
     */
    public function updatestatus(UpdateStatusRequest $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        if ($project->company_id !== $user->company_id) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        $this->authorize('update', $project);
        $this->projectService->toggleStatus($project);

        if ($request->has('department_id')) {
            $project->departments()->sync([$request->department_id]);
        }

        return response()->json(['message' => 'Project status toggled']);
    }

    /**
     * GET /projects/{id}/revisions – Get revision history.
     */
    public function getRevisions(int $id): JsonResponse
    {
        $user = Auth::user();
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        if ($project->company_id !== $user->company_id) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        $revisions = $this->projectService->getRevisions($id);

        return response()->json([
            'project_id' => $id,
            'revisions'  => $revisions,
        ], 200);
    }
}
