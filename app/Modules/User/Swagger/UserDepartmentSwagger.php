<?php

namespace App\Modules\User\Swagger;

/**
 * @OA\Tag(
 *     name="User Department",
 *     description="Manage user department assignments, managers, and fire tokens"
 * )
 */
class UserDepartmentSwagger
{
    /**
     * @OA\Post(
     *     path="/api/users/{userId}/assign-departments",
     *     summary="Assign departments to a user",
     *     tags={"User Department"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="userId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"department_ids"},
     *             @OA\Property(property="department_ids", type="array", @OA\Items(type="integer"), example={1, 2})
     *         )
     *     ),
     *     @OA\Response(response=200, description="Departments assigned successfully"),
     *     @OA\Response(response=400, description="Validation or company error"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function assignDepartments() {}

    /**
     * @OA\Post(
     *     path="/api/unassign-department/{userId}",
     *     summary="Unassign user from a department",
     *     tags={"User Department"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="userId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"department_id"},
     *             @OA\Property(property="department_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Unassigned successfully"),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function unassignDepartment() {}

    /**
     * @OA\Put(
     *     path="/api/department/assign-manager",
     *     summary="Assign manager to a department",
     *     tags={"User Department"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"department_id","user_id"},
     *             @OA\Property(property="department_id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Manager assigned successfully"),
     *     @OA\Response(response=400, description="Assignment failed")
     * )
     */
    public function assignManagerToDepartment() {}

    /**
     * @OA\Get(
     *     path="/api/departments-users",
     *     summary="Get users in the authenticated user's department",
     *     tags={"User Department"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Users retrieved"),
     *     @OA\Response(response=400, description="User not in any department")
     * )
     */
    public function getUsersInDepartment() {}

    /**
     * @OA\Get(
     *     path="/api/deptUsersFireToken/{id}",
     *     summary="Get Firebase tokens for users in a department",
     *     tags={"User Department"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="FireTokens Retrieved Successfully"),
     *     @OA\Response(response=404, description="Department not found")
     * )
     */
    public function getUsersFireTokensInAnyDepartment() {}
}
