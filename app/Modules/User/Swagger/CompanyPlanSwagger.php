<?php

namespace App\Modules\User\Swagger;

/**
 * @OA\Tag(
 *     name="Company Plan",
 *     description="Manage and inspect company subscriptions and plans"
 * )
 */
class CompanyPlanSwagger
{
    /**
     * @OA\Get(
     *     path="/api/getCompanyPlanDetails",
     *     summary="Get company plan details",
     *     tags={"Company Plan"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Plan details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="check plan details retrieved successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function getCompanyPlanDetails()
    {
        // Virtual method for Swagger docblock
    }
}
