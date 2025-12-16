<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class PaymobService
{
    protected $integrationId;
    protected $paymobSecretKey;
    protected $paymobPublicKey;
    protected $paymobRedirectURL;
    protected $paymobURL = "https://accept.paymob.com/v1/intention/";
    protected $paymobCheckoutURL = "https://accept.paymob.com/unifiedcheckout/?";

    public function __construct()
    {
        $this->integrationId = Config::get('services.paymob.integration_id');
        $this->paymobSecretKey = Config::get('services.paymob.secret_key');
        $this->paymobPublicKey = Config::get('services.paymob.public_key');
        $this->paymobRedirectURL = Config::get('services.paymob.redirect_url');
    }

    public function createIntention($amount, $items, $billingData, $extras = [])
    {
        $payload = [
            'amount' => $amount,
            'currency' => 'EGP',
            'payment_methods' => [(int) $this->integrationId],
            'items' => $items,
            'billing_data' => $billingData,
            'metadata' => $extras,
            'extras' => $extras,
        ];

        if ($this->paymobRedirectURL) {
            $payload['redirect_url'] = $this->paymobRedirectURL;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Token ' . $this->paymobSecretKey,
            'Content-Type'  => 'application/json',
        ])->post($this->paymobURL, $payload);

        $data = $response->json();

        if (!isset($data['client_secret'])) {
            throw new \Exception('Failed to create payment intention');
        }

        $clientSecret = $data['client_secret'];
        return $this->paymobCheckoutURL . "publicKey={$this->paymobPublicKey}&clientSecret={$clientSecret}";
    }
}
