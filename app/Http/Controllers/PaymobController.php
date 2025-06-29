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
        $transactionData = [
            'company_id' => $companyId,
            'user_id' => $userId,
            'plan_id' => $planId,
            'plan_name' => $planName,
            'transaction_id' => $data['id'] ?? null,
            'amount_cents' => ($data['amount_cents'] ?? 0),
            'currency' => $data['currency'] ?? 'EGP',
            'payment_method' => $data['payment_method'] ?? null,
            'additional_info' => json_encode($additionalInfo),
            'success' => (bool)($data['success'] ?? false),
            'error_message' => $data['error_message'] ?? null,
            'paid_at' => now(),
        ];
        $transaction = Transaction::create($transactionData);
        if ($data['success']) {
            $company->update([
                'plan_id' => $planId,
                'plan_expires_at' => today()->addMonth(),
            ]);
            return redirect('http://192.168.1.40:8080/signin');
        }
        return redirect('http://192.168.1.40:8080/signin');
    }
}
