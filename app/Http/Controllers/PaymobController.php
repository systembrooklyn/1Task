<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
use App\Models\Transaction;
use App\Services\PaymobService;
use App\Services\PromoCodeService;
use Illuminate\Http\Request;

class PaymobController extends Controller
{
    protected $paymobService;
    protected $promoCodeService;
    public function __construct(PromoCodeService $promoCodeService, PaymobService $paymobService)
    {
        $this->promoCodeService = $promoCodeService;
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

    public function handleCallback2(Request $request)
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

        if (!$companyId || !$planId) {
            return response()->json(['error' => 'Missing company or plan ID'], 400);
        }

        $company = Company::find($companyId);
        if (!$company) {
            return response()->json(['error' => 'Company not found'], 404);
        }
        $success = filter_var($data['success'], FILTER_VALIDATE_BOOLEAN);
        $pending = filter_var($data['pending'], FILTER_VALIDATE_BOOLEAN);
        $isRefunded = filter_var($data['is_refunded'], FILTER_VALIDATE_BOOLEAN);
        $isVoided = filter_var($data['is_voided'], FILTER_VALIDATE_BOOLEAN);
        $errorOccurred = filter_var($data['error_occured'], FILTER_VALIDATE_BOOLEAN);
        $status = 'unknown';

        if ($isVoided) {
            $status = 'voided';
        } elseif ($isRefunded) {
            $status = 'refunded';
        } elseif ($pending) {
            $status = 'pending';
        } elseif ($success && !$errorOccurred) {
            $status = 'success';
        } else {
            $status = 'failed';
        }
        $transaction = Transaction::updateOrCreate(
            ['transaction_id' => $data['id']],
            [
                'company_id' => $companyId,
                'user_id' => $userId,
                'plan_id' => $planId,
                'plan_name' => $planName,
                'amount_cents' => (int)($data['amount_cents'] ?? 0),
                'currency' => $data['currency'] ?? 'EGP',
                'payment_method' => $data['source_data_type'] ?? null,
                'additional_info' => $additionalInfo,
                'success' => filter_var($data['success'], FILTER_VALIDATE_BOOLEAN),
                'pending' => filter_var($data['pending'], FILTER_VALIDATE_BOOLEAN),
                'is_refunded' => filter_var($data['is_refunded'], FILTER_VALIDATE_BOOLEAN),
                'is_voided' => filter_var($data['is_voided'], FILTER_VALIDATE_BOOLEAN),
                'refunded_amount_cents' => (int)($data['refunded_amount_cents'] ?? 0),
                'error_message' => $data['data_message'] ?? null,
                'status' => $status,
                'paid_at' => $status === 'success' ? now() : null,
                'raw_response' => json_encode($data),
            ]
        );
        if ($status === 'success') {
            if ($promoCode) {
                $result = $this->promoCodeService->isValid($promoCode, $company->id, $planId);
                $this->promoCodeService->applyPromo($result['promo'], $company->id);
            }
            $company->update([
                'plan_id' => $planId,
                'plan_expires_at' => today()->addMonth(),
            ]);
        }
        return response()->json(['status' => 'ok'], 200);
    }
    public function handleCallback(Request $request)
    {
        $this->handleCallback2($request);
        return redirect('https://www.1task.net')->with('payment_status');
    }
}
