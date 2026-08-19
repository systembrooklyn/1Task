<?php

namespace App\Modules\User\Http\Controllers\Auth;

use App\Modules\User\Services\AuthService;
use App\Modules\User\Http\Requests\ForgotPasswordRequest;
use App\Modules\User\Http\Requests\ResetPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    public function sendResetLink(ForgotPasswordRequest $request): JsonResponse
    {
        $sent = $this->authService->sendPasswordResetLink($request->input('email'));
        if ($sent) {
            return response()->json(['message' => 'Password reset link sent successfully!']);
        }
        return response()->json(['message' => 'Failed to send password reset link.'], 400);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $status = $this->authService->resetPassword($request->validated());
        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }
}
