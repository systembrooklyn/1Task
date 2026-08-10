<?php

namespace App\Modules\User\Http\Controllers\Auth;

use App\Modules\User\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class LogoutController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    public function logout(): JsonResponse
    {
        $this->authService->logout(auth()->user());
        return response()->json(['message' => 'You are logged out']);
    }
}
