<?php

namespace App\Http\Controllers;

use App\Services\PromoCodeService;
use App\Models\Plan;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    protected $promoCodeService;

    public function __construct(PromoCodeService $promoCodeService)
    {
        $this->promoCodeService = $promoCodeService;
    }

    /**
     * Handle subscription with optional promo code.
     */
    public function subscribe(Request $request)
    {
        $user = Auth::user();
        $company = Company::find($user->company_id);
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'promo_code' => 'nullable|string',
        ]);
        $planId = $request->input('plan_id');
        $promoCode = $request->input('promo_code');

        $plan = Plan::find($planId);
        if (!$plan || !$plan->is_active) {
            return response()->json([
                'message' => 'Plans is not Available now',
            ], 404);
        }

        $finalPrice = $plan->price;
        if ($promoCode) {
            $result = $this->promoCodeService->isValid($promoCode, $company->id, $planId);

            if (!$result['valid']) {
                return response()->json(['error' => $result['message']], 400);
            }

            $promo = $result['promo'];
            if ($promo->type === 'fixed') {
                $finalPrice = max(0, $plan->price - $promo->value);
            } else {
                $finalPrice = $plan->price * (1 - ($promo->value / 100));
            }
            $this->promoCodeService->applyPromo($promo, $company->id);
        }
        $company->update([
            'plan_id' => $planId,
            'plan_expires_at' => now()->addMonth(),
        ]);

        return response()->json([
            'plan' => $plan->name,
            'price' => $finalPrice,
            'expires_at' => now()->addMonth(),
        ]);
    }
}
