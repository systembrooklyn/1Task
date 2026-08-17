<?php

namespace App\Modules\DigitalCard\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\DigitalCard\Http\Requests\RegisterRequest;
use App\Modules\DigitalCard\Http\Requests\VerifyCodeRequest;
use App\Modules\DigitalCard\Http\Requests\LoginRequest;
use App\Modules\DigitalCard\Services\AuthServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->register($request->validated());
            return response()->json([
                'message' => 'User registered successfully. Please check your email for the verification code.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send verification email.'], 500);
        }
    }

    public function verifyCode(VerifyCodeRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->verifyCode($request->email, $request->verification_code);
            return response()->json([
                'message' => 'Email verified successfully.',
                'user' => $user,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->errors()['verification_code'][0] ?? 'Invalid verification code.'], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => 'User not found.'], 404);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->email, $request->password);
            return response()->json([
                'message' => 'Login successful.',
                'token' => $result['token'],
            ], 200);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            if (isset($errors['email']) && str_contains($errors['email'][0], 'Please verify your email')) {
                return response()->json(['message' => 'Please verify your email first.'], 403);
            }
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }
    }
}
