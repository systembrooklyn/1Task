<?php

namespace App\Modules\Plan\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Plan\Services\FeatureService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FeatureController extends Controller
{
    public function __construct(protected FeatureService $featureService) {}

    public function index(): JsonResponse
    {
        $features = $this->featureService->getAllFeatures();
        return response()->json([
            'message' => 'Features retrieved successfully',
            'data' => $features
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'slug' => 'required|string|unique:features,slug',
            'unit' => 'required|string|in:count,kb,mb',
            'reset_frequency' => 'nullable|string|in:daily,weekly,monthly',
        ]);

        $feature = $this->featureService->createFeature($validated);
        return response()->json([
            'message' => 'Feature Created Successfully',
            'data' => $feature
        ], 201);
    }
}
