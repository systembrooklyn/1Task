<?php

namespace App\Modules\DigitalCard\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Digital Card Auth",
 *     description="Authentication for digital card users"
 * )
 */
class AuthSwagger
{
    /**
     * @OA\Post(
     *     path="/api/digital-card/register",
     *     summary="Register a new digital card user",
     *     tags={"Digital Card Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User registered successfully. Please check your email for the verification code.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to send verification email"
     *     )
     * )
     */
    public function register() {}

    /**
     * @OA\Post(
     *     path="/api/digital-card/verify-code",
     *     summary="Verify email with code",
     *     tags={"Digital Card Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "verification_code"},
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="verification_code", type="string", example="ABC123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email verified successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Email verified successfully."),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="is_verified", type="boolean", example=true),
     *                 @OA\Property(property="email_verified_at", type="string", format="date-time"),
     *                 @OA\Property(property="user_code", type="string", example="ABC123"),
     *                 @OA\Property(property="title", type="string", nullable=true),
     *                 @OA\Property(property="desc", type="string", nullable=true),
     *                 @OA\Property(property="profile_pic_url", type="string", nullable=true),
     *                 @OA\Property(property="back_pic_link", type="string", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid verification code"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function verifyCode() {}

    /**
     * @OA\Post(
     *     path="/api/digital-card/login",
     *     summary="Login a digital card user",
     *     tags={"Digital Card Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Login successful."),
     *             @OA\Property(property="token", type="string", example="1|abc123...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Email not verified"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function login() {}
}