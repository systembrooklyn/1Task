<?php

namespace App\Modules\Plan\Swagger;

/**
 * @OA\Tag(
 *     name="Features",
 *     description="Endpoints for feature management"
 * )
 */
class FeatureSwagger
{
    /**
     * @OA\Get(
     *     path="/api/features",
     *     summary="Get all features",
     *     tags={"Features"},
     *     @OA\Response(
     *         response=200,
     *         description="Features retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Features retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="API Requests"),
     *                     @OA\Property(property="slug", type="string", example="api-requests"),
     *                     @OA\Property(property="unit", type="string", enum={"count", "kb", "mb"}, example="count"),
     *                     @OA\Property(property="reset_frequency", type="string", enum={"daily", "weekly", "monthly"}, nullable=true, example="monthly")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index() {}
}
