<?php

namespace Obrainwave\Paygate\Services\Gateways;

use Obrainwave\Paygate\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;

class FlutterwaveService implements PaymentGatewayInterface
{
    protected string $baseUrl;
    protected string $secretKey;
    protected bool $enabled;

    public function __construct()
    {
        $this->baseUrl = config('paygate.flutterwave.base_url');
        $this->secretKey = config('paygate.flutterwave.secret_key');
        $this->enabled = config('paygate.flutterwave.enabled', true);
    }

    /**
     * Initialize a payment transaction
     */
    public function initiatePayment(array $data): object
    {
        $url = "{$this->baseUrl}/payments";
        $headers = ['Content-Type' => 'application/json'];

        $payload = [
            'tx_ref' => $data['reference'],
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'NGN',
            'redirect_url' => $data['redirect_url'] ?? null,
            'customer' => [
                'email' => $data['email'],
                'phone_number' => $data['phone_number'] ?? null,
                'name' => $data['name'] ?? null,
            ],
            'customizations' => [
                'title' => $data['title'] ?? null,
                'logo' => $data['logo'] ?? null,
            ]
        ];

        $response = Http::withToken($this->secretKey)
            ->withOptions(['headers' => $headers])
            ->post($url, $payload);

        if ($response->status() === 200 && $response['status'] === 'success') {
            return $this->formatSuccessResponse($response, $data);
        }

        return $this->formatErrorResponse($response, 'Payment initiation failed');
    }

    /**
     * Verify a payment transaction
     */
    public function verifyPayment(array $data): object
    {
        $url = "{$this->baseUrl}/transactions/verify_by_reference?tx_ref={$data['reference']}";
        $headers = ['Content-Type' => 'application/json'];

        $response = Http::withToken($this->secretKey)
            ->withOptions(['headers' => $headers])
            ->get($url);

        if ($response->status() === 200 && $response['status'] === 'success') {
            return $this->formatVerificationResponse($response, $data);
        }

        return $this->formatErrorResponse($response, 'Payment verification failed');
    }

    /**
     * Refund a payment transaction
     */
    public function refundPayment(array $data): object
    {
        $url = "{$this->baseUrl}/transactions/{$data['reference']}/refund";
        $headers = ['Content-Type' => 'application/json'];

        $payload = [
            'amount' => $data['amount'] ?? null,
            'comments' => $data['reason'] ?? 'Refund request',
        ];

        $response = Http::withToken($this->secretKey)
            ->withOptions(['headers' => $headers])
            ->post($url, $payload);

        if ($response->status() === 200 && $response['status'] === 'success') {
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
        return 'flutterwave';
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
        $required = ['email', 'amount', 'reference', 'redirect_url'];
        
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
        return ['card', 'bank', 'mobile_money', 'bank_transfer'];
    }

    /**
     * Get supported currencies
     */
    public function getSupportedCurrencies(): array
    {
        return ['NGN', 'USD', 'GHS', 'KES', 'ZAR', 'TZS', 'UGX', 'ZMW', 'BWP', 'RWF'];
    }

    /**
     * Format success response
     */
    protected function formatSuccessResponse($response, array $data): object
    {
        $checkoutUrl = $response['data']['link'] ?? null;
        $accessCode = $checkoutUrl ? basename(parse_url($checkoutUrl, PHP_URL_PATH)) : null;

        return (object) [
            'errors' => false,
            'message' => 'Payment initiated successfully with flutterwave',
            'data' => [
                'checkout_url' => $checkoutUrl,
                'reference' => $data['reference'],
                'access_code' => $accessCode,
                'provider' => 'flutterwave'
            ]
        ];
    }

    /**
     * Format verification response
     */
    protected function formatVerificationResponse($response, array $data): object
    {
        $transaction = $response['data'];
        $status = strtolower($transaction['status']) === 'successful' ? 'successful' : 'failed';

        return (object) [
            'errors' => false,
            'message' => 'Payment verified successfully with flutterwave',
            'provider' => 'flutterwave',
            'status' => $status,
            'amount' => $transaction['amount'],
            'charged_amount' => $transaction['charged_amount'],
            'reference' => $transaction['tx_ref'],
            'provider_reference' => $transaction['flw_ref'],
            'payment_method' => $transaction['payment_type'] ?? 'unknown',
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
            'message' => 'Refund processed successfully with flutterwave',
            'provider' => 'flutterwave',
            'refund_reference' => $response['data']['id'] ?? null,
            'amount' => $response['data']['amount'] ?? null,
            'status' => $response['data']['status'] ?? 'pending',
            'data' => $response['data']
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
            'description' => $response['errors'] ?? 'Please check your payment details and try again',
            'provider' => 'flutterwave',
            'data' => $response->json()
        ];
    }
}
