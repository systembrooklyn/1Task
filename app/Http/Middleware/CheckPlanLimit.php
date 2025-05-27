<?php

// namespace App\Http\Middleware;

// use App\Models\Feature;
// use Closure;
// use Illuminate\Http\Request;
// use Symfony\Component\HttpFoundation\Response;

// class CheckPlanLimit
// {
//     public function handle($request, Closure $next, $featureSlug)
//     {
//         $user = $request->user();
//         $company = $user->company;

//         if (!$company || !$company->plan) {
//             throw new \Exception('No active plan found.');
//         }

//         $feature = Feature::where('slug', $featureSlug)->firstOrFail();

//         $planFeature = $company->plan->features()
//             ->where('feature_id', $feature->id)
//             ->first();

//         if (!$planFeature) {
//             return response()->json(['error' => 'Feature not available in your plan.'], 403);
//         }

//         $usage = $company->usages()
//             ->where('feature_id', $feature->id)
//             ->whereDate('reset_date', now()->startOfMonth())
//             ->first();

//         if ($usage && $usage->used >= $planFeature->value) {
//             return response()->json(['error' => 'Usage limit exceeded for this feature.'], 403);
//         }

//         if ($usage) {
//             $usage->increment('used');
//         } else {
//             $company->usages()->create([
//                 'feature_id' => $feature->id,
//                 'used' => 1,
//                 'reset_date' => now()->startOfMonth(),
//             ]);
//         }

//         return $next($request);
//     }
// }
