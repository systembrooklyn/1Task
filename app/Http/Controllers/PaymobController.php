<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Transaction;
use App\Services\PaymobService;
use Illuminate\Http\Request;

class PaymobController extends Controller
{
    protected $paymobService;

    public function __construct(PaymobService $paymobService)
    {
        $this->paymobService = $paymobService;
    }

    public function initiatePayment(array $request)
    {
        $amount = $request['amount'];
        $billingData = $request['billing_data'];
        $companyDetails = $request['companyDetails'];
        $items = [
            [
                'name' => 'Check Payment',
                'amount_cents' => $amount * 100,
                'quantity' => 1,
            ],
        ];
        $customMetadata = [
            'additional_info' => $companyDetails,
        ];
        $order = $this->paymobService->createOrder($amount, $items, $customMetadata);
        $paymentKey = $this->paymobService->getPaymentKey($order, $billingData);
        $iframeId = $this->paymobService->getIframeId();

        return response()->json([
            'message' => "Payment link retreived successfully",
            'iframe_url' => "https://accept.paymob.com/api/acceptance/iframes/{$iframeId}?payment_token={$paymentKey}",
        ]);
    }

    public function handleCallback(Request $request)
    {
        $data = $request->all();
        $customDataJson = $data['merchant_order_id'] ?? null;
        if (!$customDataJson) {
            return response()->json(['error' => 'Missing merchant_order_id'], 400);
        }
        $customData = json_decode($customDataJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Invalid JSON in merchant_order_id'], 400);
        }
        $additionalInfo = $customData['additional_info'] ?? null;
        if (!$additionalInfo) {
            return response()->json(['error' => 'Missing additional_info'], 400);
        }
        $companyId = $additionalInfo['company_id'] ?? null;
        $companyName = $additionalInfo['company_name'] ?? null;
        $userId = $additionalInfo['user_id'] ?? null;
        $userName = $additionalInfo['user_name'] ?? null;
        $planId = $additionalInfo['plan_id'] ?? null;
        $planName = $additionalInfo['plan_name'] ?? null;
        $promoCode = $additionalInfo['promo_code'] ?? null;
        $company = Company::find($companyId);
        if ($data['success']) {
            $company->update([
                'plan_id' => $planId,
                'plan_expires_at' => today()->addMonth(),
            ]);
        }
        return redirect('https://1task.net/signin');
        // return response()->json([
        //     'message' => 'Payment received successfully',
        //     'company' => [
        //         'id' => $companyId,
        //         'name' => $companyName,
        //     ],
        //     'user' => [
        //         'id' => $userId,
        //         'name' => $userName,
        //     ],
        //     'plan' => [
        //         'id' => $planId,
        //         'name' => $planName,
        //     ],
        //     'promo_code' => $promoCode,
        // ]);
    }
}
