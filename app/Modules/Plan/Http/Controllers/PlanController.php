<?php

namespace App\Modules\Plan\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Plan\Http\Requests\StorePlanRequest;
use App\Modules\Plan\Http\Requests\AttachFeaturesRequest;
use App\Modules\Plan\Services\PlanService;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PlanController extends Controller
{
    public function __construct(protected PlanService $planService) {}

    public function allPlans(): JsonResponse
    {
        $data = $this->planService->getAllPlans();
        return response()->json([
            'message' => 'Plans retrieved successfully',
            'data' => $data
        ], 200);
    }

    public function index(): JsonResponse
    {
        $user = Auth::user();
        $data = $this->planService->getPlansForUser($user);
        return response()->json([
            'message' => 'Plans retrieved successfully',
            'data' => $data
        ], 200);
    }

    public function adminPlans(): JsonResponse
    {
        $data = $this->planService->getAdminPlans();
        return response()->json([
            'message' => 'Plans retrieved successfully',
            'data' => $data
        ], 200);
    }

    public function store(StorePlanRequest $request): JsonResponse
    {
        $plan = $this->planService->createPlan($request->validated());
        return response()->json([
            'message' => 'Plan created successfully',
            'data' => $plan
        ], 201);
    }

    public function attachFeatures(AttachFeaturesRequest $request, int $planId): JsonResponse
    {
        $plan = Plan::findOrFail($planId);

        $this->planService->attachFeatures($plan, $request->input('features'));

        return response()->json([
            'message' => 'Features updated successfully.',
            'plan' => $plan->load('features')
        ]);
    }
}
