<?php

namespace App\Modules\User\Swagger;

/**
 * @OA\Tag(
 *     name="Invitations",
 *     description="Operations for inviting users and completing invitations"
 * )
 */
class InvitationSwagger
{
    /**
     * @OA\Post(
     *     path="/api/invite",
     *     summary="Send an invitation to a user",
     *     tags={"Invitations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="invitee@example.com")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Invitation sent successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function invite() {}

    /**
     * @OA\Get(
     *     path="/api/invitation/{token}",
     *     summary="Validate invitation token",
     *     tags={"Invitations"},
     *     @OA\Parameter(name="token", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Invitation is valid"),
     *     @OA\Response(response=400, description="Invalid or expired token")
     * )
     */
    public function registerUsingInvitation() {}

    /**
     * @OA\Post(
     *     path="/api/invitation/{token}/register",
     *     summary="Complete registration via invitation token",
     *     tags={"Invitations"},
     *     @OA\Parameter(name="token", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(required=true, @OA\JsonContent()),
     *     @OA\Response(response=201, description="User registered successfully"),
     *     @OA\Response(response=400, description="Invalid or expired token")
     * )
     */
    public function completeRegistration() {}

    /**
     * @OA\Post(
     *     path="/api/registerViaInvitation",
     *     summary="Register user via invitation info",
     *     tags={"Invitations"},
     *     @OA\RequestBody(required=true, @OA\JsonContent()),
     *     @OA\Response(response=201, description="Registration successful"),
     *     @OA\Response(response=400, description="Invalid or expired invitation")
     * )
     */
    public function registerViaInvitation() {}

    /**
     * @OA\Get(
     *     path="/api/getInvitations",
     *     summary="Get pending invitations for the company",
     *     tags={"Invitations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Invitations retrieved"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function getInvitations() {}
}
