<?php

// namespace App\Helpers;

// use App\Models\Company;
// use App\Models\Feature;

// class PlanHelper
// {
//     public static function checkFeatureAccess($companyId, $featureSlug)
//     {
//         $company = Company::find($companyId);

//         if (!$company || !$company->plan) {
//             return ['allowed' => false, 'message' => 'No active plan found.'];
//         }

//         $feature = Feature::where('slug', $featureSlug)->first();

//         if (!$feature) {
//             return ['allowed' => false, 'message' => 'Feature not found.'];
//         }

//         $planFeature = $company->plan->features()
//             ->where('feature_id', $feature->id)
//             ->first();

//         if (!$planFeature) {
//             return ['allowed' => false, 'message' => 'Feature not available in your plan.'];
//         }

//         $usage = $company->usages()
//             ->where('feature_id', $feature->id)
//             ->whereDate('reset_date', now()->startOfMonth())
//             ->first();

//         if ($usage && $usage->used >= $planFeature->value) {
//             return ['allowed' => false, 'message' => 'Usage limit exceeded for this feature.'];
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

//         return ['allowed' => true];
//     }
// }