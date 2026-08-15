<?php

namespace App\Modules\User\Swagger;

/**
 * @OA\Tag(
 *     name="User Management",
 *     description="Edit, delete, tokens, and role assignments for users"
 * )
 */
class UserManagementSwagger
{
    /**
     * @OA\Post(
     *     path="/api/edit-user/{id}",
     *     summary="Edit user details",
     *     tags={"User Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent()),
     *     @OA\Response(response=200, description="User name changed successfully"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function edit() {}

    /**
     * @OA\Delete(
     *     path="/api/delete-user/{id}",
     *     summary="Delete a user",
     *     tags={"User Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Deleted successfully"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function delete() {}

    /**
     * @OA\Post(
     *     path="/api/fireToken",
     *     summary="Update Firebase token for authenticated user",
     *     tags={"User Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent()),
     *     @OA\Response(response=200, description="Token updated successfully")
     * )
     */
    public function updateFireToken() {}

    /**
     * @OA\Post(
     *     path="/api/users/assign-role",
     *     summary="Assign roles to a user",
     *     tags={"User Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent()),
     *     @OA\Response(response=200, description="Roles assigned successfully")
     * )
     */
    public function assignRole() {}

    /**
     * @OA\Post(
     *     path="/api/unassign-role",
     *     summary="Unassign roles from a user",
     *     tags={"User Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent()),
     *     @OA\Response(response=200, description="Roles unassigned successfully")
     * )
     */
    public function unassignRole() {}
}
