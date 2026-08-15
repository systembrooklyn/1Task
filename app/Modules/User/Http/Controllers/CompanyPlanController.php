<?php

namespace App\Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Services\CompanyPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CompanyPlanController extends Controller
{
    public function __construct(protected CompanyPlanService $planService) {}

    public function getCompanyPlanDetails(): JsonResponse
    {
        $user = Auth::user();

        $data = $this->planService->getCompanyPlanDetails($user);

        return response()->json([
            'message' => 'check plan details retrieved successfully',
            'data'    => $data,
        ], 200);
    }
}
