<?php

namespace App\Modules\User\Swagger;

/**
 * @OA\Tag(
 *     name="User Profile",
 *     description="User profile operations and profile picture handling"
 * )
 */
class UserProfileSwagger
{
    /**
     * @OA\Get(
     *     path="/api/userProfile",
     *     summary="Get current user profile data",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="User retrieved successfully")
     * )
     */
    public function index() {}

    /**
     * @OA\Get(
     *     path="/api/userProfile/{id}",
     *     summary="Get specific user profile by ID",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="User retrieved successfully"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function show() {}

    /**
     * @OA\Put(
     *     path="/api/userProfile",
     *     summary="Update current user profile information",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="User data updated successfully")
     * )
     */
    public function update() {}

    /**
     * @OA\Post(
     *     path="/api/user/upload-profile-picture",
     *     summary="Upload profile picture",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"profile_picture"},
     *                 @OA\Property(
     *                     property="profile_picture",
     *                     description="The profile picture file (jpeg, png, jpg, max 2MB)",
     *                     type="string",
     *                     format="binary"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile picture uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Profile picture uploaded successfully."),
     *             @OA\Property(property="url", type="string", example="https://your-live-domain.com"),
     *             @OA\Property(property="file_size_kb", type="number", example=142.5)
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function uploadProfilePicture() {}
}
