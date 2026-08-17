<?php

namespace App\Modules\RolePermission\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Permissions",
 *     description="Permission management endpoints"
 * )
 */
class PermissionSwagger
{
    /**
     * @OA\Get(
     *     path="/api/permissions",
     *     summary="Get all permissions",
     *     tags={"Permissions"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of permissions",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="manage_users"),
     *                 @OA\Property(property="guard_name", type="string", example="web"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
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
     * @OA\Get(
     *     path="/api/permissions/{id}",
     *     summary="Get a specific permission by ID",
     *     tags={"Permissions"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission details",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="manage_users"),
     *             @OA\Property(property="guard_name", type="string", example="web"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Permission not found"
     *     )
     * )
     */
    public function show() {}
}
