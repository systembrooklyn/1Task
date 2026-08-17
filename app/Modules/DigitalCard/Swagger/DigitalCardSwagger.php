<?php

namespace App\Modules\DigitalCard\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Digital Card",
 *     description="Public digital card viewing and management"
 * )
 */
class DigitalCardSwagger
{
    /**
     * @OA\Get(
     *     path="/api/digital-card/user",
     *     summary="Get authenticated user's digital card",
     *     tags={"Digital Card"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User details with social links and phones",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="is_verified", type="boolean", example=true),
     *                 @OA\Property(property="email_verified_at", type="string", format="date-time"),
     *                 @OA\Property(property="user_code", type="string", example="ABC123"),
     *                 @OA\Property(property="title", type="string", example="Software Engineer"),
     *                 @OA\Property(property="desc", type="string", example="Laravel developer"),
     *                 @OA\Property(property="profile_pic_url", type="string", example="https://example.com/photo.jpg"),
     *                 @OA\Property(property="back_pic_link", type="string", example="https://example.com/background.jpg"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(
     *                     property="social_links",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="user_id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Twitter"),
     *                         @OA\Property(property="icon", type="string", example="twitter-icon"),
     *                         @OA\Property(property="link", type="string", example="https://twitter.com/johndoe"),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="phones",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="user_id", type="integer", example=1),
     *                         @OA\Property(property="phone", type="string", example="+1234567890"),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function getDigitalCard() {}

    /**
     * @OA\Put(
     *     path="/api/digital-card/update",
     *     summary="Update authenticated user's digital card",
     *     tags={"Digital Card"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Senior Developer"),
     *             @OA\Property(property="desc", type="string", example="Experienced in Laravel & Vue"),
     *             @OA\Property(property="profile_pic_url", type="string", example="https://example.com/newphoto.jpg"),
     *             @OA\Property(property="back_pic_link", type="string", example="https://example.com/newbg.jpg"),
     *             @OA\Property(
     *                 property="social_links",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="name", type="string", example="LinkedIn"),
     *                     @OA\Property(property="icon", type="string", example="linkedin-icon"),
     *                     @OA\Property(property="link", type="string", example="https://linkedin.com/in/johndoe")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="phones",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="phone", type="string", example="+1987654321")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Digital card updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Digital card updated successfully."),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="title", type="string", nullable=true),
     *                 @OA\Property(property="desc", type="string", nullable=true),
     *                 @OA\Property(property="profile_pic_url", type="string", nullable=true),
     *                 @OA\Property(property="back_pic_link", type="string", nullable=true),
     *                 @OA\Property(
     *                     property="social_links",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="icon", type="string"),
     *                         @OA\Property(property="link", type="string")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="phones",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="phone", type="string")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function updateDigitalCard() {}

    /**
     * @OA\Get(
     *     path="/api/digital-card/view/{user_code}",
     *     summary="View a public digital card by user code",
     *     tags={"Digital Card"},
     *     @OA\Parameter(
     *         name="user_code",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Unique user code for the digital card"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Digital card retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Digital card retrieved successfully."),
     *             @OA\Property(
     *                 property="digital_card",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="user_code", type="string", example="ABC123"),
     *                 @OA\Property(property="title", type="string", example="Software Engineer"),
     *                 @OA\Property(property="desc", type="string", example="Laravel developer"),
     *                 @OA\Property(property="profile_pic_url", type="string", nullable=true),
     *                 @OA\Property(property="back_pic_link", type="string", nullable=true),
     *                 @OA\Property(
     *                     property="social_links",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="icon", type="string"),
     *                         @OA\Property(property="link", type="string")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="phones",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="phone", type="string")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Digital card not found"
     *     )
     * )
     */
    public function viewDigitalCard() {}
}