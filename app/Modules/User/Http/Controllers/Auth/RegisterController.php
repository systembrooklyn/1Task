<?php

namespace App\Modules\User\Http\Controllers\Auth;

use App\Modules\User\Http\Requests\CheckEmailRequest;
use App\Modules\User\Http\Requests\RegisterRequest;
use App\Modules\User\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class RegisterController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());
        return response()->json([
            'user' => $result['user'],
            'token' => $result['token'],
        ]);
    }

    public function checkEmail(CheckEmailRequest $request): JsonResponse
    {
        $exists = $this->authService->checkEmailExists($request->input('email'));
        return response()->json(['exists' => $exists]);
    }
}
