<?php

namespace App\Modules\User\Http\Controllers\Auth;

use App\Modules\User\Http\Requests\LoginRequest;
use App\Modules\User\Services\AuthService;
use App\Exceptions\ResourceDeletedException;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class LoginController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $result = $this->authService->login($validated['email'], $validated['password']);

        if (!$result) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
                'errors' => ['email' => ['The provided email or password is incorrect.']]
            ], 401);
        }

        $user = $result['user'];
        if ($user->is_deleted) {
            throw new ResourceDeletedException(
                'This user account has been deleted. Please contact support.',
                'User Deleted'
            );
        }

        $user->load(['company', 'departments', 'roles.permissions']);

        $user->makeHidden(['created_at', 'updated_at', 'email_verified_at', 'company_id']);
        $user->company?->makeHidden(['created_at', 'updated_at']);
        $user->departments->each(fn($d) => $d->makeHidden(['created_at', 'updated_at', 'company_id', 'pivot']));
        $user->roles->each(function ($role) {
            $role->makeHidden(['created_at', 'updated_at', 'company_id', 'guard_name', 'pivot']);
            $role->permissions->each(fn($p) => $p->makeHidden(['created_at', 'updated_at', 'guard_name', 'pivot']));
        });

        return response()->json([
            'user' => $user,
            'token' => $result['token'],
        ]);
    }
}
