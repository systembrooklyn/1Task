<?php

namespace App\Modules\User\Http\Controllers;

use App\Modules\User\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function showAuthenticated(): JsonResponse
    {
        $user = auth()->user()->load(['company', 'departments', 'roles.permissions']);
        // Build exactly the same response as original closure
        $response = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'ppUrl' => $user->profile?->ppUrl ?? null,
                'company' => [
                    'id' => $user->company->id,
                    'name' => $user->company->name,
                ],
                'departments' => $user->departments->map(fn($dept) => [
                    'id' => $dept->id,
                    'name' => $dept->name,
                ]),
            ],
            'roles' => $user->roles->map(fn($role) => [
                'id' => $role->id,
                'name' => $role->name,
            ]),
            'permissions' => $user->roles->flatMap(fn($role) => $role->permissions->map(fn($perm) => [
                'id' => $perm->id,
                'name' => $perm->name,
            ]))->unique('id')->values(),
            'token' => request()->bearerToken(),
        ];
        return response()->json($response);
    }

    public function getCompanyUsers(): JsonResponse
    {
        $user = auth()->user();
        $companyId = $user->company_id;
        $users = $this->userService->getCompanyUsers($companyId, ['departments', 'roles', 'profile']);

        $usersData = $users->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'last_name' => $u->last_name ?? null,
                'email' => $u->email,
                'ppUrl' => $u->profile?->ppUrl ?? null,
                'position' => $u->profile?->position ?? null,
                'fireToken' => $u->fireToken,
                'departments' => $u->departments->pluck('name'),
                'roles' => $u->roles->pluck('name'),
                'departments_ids' => $u->departments->map(fn($d) => ['id' => $d->id, 'name' => $d->name]),
                'roles_ids' => $u->roles->map(fn($r) => ['id' => $r->id, 'name' => $r->name]),
            ];
        });

        return response()->json(['users' => $usersData], 200);
    }
}
