<?php

namespace Obrainwave\Paygate\Services\Gateways;

use Obrainwave\Paygate\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;

class VtpassService implements PaymentGatewayInterface
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $secretKey;
    protected bool $enabled;

    public function __construct()
    {
        $this->baseUrl = config('paygate.vtpass.base_url');
        $this->apiKey = config('paygate.vtpass.api_key');
        $this->secretKey = config('paygate.vtpass.secret_key');
        $this->enabled = config('paygate.vtpass.enabled', true);
    }

    /**
     * Initialize a payment transaction
     */
    public function initiatePayment(array $data): object
    {
        $url = "{$this->baseUrl}/api/pay";
        
        $headers = [
            'Content-Type' => 'application/json',
            'api-key' => $this->apiKey,
            'secret-key' => $this->secretKey
        ];

        $payload = [
            'request_id' => $data['reference'],
            'serviceID' => $data['service_id'] ?? 'airtime',
            'variation_code' => $data['variation_code'] ?? 'mtn',
            'amount' => $data['amount'],
            'phone' => $data['phone_number'] ?? null,
            'email' => $data['email'],
            'bills_fees' => $data['bills_fees'] ?? 0,
            'quantity' => $data['quantity'] ?? 1,
        ];

        $response = Http::withOptions(['headers' => $headers])
            ->post($url, $payload);

        if ($response->status() === 200 && $response['code'] === '000') {
            return $this->formatSuccessResponse($response, $data);
        }

        return $this->formatErrorResponse($response, 'Payment initiation failed');
    }

    /**
     * Verify a payment transaction
     */
    public function verifyPayment(array $data): object
    {
        $url = "{$this->baseUrl}/api/requery";
        
        $headers = [
            'Content-Type' => 'application/json',
            'api-key' => $this->apiKey,
            'secret-key' => $this->secretKey
        ];

        $payload = [
            'request_id' => $data['reference']
        ];

        $response = Http::withOptions(['headers' => $headers])
            ->post($url, $payload);

        if ($response->status() === 200 && $response['code'] === '000') {
            return $this->formatVerificationResponse($response, $data);
        }

        return $this->formatErrorResponse($response, 'Payment verification failed');
    }

    /**
     * Refund a payment transaction
     */
    public function refundPayment(array $data): object
    {
        // VTPass doesn't have direct refund API, return error
        return (object) [
            'errors' => true,
            'message' => 'Refund not supported by VTPass',
            'description' => 'Please contact VTPass support for refund processing',
            'provider' => 'vtpass'
        ];
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $reference): object
    {
        return $this->verifyPayment(['reference' => $reference]);
    }

    /**
     * Get gateway name
     */
    public function getGatewayName(): string
    {
        return 'vtpass';
    }

    /**
     * Check if gateway is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->apiKey) && !empty($this->secretKey);
    }

    /**
     * Validate payment data
     */
    public function validatePaymentData(array $data): object
    {
        $required = ['email', 'amount', 'reference'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return (object) [
                    'status' => false,
                    'message' => "Missing required field: {$field}"
                ];
            }
        }

        return (object) ['status' => true];
    }

    /**
     * Get supported payment methods
     */
    public function getSupportedPaymentMethods(): array
    {
        return ['airtime', 'data', 'electricity', 'cable_tv', 'internet', 'education'];
    }

    /**
     * Get supported currencies
     */
    public function getSupportedCurrencies(): array
    {
        return ['NGN'];
    }

    /**
     * Format success response
     */
    protected function formatSuccessResponse($response, array $data): object
    {
        return (object) [
            'errors' => false,
            'message' => 'Payment initiated successfully with vtpass',
            'data' => [
                'checkout_url' => $response['purchase_code'] ?? null,
                'reference' => $data['reference'],
                'access_code' => $response['purchase_code'] ?? null,
                'provider' => 'vtpass'
            ]
        ];
    }

    /**
     * Format verification response
     */
    protected function formatVerificationResponse($response, array $data): object
    {
        $status = strtolower($response['content']['transactions']['status']) === 'delivered' ? 'successful' : 'failed';

        return (object) [
            'errors' => false,
            'message' => 'Payment verified successfully with vtpass',
            'provider' => 'vtpass',
            'status' => $status,
            'amount' => $response['content']['transactions']['amount'] ?? 0,
            'charged_amount' => $response['content']['transactions']['amount'] ?? 0,
            'reference' => $data['reference'],
            'provider_reference' => $response['content']['transactions']['transactionId'] ?? null,
            'payment_method' => $response['content']['transactions']['product_name'] ?? 'unknown',
            'data' => $response
        ];
    }

    /**
     * Format error response
     */
    protected function formatErrorResponse($response, string $message): object
    {
        return (object) [
            'errors' => true,
            'message' => $response['response_description'] ?? $message,
            'description' => $response['response_description'] ?? 'Please check your payment details and try again',
            'provider' => 'vtpass',
            'data' => $response->json()
        ];
    }
}
