<?php

namespace Obrainwave\Paygate\Services\Gateways;

use Obrainwave\Paygate\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;

class GtpayService implements PaymentGatewayInterface
{
    protected string $baseUrl;
    protected string $secretKey;
    protected bool $enabled;

    public function __construct()
    {
        $this->baseUrl = config('paygate.gtpay.base_url');
        $this->secretKey = config('paygate.gtpay.secret_key');
        $this->enabled = config('paygate.gtpay.enabled', true);
    }

    /**
     * Initialize a payment transaction
     */
    public function initiatePayment(array $data): object
    {
        $url = "{$this->baseUrl}/transaction/initiate";
        $headers = ['Content-Type' => 'application/json'];

        $payload = [
            'transaction_ref' => $data['reference'] ?? null,
            'customer_name' => $data['name'] ?? null,
            'email' => $data['email'],
            'amount' => ($data['amount'] ?? 0) * 100,
            'currency' => $data['currency'] ?? 'NGN',
            'initiate_type' => $data['initiate_type'] ?? 'inline',
            'callback_url' => $data['redirect_url'] ?? null,
            'payment_channels' => $data['payment_methods'] ?? [],
            'pass_charge' => $data['pass_charge'] ?? false,
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
        // GTPay doesn't have direct refund API, return error
        return (object) [
            'errors' => true,
            'message' => 'Refund not supported by GTPay',
            'description' => 'Please contact GTPay support for refund processing',
            'provider' => 'gtpay'
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
        return 'gtpay';
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
     * Format success response
     */
    protected function formatSuccessResponse($response, array $data): object
    {
        return (object) [
            'errors' => false,
            'message' => 'Payment initiated successfully with gtpay',
            'data' => [
                'checkout_url' => $response['data']['checkout_url'] ?? null,
                'reference' => $response['data']['transaction_ref'] ?? $data['reference'],
                'access_code' => $response['data']['transaction_ref'] ?? null,
                'provider' => 'gtpay'
            ]
        ];
    }

    /**
     * Format verification response
     */
    protected function formatVerificationResponse($response, array $data): object
    {
        $transaction = $response['data'];
        $status = strtolower($transaction['transaction_status']) === 'success' ? 'successful' : 'failed';

        return (object) [
            'errors' => false,
            'message' => 'Payment verified successfully with gtpay',
            'provider' => 'gtpay',
            'status' => $status,
            'amount' => $transaction['transaction_amount'] / 100,
            'charged_amount' => ($transaction['transaction_amount'] / 100) + $transaction['fee'],
            'reference' => $transaction['transaction_ref'],
            'provider_reference' => $transaction['gateway_transaction_ref'],
            'payment_method' => $transaction['transaction_type'] ?? 'unknown',
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
            'description' => $response['data'] ?? 'Please check your payment details and try again',
            'provider' => 'gtpay',
            'data' => $response->json()
        ];
    }
}
