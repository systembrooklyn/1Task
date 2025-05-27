<?php

namespace App\Http\Controllers;

use App\Exceptions\DuplicateDataException;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Feature;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PlanController extends Controller
{

    public function allPlans()
    {
        $plans = Plan::where('is_active', true)
            ->with(['features' => function ($query) {
                $query->withPivot('value');
            }])
            ->get()
            ->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'price' => $plan->price,
                    'currency' => $plan->currency,
                    'features' => $plan->features->map(function ($feature) {
                        return [
                            'name' => optional($feature)->name,
                            'value' => optional($feature->pivot)->value,
                        ];
                    }),
                ];
            });

        return response()->json([
            'message' => 'Plans retreived successfully',
            'data' => $plans
        ], 200);
    }

    public function index()
    {
        $UserAuth = Auth::user();
        $user = User::find($UserAuth->id);
        $price = $user->company->plan->price ?? 0;
        $plans = Plan::where('is_active', true)
            ->where('price', '>=', $price)
            ->with(['features' => function ($query) {
                $query->withPivot('value');
            }])
            ->get()
            ->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'price' => $plan->price,
                    'currency' => $plan->currency,
                    'features' => $plan->features->map(function ($feature) {
                        return [
                            'name' => optional($feature)->name,
                            'value' => optional($feature->pivot)->value,
                        ];
                    }),
                ];
            });

        return response()->json([
            'message' => 'Plans retreived successfully',
            'data' => $plans
        ], 200);
    }

    // Create a new plan
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'currency' => 'required|string',
            'is_active' => 'boolean',
        ]);
        $existingPlan = Plan::where('name', $request->name)->first();
        if ($existingPlan) throw new DuplicateDataException('Plan Already Exists with the same name', 409);
        $plan = Plan::create($request->only(['name', 'price', 'is_active', 'currency']));

        return response()->json([
            'message' => 'Plan created successfully',
            'data' => $plan
        ], 201);
    }

    public function attachFeatures(Request $request, Plan $plan)
    {
        $request->validate([
            'features' => 'required|array',
            'features.*.id' => 'exists:features,id',
            'features.*.value' => 'required|integer',
            'features.*.resettable' => 'boolean'
        ]);

        $features = collect($request->input('features'))
            ->mapWithKeys(function ($item) {
                $feature = Feature::find($item['id']);
                return [
                    $item['id'] => [
                        'value' => $item['value'],
                        'resettable' => $item['resettable'] ?? true,
                        'reset_frequency' => $feature->reset_frequency
                    ]
                ];
            })
            ->all();

        $plan->features()->sync($features);

        return response()->json([
            'message' => 'Features updated successfully.',
            'plan' => $plan->load('features')
        ]);
    }
}
