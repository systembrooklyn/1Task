<?php

namespace App\Modules\Plan\Swagger;

/**
 * @OA\Tag(
 *     name="Plans",
 *     description="Endpoints for managing and retrieving subscription plans"
 * )
 */
class PlanSwagger
{
    /**
     * @OA\Get(
     *     path="/api/plans/all",
     *     summary="Get all public plans",
     *     tags={"Plans"},
     *     @OA\Response(
     *         response=200,
     *         description="Plans retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Plans retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Basic Plan"),
     *                     @OA\Property(property="price", type="number", format="float", example=19.99),
     *                     @OA\Property(property="currency", type="string", example="USD"),
     *                     @OA\Property(property="is_active", type="boolean", example=true)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function allPlans() {}

    /**
     * @OA\Get(
     *     path="/api/plans",
     *     summary="Get plans for authenticated user",
     *     tags={"Plans"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Plans retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Plans retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index() {}

    /**
     * @OA\Get(
     *     path="/api/plans/adminPlans",
     *     summary="Get admin plans list",
     *     tags={"Plans"},
     *     security={{"AdminToken": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Plans retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Plans retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized / Invalid Token")
     * )
     */
    public function adminPlans() {}

    /**
     * @OA\Post(
     *     path="/api/plans/adminPlans",
     *     summary="Create a new plan (Admin)",
     *     tags={"Plans"},
     *     security={{"AdminToken": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "price", "currency"},
     *             @OA\Property(property="name", type="string", example="Pro Plan"),
     *             @OA\Property(property="price", type="number", format="float", example=49.99),
     *             @OA\Property(property="currency", type="string", example="USD"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Plan created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Plan created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store() {}

    /**
     * @OA\Post(
     *     path="/api/plans/{plan}/features",
     *     summary="Attach features to a plan (Admin)",
     *     tags={"Plans"},
     *     security={{"AdminToken": {}}},
     *     @OA\Parameter(
     *         name="plan",
     *         in="path",
     *         required=true,
     *         description="ID of the plan",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"features"},
     *             @OA\Property(
     *                 property="features",
     *                 type="array",
     *                 @OA\Items(
     *                     required={"id", "value"},
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="value", type="integer", example=100),
     *                     @OA\Property(property="resettable", type="boolean", example=true)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Features updated successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Features updated successfully."),
     *             @OA\Property(property="plan", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Plan not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function attachFeatures() {}
}