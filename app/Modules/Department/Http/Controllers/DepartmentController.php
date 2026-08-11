<?php

namespace App\Modules\Department\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Department\Http\Requests\StoreDepartmentRequest;
use App\Modules\Department\Http\Requests\UpdateDepartmentRequest;
use App\Modules\Department\Services\DepartmentService;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    public function __construct(protected DepartmentService $departmentService) {}

    /**
     * GET /departments – List departments for the authenticated user's company.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $this->authorize('viewAny', Department::class);

        if (!$user->company_id) {
            return response()->json(['message' => 'You must be associated with a company to view departments.'], 403);
        }

        $departments = $this->departmentService->getCompanyDepartments($user->company_id);
        return response()->json(['Departments' => $departments], 200);
    }

    /**
     * POST /departments – Create a new department (automatically uses the user's company).
     */
    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $user = Auth::user();
        $this->authorize('create', Department::class);

        if (!$user->company_id) {
            return response()->json(['message' => 'You must be associated with a company to create a department.'], 403);
        }

        try {
            $department = $this->departmentService->createDepartment(
                $request->validated(),
                $user->company_id,
                $user->id
            );
        } catch (\Exception $e) {
            if ($e->getCode() === 400) {
                return response()->json(['message' => $e->getMessage()], 400);
            }
            throw $e;
        }

        $department->makeHidden(['created_at', 'updated_at']);
        return response()->json(['Department' => $department], 201);
    }

    /**
     * GET /departments/{id} – Show a single department (must belong to the user's company).
     */
    public function show(int $id): JsonResponse
    {
        $user = Auth::user();

        // Ensure department belongs to the user's company
        $department = Department::where('company_id', $user->company_id)->find($id);

        if (!$department) {
            return response()->json(['message' => 'Department not found.'], 404);
        }

        $this->authorize('view', $department);
        return response()->json(['Department' => $department], 200);
    }

    /**
     * PUT /departments/{id} – Update a department (must belong to the user's company).
     */
    public function update(UpdateDepartmentRequest $request, int $id): JsonResponse
    {
        $user = Auth::user();

        if (!$user->company_id) {
            return response()->json(['message' => 'You must be associated with a company to update a department.'], 403);
        }

        $department = Department::where('company_id', $user->company_id)
            ->where('id', $id)
            ->first();

        if (!$department) {
            return response()->json(['message' => 'Department not found or does not belong to your company.'], 404);
        }

        $this->authorize('update', $department);
        $this->departmentService->updateDepartment($department, $request->validated());

        return response()->json(['Department' => $department], 200);
    }

    /**
     * DELETE /departments/{id} – Delete a department (must belong to the user's company).
     */
    public function destroy(int $id): JsonResponse
    {
        $user = Auth::user();

        if (!$user->company_id) {
            return response()->json(['message' => 'You must be associated with a company to delete a department.'], 403);
        }

        $department = Department::where('company_id', $user->company_id)
            ->where('id', $id)
            ->first();

        if (!$department) {
            return response()->json(['message' => 'Department not found or does not belong to your company.'], 404);
        }

        $this->authorize('delete', $department);
        $this->departmentService->deleteDepartment($department);

        return response()->json(['message' => 'Department deleted successfully.'], 200);
    }
}
