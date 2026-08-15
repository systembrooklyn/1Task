<?php

namespace App\Modules\User\Http\Controllers\Auth;

use App\Modules\User\Services\AuthService;
use App\Modules\User\Http\Requests\ForgotPasswordRequest;
use App\Modules\User\Http\Requests\ResetPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class PasswordResetController extends Controller
{
    public function __construct(protected AuthService $authService) {}
    /**
     * @OA\Post(
     *     path="/api/forgot-password",
     *     summary="Send password reset link to user email",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset link sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password reset link sent successfully!")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Failed to send link"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function sendResetLink(ForgotPasswordRequest $request): JsonResponse
    {
        $sent = $this->authService->sendPasswordResetLink($request->input('email'));
        if ($sent) {
            return response()->json(['message' => 'Password reset link sent successfully!']);
        }
        return response()->json(['message' => 'Failed to send password reset link.'], 400);
    }
    /**
     * @OA\Post(
     *     path="/api/reset-password",
     *     summary="Reset user password using token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password","password_confirmation","token"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="token", type="string", example="sample_reset_token"),
     *             @OA\Property(property="password", type="string", format="password", example="newsecret123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newsecret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password reset successfully.")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Failed to reset password"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $reset = $this->authService->resetPassword($request->validated());
        if ($reset) {
            return response()->json(['message' => 'Password reset successfully.']);
        }
        return response()->json(['message' => 'Failed to reset password.'], 400);
    }
}
