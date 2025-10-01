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
    protected $paymobController;
    public function __construct(PromoCodeService $promoCodeService, PaymobController $paymobController)
    {
        $this->promoCodeService = $promoCodeService;
        $this->paymobController = $paymobController;
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
        $currentPlan = $company->plan;
        $planId = $request->input('plan_id');
        $promoCode = $request->input('promo_code');

        $plan = Plan::find($planId);
        if (!$plan || !$plan->is_active) {
            return response()->json([
                'message' => 'Plan is not Available now',
                'iframe_url' => null,
            ], 404);
        }
        if ($plan->price < $currentPlan->price) {
            return response()->json([
                'message' => 'You cannot downgrade your plan, please contact the support',
                'iframe_url' => null,
            ], 404);
        }

        $finalPrice = $plan->price;
        if ($promoCode) {
            $result = $this->promoCodeService->isValid($promoCode, $company->id, $planId);

            if (!$result['valid']) {
                return response()->json(['message' => $result['message']], 400);
            }
            $promo = $result['promo'];
            if ($promo->type === 'fixed') {
                $finalPrice = max(0, $plan->price - $promo->value);
            } else {
                $finalPrice = $plan->price * (1 - ($promo->value / 100));
            }
        }
        $amount = $finalPrice * 50;
        $billingData = [
            'apartment' => 'NA',
            'email' => $user->email,
            'floor' => 'NA',
            'first_name' => $user->name ?? 'User',
            'street' => 'NA',
            'building' => 'NA',
            'phone_number' => $user->phone ?? '+201000000000',
            'shipping_method' => 'NA',
            'postal_code' => 'NA',
            'city' => 'NA',
            'country' => 'EG',
            'last_name' => $user->name ?? 'User',
            'state' => 'NA'
        ];
        $companyDetails = [
            'company_id' => $company->id,
            'company_name' => $company->name,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'plan_id' => $plan->id,
            'plan_name' => $plan->name,
            'promo_code' => $promoCode
        ];
        $paymobRequest = [
            'amount' => $amount,
            'billing_data' => $billingData,
            'companyDetails' => $companyDetails
        ];

        return $this->paymobController->initiatePayment($paymobRequest);
    }
}
