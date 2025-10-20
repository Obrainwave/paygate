<?php

namespace Obrainwave\Paygate\Services\Gateways;

use Obrainwave\Paygate\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;

class RemitaService implements PaymentGatewayInterface
{
    protected string $baseUrl;
    protected string $merchantId;
    protected string $apiKey;
    protected string $serviceTypeId;
    protected bool $enabled;

    public function __construct()
    {
        $this->baseUrl = config('paygate.remita.base_url');
        $this->merchantId = config('paygate.remita.merchant_id');
        $this->apiKey = config('paygate.remita.api_key');
        $this->serviceTypeId = config('paygate.remita.service_type_id');
        $this->enabled = config('paygate.remita.enabled', true);
    }

    /**
     * Initialize a payment transaction
     */
    public function initiatePayment(array $data): object
    {
        $url = "{$this->baseUrl}/remita/ecomm/{$this->merchantId}/{$data['reference']}/{$data['amount']}/{$data['email']}/{$this->serviceTypeId}";
        
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'remitaConsumerKey=' . $this->apiKey
        ];

        $payload = [
            'amount' => $data['amount'],
            'customerName' => $data['name'] ?? null,
            'customerEmail' => $data['email'],
            'customerPhone' => $data['phone_number'] ?? null,
            'description' => $data['description'] ?? 'Payment via Paygate',
            'redirectUrl' => $data['redirect_url'] ?? null,
        ];

        $response = Http::withOptions(['headers' => $headers])
            ->post($url, $payload);

        if ($response->status() === 200 && isset($response['statuscode']) && $response['statuscode'] === '00') {
            return $this->formatSuccessResponse($response, $data);
        }

        return $this->formatErrorResponse($response, 'Payment initiation failed');
    }

    /**
     * Verify a payment transaction
     */
    public function verifyPayment(array $data): object
    {
        $url = "{$this->baseUrl}/remita/ecomm/{$this->merchantId}/{$data['reference']}/{$this->apiKey}/status.reg";
        
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'remitaConsumerKey=' . $this->apiKey
        ];

        $response = Http::withOptions(['headers' => $headers])
            ->get($url);

        if ($response->status() === 200 && isset($response['statuscode']) && $response['statuscode'] === '00') {
            return $this->formatVerificationResponse($response, $data);
        }

        return $this->formatErrorResponse($response, 'Payment verification failed');
    }

    /**
     * Refund a payment transaction
     */
    public function refundPayment(array $data): object
    {
        // Remita doesn't have direct refund API, return error
        return (object) [
            'errors' => true,
            'message' => 'Refund not supported by Remita',
            'description' => 'Please contact Remita support for refund processing',
            'provider' => 'remita'
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
        return 'remita';
    }

    /**
     * Check if gateway is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->merchantId) && !empty($this->apiKey) && !empty($this->serviceTypeId);
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
        return ['card', 'bank', 'ussd', 'qr', 'bank_transfer'];
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
            'message' => 'Payment initiated successfully with remita',
            'data' => [
                'checkout_url' => $response['RRR'] ?? null,
                'reference' => $data['reference'],
                'access_code' => $response['RRR'] ?? null,
                'provider' => 'remita'
            ]
        ];
    }

    /**
     * Format verification response
     */
    protected function formatVerificationResponse($response, array $data): object
    {
        $status = strtolower($response['status']) === 'success' ? 'successful' : 'failed';

        return (object) [
            'errors' => false,
            'message' => 'Payment verified successfully with remita',
            'provider' => 'remita',
            'status' => $status,
            'amount' => $response['amount'] ?? 0,
            'charged_amount' => $response['amount'] ?? 0,
            'reference' => $data['reference'],
            'provider_reference' => $response['RRR'] ?? null,
            'payment_method' => $response['paymentMethod'] ?? 'unknown',
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
            'message' => $response['status'] ?? $message,
            'description' => $response['message'] ?? 'Please check your payment details and try again',
            'provider' => 'remita',
            'data' => $response->json()
        ];
    }
}
