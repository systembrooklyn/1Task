<?php

namespace App\Http\Controllers;

use App\Services\PromoCodeService;
use App\Models\Plan;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class SubscriptionController extends Controller
{
    protected $promoCodeService;
    protected $paymobController;
    public function __construct(PromoCodeService $promoCodeService, PaymobController $paymobController)
    {
        $this->promoCodeService = $promoCodeService;
        $this->paymobController = $paymobController;
    }

    public function getValueFromSheetViaCsv()
    {
        $sheetId = env('GOOGLE_SHEETS_CURRENCY_ID');
        $range   = env('GOOGLE_SHEETS_CURRENCY_RANGE');
        $apiKey   = env('GOOGLE_SHEETS_CURRENCY_API_KEY');
        $url = "https://sheets.googleapis.com/v4/spreadsheets/{$sheetId}/values/{$range}?key={$apiKey}&valueRenderOption=FORMATTED_VALUE";
        try {
            $response = Http::timeout(5)->get($url);
            if ($response->successful() && isset($response['values'][0][0])) {
                $value = $response['values'][0][0];
            } else {
                $value = null;
            }
        } catch (\Exception $e) {
            $value = null;
        }
        return $value;
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
        $CurEGP = $this->getValueFromSheetViaCsv() ?? 50;
        $amount = $finalPrice * $CurEGP;
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
            'amount' => ceil((int)$amount),
            'billing_data' => $billingData,
            'companyDetails' => $companyDetails
        ];

        return $this->paymobController->initiatePayment($paymobRequest);
    }
}
