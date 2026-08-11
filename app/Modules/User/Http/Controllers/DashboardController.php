<?php

namespace App\Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Http\Requests\DashboardRequest;
use App\Modules\User\Http\Resources\DashboardResource;
use App\Modules\User\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\Access\AuthorizationException;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $dashboardService) {}

    public function getCounts(DashboardRequest $request): JsonResponse
    {
        $date = $request->input('date');
        try {
            $data = $this->dashboardService->getCounts($date);
            return response()->json([
                'message' => 'Dashboard retrieved successfully',
                ...$data
            ]);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }
}
