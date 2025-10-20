<?php

namespace Obrainwave\Paygate\Services\Gateways;

use Obrainwave\Paygate\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;

class MonnifyService implements PaymentGatewayInterface
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $secretKey;
    protected bool $enabled;

    public function __construct()
    {
        $this->baseUrl = config('paygate.monnify.base_url');
        $this->apiKey = config('paygate.monnify.api_key');
        $this->secretKey = config('paygate.monnify.secret_key');
        $this->enabled = config('paygate.monnify.enabled', true);
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

        $url = "{$this->baseUrl}/api/v1/merchant/transactions/init-transaction";
        $headers = ['Content-Type' => 'application/json'];

        $payload = [
            'paymentReference' => $data['reference'],
            'customerName' => $data['name'],
            'customerEmail' => $data['email'],
            'amount' => $data['amount'],
            'currencyCode' => $data['currency'] ?? 'NGN',
            'contractCode' => $data['contract_code'],
            'paymentMethods' => $data['payment_methods'] ?? null,
            'redirectUrl' => $data['redirect_url'] ?? null,
        ];

        $response = Http::withToken($token->token)
            ->withOptions(['headers' => $headers])
            ->post($url, $payload);

        if ($response->status() === 200 && $response['requestSuccessful'] === true) {
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

        $exp = explode('|', $data['reference']);
        $link = isset($exp[0]) && $exp[0] === 'MNFY' ? 'transactionReference' : 'paymentReference';
        $url = "{$this->baseUrl}/api/v2/merchant/transactions/query?{$link}={$data['reference']}";
        $headers = ['Content-Type' => 'application/json'];

        $response = Http::withToken($token->token)
            ->withOptions(['headers' => $headers])
            ->get($url);

        if ($response->status() === 200 && $response['requestSuccessful'] === true) {
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

        $url = "{$this->baseUrl}/api/v1/merchant/transactions/refund";
        $headers = ['Content-Type' => 'application/json'];

        $payload = [
            'transactionReference' => $data['provider_reference'] ?? $data['reference'],
            'refundAmount' => $data['amount'] ?? null,
            'refundReason' => $data['reason'] ?? 'Refund request',
        ];

        $response = Http::withToken($token->token)
            ->withOptions(['headers' => $headers])
            ->post($url, $payload);

        if ($response->status() === 200 && $response['requestSuccessful'] === true) {
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
        return 'monnify';
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
        $required = ['email', 'amount', 'reference', 'name', 'contract_code', 'redirect_url'];
        
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
        return ['card', 'bank', 'bank_transfer'];
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
        $url = "{$this->baseUrl}/api/v1/auth/login";
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':' . $this->secretKey)
        ];

        $response = Http::withOptions(['headers' => $headers])->post($url, []);

        if ($response->status() === 200 && $response['requestSuccessful'] === true) {
            return (object) [
                'status' => true,
                'token' => $response['responseBody']['accessToken']
            ];
        }

        return (object) [
            'status' => false,
            'message' => 'Unable to generate Monnify access token'
        ];
    }

    /**
     * Format success response
     */
    protected function formatSuccessResponse($response, array $data): object
    {
        return (object) [
            'errors' => false,
            'message' => 'Payment initiated successfully with monnify',
            'data' => [
                'checkout_url' => $response['checkoutUrl'] ?? null,
                'reference' => $response['responseBody']['paymentReference'] ?? $data['reference'],
                'access_code' => $response['responseBody']['transactionReference'] ?? null,
                'provider' => 'monnify'
            ]
        ];
    }

    /**
     * Format verification response
     */
    protected function formatVerificationResponse($response, array $data): object
    {
        $transaction = $response['responseBody'];
        $status = strtolower($transaction['paymentStatus']) === 'paid' ? 'successful' : 'failed';

        return (object) [
            'errors' => false,
            'message' => 'Payment verified successfully with monnify',
            'provider' => 'monnify',
            'status' => $status,
            'amount' => $transaction['amountPaid'],
            'charged_amount' => $transaction['totalPayable'],
            'reference' => $transaction['paymentReference'],
            'provider_reference' => $transaction['transactionReference'],
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
            'message' => 'Refund processed successfully with monnify',
            'provider' => 'monnify',
            'refund_reference' => $response['responseBody']['refundReference'] ?? null,
            'amount' => $response['responseBody']['refundAmount'] ?? null,
            'status' => $response['responseBody']['status'] ?? 'pending',
            'data' => $response['responseBody']
        ];
    }

    /**
     * Format error response
     */
    protected function formatErrorResponse($response, string $message): object
    {
        $errorMessage = $response['responseMessage'] ?? $response['message'] ?? $message;
        $description = $response['responseMessage'] ?? 'Please check your payment details and try again';

        return (object) [
            'errors' => true,
            'message' => $errorMessage,
            'description' => $description,
            'provider' => 'monnify',
            'data' => $response->json()
        ];
    }
}
