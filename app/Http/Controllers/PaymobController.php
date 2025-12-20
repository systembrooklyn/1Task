<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
use App\Models\Transaction;
use App\Services\PaymobService;
use App\Services\PromoCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        $amount = (int) ($request['amount'] * 100);
        $billingData = $request['billing_data'];
        $companyDetails = $request['companyDetails'];
        $items = [
            [
                'name' => $companyDetails['plan_name'],
                'amount' => $amount,
                'quantity' => 1,
            ],
        ];
        $extras = [
            'company_info' => $companyDetails,
        ];
        $url = $this->paymobService->createIntention($amount, $items, $billingData, $extras);

        return response()->json([
            'message' => "Payment link retreived successfully",
            'iframe_url' => $url,
        ]);
    }
    //------------------Bahgat Response------------------
    // public function handleCallback(Request $request)
    // {
    //     $rawContent = $request->getContent();
    //     $payload = json_decode($rawContent, true);

    //     Log::info('Paymob webhook received', [
    //         'transaction_id' => $payload['obj']['id'] ?? 'unknown',
    //         'status' => $payload['obj']['success'] ?? 'unknown',
    //     ]);

    //     $transactionId = $payload['obj']['id'] ?? null;
    //     if (!$transactionId) {
    //         Log::warning('Paymob callback missing transaction ID', compact('payload'));
    //         return response()->json(['status' => 'ok'], 200);
    //     }

    //     $companyInfo = $payload['obj']['payment_key_claims']['extra']['company_info'] ?? null;
    //     $companyId = $companyInfo['company_id'] ?? null;
    //     $planId = $companyInfo['plan_id'] ?? null;
    //     $userId = $companyInfo['user_id'] ?? null;
    //     $planName = $companyInfo['plan_name'] ?? null;
    //     $promoCode = $companyInfo['promo_code'] ?? null;

    //     $metadataValid = $companyId && $planId;

    //     if ($metadataValid) {
    //         $company = Company::find($companyId);
    //         if (!$company) {
    //             Log::error("Company not found for ID: $companyId", compact('transactionId'));
    //             $metadataValid = false;
    //         }
    //     } else {
    //         Log::warning('Missing company_info in Paymob callback', [
    //             'transaction_id' => $transactionId,
    //             'provided_data' => $companyInfo,
    //         ]);
    //     }

    //     $obj = $payload['obj'] ?? [];

    //     $success = ($obj['success'] ?? false) === true;
    //     $pending = ($obj['pending'] ?? false) === true;
    //     $isRefunded = ($obj['is_refunded'] ?? false) === true;
    //     $isVoided = ($obj['is_voided'] ?? false) === true;
    //     $errorOccurred = ($obj['error_occured'] ?? false) === true;

    //     $status = 'unknown';
    //     if ($isVoided) {
    //         $status = 'voided';
    //     } elseif ($isRefunded) {
    //         $status = 'refunded';
    //     } elseif ($pending) {
    //         $status = 'pending';
    //     } elseif ($success && !$errorOccurred) {
    //         $status = 'success';
    //     } else {
    //         $status = 'failed';
    //     }
    //     $transaction = Transaction::updateOrCreate(
    //         ['transaction_id' => (string) $transactionId],
    //         [
    //             'company_id' => $companyId,
    //             'user_id' => $userId,
    //             'plan_id' => $planId,
    //             'plan_name' => $planName,
    //             'amount_cents' => (int)($obj['amount_cents'] ?? 0),
    //             'currency' => $obj['currency'] ?? 'EGP',
    //             'payment_method' => $obj['source_data']['type'] ?? null,
    //             'additional_info' => $companyInfo,
    //             'success' => $success,
    //             'pending' => $pending,
    //             'is_refunded' => $isRefunded,
    //             'is_voided' => $isVoided,
    //             'refunded_amount_cents' => (int)($obj['refunded_amount_cents'] ?? 0),
    //             'error_message' => $obj['data']['message'] ?? ($obj['data_message'] ?? null),
    //             'status' => $status,
    //             'paid_at' => $status === 'success' ? now() : null,
    //             'raw_response' => $payload,
    //         ]
    //     );

    //     if ($status === 'success' && $metadataValid) {
    //         if ($promoCode) {
    //             $result = $this->promoCodeService->isValid($promoCode, $company->id, $planId);
    //             if ($result['valid']) {
    //                 $this->promoCodeService->applyPromo($result['promo'], $company->id);
    //             }
    //         }

    //         $company->update([
    //             'plan_id' => $planId,
    //             'plan_expires_at' => today()->addMonth(),
    //         ]);

    //         Log::info('Subscription updated successfully', [
    //             'company_id' => $company->id,
    //             'plan_id' => $planId,
    //             'transaction_id' => $transactionId,
    //         ]);
    //     }

    //     return response()->json(['status' => 'ok'], 200);
    // }
    public function handleCallback(Request $request)
    {
        $rawContent = $request->getContent();
        $payload = json_decode($rawContent, true);
        Log::info('Paymob client-side callback received', [
            'transaction_id' => $payload['transaction']['id'] ?? 'unknown',
            'success' => $payload['transaction']['success'] ?? 'unknown',
        ]);
        $transactionData = $payload['transaction'] ?? [];
        $intentionData = $payload['intention'] ?? [];

        $transactionId = $transactionData['id'] ?? null;
        if (!$transactionId) {
            Log::warning('Missing transaction ID in Paymob response', compact('payload'));
            return response()->json(['status' => 'ok'], 200);
        }
        $companyInfo = $intentionData['extras']['creation_extras']['company_info'] ?? null;

        $companyId = $companyInfo['company_id'] ?? null;
        $planId = $companyInfo['plan_id'] ?? null;
        $userId = $companyInfo['user_id'] ?? null;
        $planName = $companyInfo['plan_name'] ?? null;
        $promoCode = $companyInfo['promo_code'] ?? null;

        $metadataValid = $companyId && $planId;

        if ($metadataValid) {
            $company = Company::find($companyId);
            if (!$company) {
                Log::error("Company not found: $companyId", compact('transactionId'));
                $metadataValid = false;
            }
        } else {
            Log::warning('Missing company_info in Paymob response', [
                'transaction_id' => $transactionId,
                'data' => $companyInfo,
            ]);
        }
        $success = ($transactionData['success'] ?? false) === true;
        $pending = ($transactionData['pending'] ?? false) === true;
        $isRefunded = ($transactionData['is_refunded'] ?? false) === true;
        $isVoided = ($transactionData['is_voided'] ?? false) === true;
        $errorOccurred = ($transactionData['error_occured'] ?? false) === true;

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
            ['transaction_id' => (string) $transactionId],
            [
                'company_id' => $companyId,
                'user_id' => $userId,
                'plan_id' => $planId,
                'plan_name' => $planName,
                'amount_cents' => (int)($transactionData['amount_cents'] ?? 0),
                'currency' => $transactionData['currency'] ?? 'EGP',
                'payment_method' => $transactionData['source_data']['type'] ?? null,
                'additional_info' => $companyInfo,
                'success' => $success,
                'pending' => $pending,
                'is_refunded' => $isRefunded,
                'is_voided' => $isVoided,
                'refunded_amount_cents' => (int)($transactionData['refunded_amount_cents'] ?? 0),
                'error_message' => null,
                'status' => $status,
                'paid_at' => $status === 'success' ? now() : null,
                'raw_response' => $payload,
            ]
        );
        if ($status === 'success' && $metadataValid) {
            if ($promoCode) {
                $result = $this->promoCodeService->isValid($promoCode, $company->id, $planId);
                if ($result['valid']) {
                    $this->promoCodeService->applyPromo($result['promo'], $company->id);
                }
            }

            $company->update([
                'plan_id' => $planId,
                'plan_expires_at' => today()->addMonth(),
            ]);

            Log::info('Subscription updated successfully', [
                'company_id' => $company->id,
                'plan_id' => $planId,
                'transaction_id' => $transactionId,
            ]);
        }

        return response()->json(['status' => 'ok'], 200);
    }
}
