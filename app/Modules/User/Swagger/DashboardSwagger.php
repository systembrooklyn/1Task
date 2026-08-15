<?php

namespace App\Modules\User\Swagger;

/**
 * @OA\Tag(
 *     name="Dashboard",
 *     description="Dashboard metrics and counts"
 * )
 */
class DashboardSwagger
{
    /**
     * @OA\Get(
     *     path="/api/dashboard",
     *     summary="Get dashboard counts",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Optional date filter (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2026-08-15")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dashboard retrieved successfully")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function getCounts()
    {
        // Virtual method purely for holding the docblock definition
    }
}
