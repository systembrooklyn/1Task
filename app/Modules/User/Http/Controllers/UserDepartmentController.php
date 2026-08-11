<?php

namespace App\Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\User\Http\Requests\AssignDepartmentsRequest;
use App\Modules\User\Http\Requests\UnassignDepartmentRequest;
use App\Modules\User\Http\Requests\AssignManagerRequest;
use App\Modules\User\Services\UserDepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserDepartmentController extends Controller
{
    public function __construct(protected UserDepartmentService $deptService) {}

    public function assignDepartments(AssignDepartmentsRequest $request, int $userId): JsonResponse
    {
        $loggedInUser = Auth::user();
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if ($user->company_id !== $loggedInUser->company_id) {
            return response()->json(['message' => 'The user and the logged-in user are not in the same company.'], 400);
        }

        $success = $this->deptService->assignDepartments($userId, $request->input('department_ids'));
        if (!$success) {
            return response()->json(['message' => 'One or more departments do not belong to the user\'s company.'], 400);
        }

        return response()->json(['message' => 'Departments assigned successfully.']);
    }

    public function unassignDepartment(UnassignDepartmentRequest $request, int $userId): JsonResponse
    {
        $success = $this->deptService->unassignDepartment($userId, $request->input('department_id'));
        if (!$success) {
            return response()->json(['message' => 'User is not assigned to this department or department/user not found.'], 400);
        }

        return response()->json(['message' => 'User successfully unassigned from the department.']);
    }

    public function assignManagerToDepartment(AssignManagerRequest $request): JsonResponse
    {
        $success = $this->deptService->assignManager(
            $request->input('department_id'),
            $request->input('user_id')
        );

        if (!$success) {
            return response()->json(['message' => 'Manager assignment failed. Ensure user and department are in the same company.'], 400);
        }

        return response()->json(['message' => 'Manager assigned successfully.']);
    }

    public function getUsersInDepartment(): JsonResponse
    {
        $user = Auth::user();
        $userDepartments = $user->departments;
        if ($userDepartments->isEmpty()) {
            return response()->json(['message' => 'User is not assigned to any department.'], 400);
        }

        $department = $userDepartments->first();
        $users = $this->deptService->getUsersInDepartment($department->id);

        return response()->json(['users' => $users]);
    }

    public function getUsersFireTokensInAnyDepartment(int $deptId): JsonResponse
    {
        $userId = Auth::id();
        $department = \App\Models\Department::find($deptId);
        if (!$department) {
            return response()->json(['message' => 'Department not found.'], 404);
        }

        $user = \App\Models\User::find($userId);
        if (!$user || $user->company_id !== $department->company_id) {
            return response()->json(['message' => 'Department not found'], 404);
        }

        $fireTokens = $this->deptService->getFireTokensForDepartment($deptId);
        return response()->json([
            'message' => 'Department FireTokens Retrieved Successfully',
            'data'    => $fireTokens,
        ]);
    }
}
