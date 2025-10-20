<?php

namespace Obrainwave\Paygate\Services\Gateways;

use Obrainwave\Paygate\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;

class InterswitchService implements PaymentGatewayInterface
{
    protected string $baseUrl;
    protected string $clientId;
    protected string $clientSecret;
    protected bool $enabled;

    public function __construct()
    {
        $this->baseUrl = config('paygate.interswitch.base_url');
        $this->clientId = config('paygate.interswitch.client_id');
        $this->clientSecret = config('paygate.interswitch.client_secret');
        $this->enabled = config('paygate.interswitch.enabled', true);
    }

    /**
     * Initialize a payment transaction
     */
    public function initiatePayment(array $data): object
    {
        // Generate access token first
        $token = $this->generateAccessToken();
        if (!$token->status) {
            return $token;
        }

        $url = "{$this->baseUrl}/api/v2/payments";
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token->token
        ];

        $payload = [
            'amount' => $data['amount'] * 100, // Convert to kobo
            'currency' => $data['currency'] ?? 'NGN',
            'customerEmail' => $data['email'],
            'customerName' => $data['name'] ?? null,
            'paymentReference' => $data['reference'],
            'redirectUrl' => $data['redirect_url'] ?? null,
            'description' => $data['description'] ?? 'Payment via Paygate',
        ];

        $response = Http::withOptions(['headers' => $headers])
            ->post($url, $payload);

        if ($response->status() === 200 && $response['responseCode'] === '00') {
            return $this->formatSuccessResponse($response, $data);
        }

        return $this->formatErrorResponse($response, 'Payment initiation failed');
    }

    /**
     * Verify a payment transaction
     */
    public function verifyPayment(array $data): object
    {
        $token = $this->generateAccessToken();
        if (!$token->status) {
            return $token;
        }

        $url = "{$this->baseUrl}/api/v2/payments/{$data['reference']}";
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token->token
        ];

        $response = Http::withOptions(['headers' => $headers])
            ->get($url);

        if ($response->status() === 200 && $response['responseCode'] === '00') {
            return $this->formatVerificationResponse($response, $data);
        }

        return $this->formatErrorResponse($response, 'Payment verification failed');
    }

    /**
     * Refund a payment transaction
     */
    public function refundPayment(array $data): object
    {
        $token = $this->generateAccessToken();
        if (!$token->status) {
            return $token;
        }

        $url = "{$this->baseUrl}/api/v2/payments/{$data['reference']}/refund";
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token->token
        ];

        $payload = [
            'amount' => isset($data['amount']) ? $data['amount'] * 100 : null,
            'reason' => $data['reason'] ?? 'Refund request',
        ];

        $response = Http::withOptions(['headers' => $headers])
            ->post($url, $payload);

        if ($response->status() === 200 && $response['responseCode'] === '00') {
            return $this->formatRefundResponse($response, $data);
        }

        return $this->formatErrorResponse($response, 'Refund failed');
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
        return 'interswitch';
    }

    /**
     * Check if gateway is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->clientId) && !empty($this->clientSecret);
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
        return ['NGN', 'USD'];
    }

    /**
     * Generate access token
     */
    protected function generateAccessToken(): object
    {
        $url = "{$this->baseUrl}/api/v2/oauth/token";
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret)
        ];

        $payload = [
            'grant_type' => 'client_credentials',
            'scope' => 'payments'
        ];

        $response = Http::withOptions(['headers' => $headers])
            ->asForm()
            ->post($url, $payload);

        if ($response->status() === 200 && isset($response['access_token'])) {
            return (object) [
                'status' => true,
                'token' => $response['access_token']
            ];
        }

        return (object) [
            'status' => false,
            'message' => 'Unable to generate Interswitch access token'
        ];
    }

    /**
     * Format success response
     */
    protected function formatSuccessResponse($response, array $data): object
    {
        return (object) [
            'errors' => false,
            'message' => 'Payment initiated successfully with interswitch',
            'data' => [
                'checkout_url' => $response['paymentUrl'] ?? null,
                'reference' => $response['paymentReference'] ?? $data['reference'],
                'access_code' => $response['transactionReference'] ?? null,
                'provider' => 'interswitch'
            ]
        ];
    }

    /**
     * Format verification response
     */
    protected function formatVerificationResponse($response, array $data): object
    {
        $transaction = $response['data'] ?? $response;
        $status = strtolower($transaction['status']) === 'success' ? 'successful' : 'failed';

        return (object) [
            'errors' => false,
            'message' => 'Payment verified successfully with interswitch',
            'provider' => 'interswitch',
            'status' => $status,
            'amount' => $transaction['amount'] / 100,
            'charged_amount' => $transaction['amount'] / 100,
            'reference' => $transaction['paymentReference'] ?? $data['reference'],
            'provider_reference' => $transaction['transactionReference'] ?? null,
            'payment_method' => $transaction['paymentMethod'] ?? 'unknown',
            'data' => $transaction
        ];
    }

    /**
     * Format refund response
     */
    protected function formatRefundResponse($response, array $data): object
    {
        return (object) [
            'errors' => false,
            'message' => 'Refund processed successfully with interswitch',
            'provider' => 'interswitch',
            'refund_reference' => $response['refundReference'] ?? null,
            'amount' => $response['amount'] / 100 ?? null,
            'status' => $response['status'] ?? 'pending',
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
            'message' => $response['responseMessage'] ?? $message,
            'description' => $response['responseMessage'] ?? 'Please check your payment details and try again',
            'provider' => 'interswitch',
            'data' => $response->json()
        ];
    }
}
