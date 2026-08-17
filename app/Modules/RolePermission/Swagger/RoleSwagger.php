<?php

namespace App\Modules\RolePermission\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Roles",
 *     description="Role management endpoints"
 * )
 */
class RoleSwagger
{
    /**
     * @OA\Get(
     *     path="/api/roles",
     *     summary="Get all roles for the authenticated user's company",
     *     tags={"Roles"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of roles with their permissions",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="role_id", type="integer", example=1),
     *                 @OA\Property(property="role_name", type="string", example="Admin"),
     *                 @OA\Property(
     *                     property="permissions",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="permission_id", type="integer", example=1),
     *                         @OA\Property(property="permission_name", type="string", example="manage_users")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function index() {}

    /**
     * @OA\Post(
     *     path="/api/roles",
     *     summary="Create a new role",
     *     tags={"Roles"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Manager"),
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(type="integer", example=1),
     *                 description="Array of permission IDs"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Role created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Role created successfully"),
     *             @OA\Property(
     *                 property="role",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Manager"),
     *                 @OA\Property(property="company_id", type="integer", example=5),
     *                 @OA\Property(property="is_deleted", type="boolean", example=false),
     *                 @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(
     *                     property="permissions",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="manage_users"),
     *                         @OA\Property(property="guard_name", type="string", example="web")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error (e.g., duplicate role name)"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized (plan limit or policy)"
     *     )
     * )
     */
    public function store() {}

    /**
     * @OA\Get(
     *     path="/api/roles/{id}",
     *     summary="Get a specific role by ID",
     *     tags={"Roles"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role details",
     *         @OA\JsonContent(
     *             @OA\Property(property="role_id", type="integer", example=1),
     *             @OA\Property(property="role_name", type="string", example="Admin"),
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="permission_id", type="integer", example=1),
     *                     @OA\Property(property="permission_name", type="string", example="manage_users")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found or deleted"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Role is deleted (ResourceDeletedException)"
     *     )
     * )
     */
    public function show() {}

    /**
     * @OA\Put(
     *     path="/api/roles/{id}",
     *     summary="Update an existing role",
     *     tags={"Roles"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "permissions"},
     *             @OA\Property(property="name", type="string", example="Updated Manager"),
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(type="integer", example=2)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Role updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found"
     *     )
     * )
     */
    public function update() {}

    /**
     * @OA\Delete(
     *     path="/api/roles/{id}",
     *     summary="Soft delete a role",
     *     tags={"Roles"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Role deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found"
     *     )
     * )
     */
    public function destroy() {}

    /**
     * @OA\Post(
     *     path="/api/roles/assign-permissions",
     *     summary="Assign permissions to a role",
     *     tags={"Roles"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"role_id", "permissions"},
     *             @OA\Property(property="role_id", type="integer", example=1),
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(type="integer", example=2)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permissions assigned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Permissions assigned successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Role is deleted"
     *     )
     * )
     */
    public function assignPermissions() {}

    /**
     * @OA\Get(
     *     path="/api/roles/get-permissions/{id}",
     *     summary="Get permissions of a specific role",
     *     tags={"Roles"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permissions of the role",
     *         @OA\JsonContent(
     *             @OA\Property(property="role", type="string", example="Admin"),
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="manage_users"),
     *                     @OA\Property(property="guard_name", type="string", example="web"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found or deleted"
     *     )
     * )
     */
    public function getRolePermissions() {}

    /**
     * @OA\Post(
     *     path="/api/roles/remove-permissions",
     *     summary="Remove permissions from a role",
     *     tags={"Roles"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"role_id", "permission_ids"},
     *             @OA\Property(property="role_id", type="integer", example=1),
     *             @OA\Property(
     *                 property="permission_ids",
     *                 type="array",
     *                 @OA\Items(type="integer", example=2)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permissions removed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Permissions removed from role successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="User and role must belong to same company"
     *     )
     * )
     */
    public function removePermissions() {}
}
