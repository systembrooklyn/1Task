<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;

class PaymobService
{
    protected $client;
    protected $apiKey;
    protected $integrationId;
    protected $iframeId;
    protected $hmacSecret;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = Config::get('services.paymob.api_key');
        $this->integrationId = Config::get('services.paymob.integration_id');
        $this->iframeId = Config::get('services.paymob.iframe_id');
        $this->hmacSecret = Config::get('services.paymob.hmac_secret');
    }

    // Getter for iframeId
    public function getIframeId()
    {
        return $this->iframeId;
    }

    // Authenticate with Paymob
    public function authenticate()
    {
        $response = $this->client->post('https://accept.paymob.com/api/auth/tokens', [
            'json' => [
                'api_key' => $this->apiKey,
            ],
        ]);

        return json_decode($response->getBody(), true)['token'];
    }

    // Create an order
    public function createOrder($amount, $items, $customMetadata = [])
    {
        $token = $this->authenticate();
        $merchantOrderId = json_encode([
            'id' => uniqid('order_'),
            'serial' => $customMetadata['serial'] ?? null,
            'additional_info' => $customMetadata['additional_info'] ?? null,
        ]);

        $response = $this->client->post('https://accept.paymob.com/api/ecommerce/orders',  [
            'json' => [
                'auth_token' => $token,
                'delivery_needed' => false,
                'amount_cents' => $amount * 100, // Convert to cents
                'currency' => 'EGP',
                'items' => $items,
                'merchant_order_id' => $merchantOrderId, // Include custom data here
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    // Generate payment key
    public function getPaymentKey($order, $billingData)
    {
        $token = $this->authenticate();

        $response = $this->client->post('https://accept.paymob.com/api/acceptance/payment_keys', [
            'json' => [
                'auth_token' => $token,
                'amount_cents' => $order['amount_cents'],
                'expiration' => 3600,
                'order_id' => $order['id'],
                'billing_data' => $billingData,
                'currency' => 'EGP',
                'integration_id' => $this->integrationId,
                'return_url' => 'https://www.1task.net',
            ],
        ]);

        return json_decode($response->getBody(), true)['token'];
    }
}
