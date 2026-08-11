<?php

namespace App\Modules\User\Http\Controllers;

use App\Modules\User\Services\UserService;
use App\Modules\User\Http\Resources\AuthenticatedUserResource;
use App\Modules\User\Http\Resources\CompanyUserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class UserController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function showAuthenticated(): JsonResponse
    {
        $user = auth()->user()->load(['company', 'departments', 'roles.permissions']);

        $resource = new AuthenticatedUserResource($user);
        $data = $resource->toArray(request());
        $data['token'] = request()->bearerToken();

        return response()->json($data);
    }

    public function getCompanyUsers(): JsonResponse
    {
        $user = auth()->user();
        $companyId = $user->company_id;
        $users = $this->userService->getCompanyUsers($companyId, ['departments', 'roles', 'profile']);

        return response()->json([
            'users' => CompanyUserResource::collection($users),
        ], 200);
    }
}
