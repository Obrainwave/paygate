<?php

namespace Obrainwave\Paygate\Services\Gateways;

use Obrainwave\Paygate\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackService implements PaymentGatewayInterface
{
    protected string $baseUrl;
    protected string $secretKey;
    protected bool $enabled;

    public function __construct()
    {
        $this->baseUrl = config('paygate.paystack.base_url');
        $this->secretKey = config('paygate.paystack.secret_key');
        $this->enabled = config('paygate.paystack.enabled', true);
    }

    /**
     * Initialize a payment transaction
     */
    public function initiatePayment(array $data): object
    {
        $url = "{$this->baseUrl}/transaction/initialize";
        $headers = ['Content-Type' => 'application/json'];

        $payload = [
            'email' => $data['email'],
            'amount' => ($data['amount'] ?? 0) * 100,
            'currency' => $data['currency'] ?? 'NGN',
            'reference' => $data['reference'] ?? null,
            'channels' => $data['payment_methods'] ?? null,
            'callback_url' => $data['redirect_url'] ?? null,
        ];

        $response = Http::withToken($this->secretKey)
            ->withOptions(['headers' => $headers])
            ->post($url, $payload);

        if ($response->status() === 200 && $response['status'] === true) {
            return $this->formatSuccessResponse($response, $data);
        }

        return $this->formatErrorResponse($response, 'Payment initiation failed');
    }

    /**
     * Verify a payment transaction
     */
    public function verifyPayment(array $data): object
    {
        $url = "{$this->baseUrl}/transaction/verify/{$data['reference']}";
        $headers = ['Content-Type' => 'application/json'];

        $response = Http::withToken($this->secretKey)
            ->withOptions(['headers' => $headers])
            ->get($url);

        if ($response->status() === 200 && $response['status'] === true) {
            return $this->formatVerificationResponse($response, $data);
        }

        return $this->formatErrorResponse($response, 'Payment verification failed');
    }

    /**
     * Refund a payment transaction
     */
    public function refundPayment(array $data): object
    {
        $url = "{$this->baseUrl}/refund";
        $headers = ['Content-Type' => 'application/json'];

        $payload = [
            'transaction' => $data['reference'],
            'amount' => isset($data['amount']) ? $data['amount'] * 100 : null,
            'currency' => $data['currency'] ?? 'NGN',
            'customer_note' => $data['reason'] ?? 'Refund request',
        ];

        $response = Http::withToken($this->secretKey)
            ->withOptions(['headers' => $headers])
            ->post($url, $payload);

        if ($response->status() === 200 && $response['status'] === true) {
            return $this->formatRefundResponse($response, $data);
        }

        return $this->formatErrorResponse($response, 'Refund failed');
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $reference): object
    {
        $url = "{$this->baseUrl}/transaction/verify/{$reference}";
        $headers = ['Content-Type' => 'application/json'];

        $response = Http::withToken($this->secretKey)
            ->withOptions(['headers' => $headers])
            ->get($url);

        if ($response->status() === 200) {
            return $this->formatStatusResponse($response);
        }

        return $this->formatErrorResponse($response, 'Status check failed');
    }

    /**
     * Get gateway name
     */
    public function getGatewayName(): string
    {
        return 'paystack';
    }

    /**
     * Check if gateway is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->secretKey);
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
        return ['card', 'bank', 'ussd', 'qr', 'mobile_money', 'bank_transfer', 'eft'];
    }

    /**
     * Get supported currencies
     */
    public function getSupportedCurrencies(): array
    {
        return ['NGN', 'USD', 'GHS', 'ZAR', 'KES'];
    }

    /**
     * Format success response
     */
    protected function formatSuccessResponse($response, array $data): object
    {
        return (object) [
            'errors' => false,
            'message' => 'Payment initiated successfully with paystack',
            'data' => [
                'checkout_url' => $response['data']['authorization_url'] ?? null,
                'reference' => $response['data']['reference'] ?? $data['reference'],
                'access_code' => $response['data']['access_code'] ?? null,
                'provider' => 'paystack'
            ]
        ];
    }

    /**
     * Format verification response
     */
    protected function formatVerificationResponse($response, array $data): object
    {
        $transaction = $response['data'];
        $status = strtolower($transaction['status']) === 'success' ? 'successful' : 'failed';

        return (object) [
            'errors' => false,
            'message' => 'Payment verified successfully with paystack',
            'provider' => 'paystack',
            'status' => $status,
            'amount' => $transaction['amount'] / 100,
            'charged_amount' => $transaction['requested_amount'] / 100,
            'reference' => $transaction['reference'],
            'provider_reference' => $transaction['reference'],
            'payment_method' => $transaction['channel'] ?? 'unknown',
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
            'message' => 'Refund processed successfully with paystack',
            'provider' => 'paystack',
            'refund_reference' => $response['data']['reference'] ?? null,
            'amount' => $response['data']['amount'] / 100,
            'status' => $response['data']['status'],
            'data' => $response['data']
        ];
    }

    /**
     * Format status response
     */
    protected function formatStatusResponse($response): object
    {
        $transaction = $response['data'];
        $status = strtolower($transaction['status']) === 'success' ? 'successful' : 'failed';

        return (object) [
            'errors' => false,
            'message' => 'Payment status retrieved successfully',
            'provider' => 'paystack',
            'status' => $status,
            'amount' => $transaction['amount'] / 100,
            'reference' => $transaction['reference'],
            'data' => $transaction
        ];
    }

    /**
     * Format error response
     */
    protected function formatErrorResponse($response, string $message): object
    {
        return (object) [
            'errors' => true,
            'message' => $response['message'] ?? $message,
            'description' => $response['meta']['nextStep'] ?? 'Please check your payment details and try again',
            'provider' => 'paystack',
            'data' => $response->json()
        ];
    }
}
