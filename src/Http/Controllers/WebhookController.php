<?php

namespace Obrainwave\Paygate\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Obrainwave\Paygate\Contracts\PaymentServiceInterface;
use Obrainwave\Paygate\Events\PaymentCompleted;
use Obrainwave\Paygate\Events\PaymentFailed;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected PaymentServiceInterface $paymentService;

    public function __construct(PaymentServiceInterface $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Handle webhook from payment gateways
     */
    public function handle(Request $request): JsonResponse
    {
        $provider = $this->detectProvider($request);
        
        if (!$provider) {
            return response()->json(['error' => 'Unknown provider'], 400);
        }

        // Verify webhook signature
        if (!$this->verifySignature($request, $provider)) {
            Log::warning('Invalid webhook signature', [
                'provider' => $provider,
                'ip' => $request->ip()
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Process webhook based on provider
        $result = $this->processWebhook($request, $provider);

        if ($result) {
            // Dispatch appropriate events
            if ($result->status === 'successful') {
                event(new PaymentCompleted($result));
            } else {
                event(new PaymentFailed($result));
            }

            Log::info('Webhook processed successfully', [
                'provider' => $provider,
                'reference' => $result->reference ?? null,
                'status' => $result->status ?? null
            ]);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Detect payment provider from request
     */
    protected function detectProvider(Request $request): ?string
    {
        $userAgent = $request->userAgent();
        $headers = $request->headers->all();
        
        // Check for provider-specific headers or patterns
        if (str_contains($userAgent, 'paystack') || isset($headers['x-paystack-signature'])) {
            return 'paystack';
        }
        
        if (str_contains($userAgent, 'squad') || isset($headers['x-squad-signature'])) {
            return 'gtpay';
        }
        
        if (str_contains($userAgent, 'flutterwave') || isset($headers['x-flutterwave-signature'])) {
            return 'flutterwave';
        }
        
        if (str_contains($userAgent, 'monnify') || isset($headers['x-monnify-signature'])) {
            return 'monnify';
        }

        // Try to detect from request data
        $data = $request->all();
        if (isset($data['provider'])) {
            return $data['provider'];
        }

        return null;
    }

    /**
     * Verify webhook signature
     */
    protected function verifySignature(Request $request, string $provider): bool
    {
        $webhookSecret = config("paygate.{$provider}.webhook_secret");
        
        if (!$webhookSecret) {
            return true; // Skip verification if no secret is set
        }

        $signature = $request->header('X-' . ucfirst($provider) . '-Signature');
        $payload = $request->getContent();
        
        if (!$signature) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
        
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Process webhook data
     */
    protected function processWebhook(Request $request, string $provider): ?object
    {
        $data = $request->all();
        
        switch ($provider) {
            case 'paystack':
                return $this->processPaystackWebhook($data);
            case 'gtpay':
                return $this->processGtpayWebhook($data);
            case 'flutterwave':
                return $this->processFlutterwaveWebhook($data);
            case 'monnify':
                return $this->processMonnifyWebhook($data);
            default:
                return null;
        }
    }

    /**
     * Process Paystack webhook
     */
    protected function processPaystackWebhook(array $data): ?object
    {
        if (!isset($data['event']) || $data['event'] !== 'charge.success') {
            return null;
        }

        $transaction = $data['data'] ?? [];
        $reference = $transaction['reference'] ?? null;
        
        if (!$reference) {
            return null;
        }

        // Update payment status
        $this->paymentService->updatePaymentStatus($reference, 'successful', $transaction);

        return (object) [
            'reference' => $reference,
            'status' => 'successful',
            'provider' => 'paystack',
            'amount' => $transaction['amount'] / 100,
            'data' => $transaction
        ];
    }

    /**
     * Process GTPay webhook
     */
    protected function processGtpayWebhook(array $data): ?object
    {
        $reference = $data['transaction_ref'] ?? null;
        $status = $data['transaction_status'] ?? null;
        
        if (!$reference || !$status) {
            return null;
        }

        $paymentStatus = strtolower($status) === 'success' ? 'successful' : 'failed';
        
        // Update payment status
        $this->paymentService->updatePaymentStatus($reference, $paymentStatus, $data);

        return (object) [
            'reference' => $reference,
            'status' => $paymentStatus,
            'provider' => 'gtpay',
            'amount' => $data['transaction_amount'] / 100,
            'data' => $data
        ];
    }

    /**
     * Process Flutterwave webhook
     */
    protected function processFlutterwaveWebhook(array $data): ?object
    {
        $reference = $data['tx_ref'] ?? null;
        $status = $data['status'] ?? null;
        
        if (!$reference || !$status) {
            return null;
        }

        $paymentStatus = strtolower($status) === 'successful' ? 'successful' : 'failed';
        
        // Update payment status
        $this->paymentService->updatePaymentStatus($reference, $paymentStatus, $data);

        return (object) [
            'reference' => $reference,
            'status' => $paymentStatus,
            'provider' => 'flutterwave',
            'amount' => $data['amount'] ?? 0,
            'data' => $data
        ];
    }

    /**
     * Process Monnify webhook
     */
    protected function processMonnifyWebhook(array $data): ?object
    {
        $reference = $data['paymentReference'] ?? null;
        $status = $data['paymentStatus'] ?? null;
        
        if (!$reference || !$status) {
            return null;
        }

        $paymentStatus = strtolower($status) === 'paid' ? 'successful' : 'failed';
        
        // Update payment status
        $this->paymentService->updatePaymentStatus($reference, $paymentStatus, $data);

        return (object) [
            'reference' => $reference,
            'status' => $paymentStatus,
            'provider' => 'monnify',
            'amount' => $data['amountPaid'] ?? 0,
            'data' => $data
        ];
    }
}
