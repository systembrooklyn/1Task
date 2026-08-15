<?php

namespace App\Modules\User\Swagger;

/**
 * @OA\Tag(
 *     name="User",
 *     description="Authenticated user operations"
 * )
 */
class UserSwagger
{
    /**
     * @OA\Get(
     *     path="/api/user",
     *     summary="Get logged-in user details",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function showAuthenticated() {}

    /**
     * @OA\Get(
     *     path="/api/company-users",
     *     summary="Get list of company users",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function getCompanyUsers() {}
}
