<?php

namespace Obrainwave\Paygate\Services;

use Obrainwave\Paygate\Contracts\PaymentServiceInterface;
use Obrainwave\Paygate\Contracts\PaymentGatewayInterface;
use Obrainwave\Paygate\Models\Payment;
use Obrainwave\Paygate\Services\Gateways\PaystackService;
use Obrainwave\Paygate\Services\Gateways\GtpayService;
use Obrainwave\Paygate\Services\Gateways\FlutterwaveService;
use Obrainwave\Paygate\Services\Gateways\MonnifyService;
use Obrainwave\Paygate\Services\Gateways\InterswitchService;
use Obrainwave\Paygate\Services\Gateways\RemitaService;
use Obrainwave\Paygate\Services\Gateways\VtpassService;
use Obrainwave\Paygate\Services\CacheService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PaymentService implements PaymentServiceInterface
{
    protected array $gateways = [];
    protected CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
        $this->registerGateways();
    }

    /**
     * Register available payment gateways
     */
    protected function registerGateways(): void
    {
        $this->gateways = [
            'paystack' => new PaystackService(),
            'gtpay' => new GtpayService(),
            'flutterwave' => new FlutterwaveService(),
            'monnify' => new MonnifyService(),
            'interswitch' => new InterswitchService(),
            'remita' => new RemitaService(),
            'vtpass' => new VtpassService(),
        ];
    }

    /**
     * Initiate a payment
     */
    public function initiatePayment(array $data): object
    {
        $provider = $data['provider'] ?? config('paygate.default_provider');
        $gateway = $this->getGateway($provider);

        // Validate payment data
        $validation = $gateway->validatePaymentData($data);
        if (!$validation->status) {
            return $validation;
        }

        // Add default values
        $data = $this->addDefaultValues($data);

        // Check cache first
        $cacheKey = "payment_initiate_{$provider}_" . md5(serialize($data));
        $cachedResult = $this->cacheService->getCachedPaymentResponse($cacheKey);
        
        if ($cachedResult) {
            return $cachedResult;
        }

        // Initiate payment with gateway
        $result = $gateway->initiatePayment($data);

        // Cache successful responses
        if (!$result->errors) {
            $this->cacheService->cachePaymentResponse($cacheKey, $result, 300); // 5 minutes
        }

        // Store payment if enabled
        if (config('paygate.store_payments', true) && !$result->errors) {
            $this->storePayment($data, $result);
        }

        // Log payment initiation
        if (config('paygate.enable_logging', true)) {
            Log::info('Payment initiated', [
                'provider' => $provider,
                'reference' => $data['reference'] ?? null,
                'amount' => $data['amount'] ?? null,
            ]);
        }

        return $result;
    }

    /**
     * Verify a payment
     */
    public function verifyPayment(array $data): object
    {
        $provider = $data['provider'] ?? config('paygate.default_provider');
        $gateway = $this->getGateway($provider);

        // Verify payment with gateway
        $result = $gateway->verifyPayment($data);

        // Update payment status if stored
        if (config('paygate.store_payments', true) && !$result->errors) {
            $this->updatePaymentStatus(
                $data['reference'],
                $result->status ?? 'failed',
                (array) $result
            );
        }

        // Log payment verification
        if (config('paygate.enable_logging', true)) {
            Log::info('Payment verified', [
                'provider' => $provider,
                'reference' => $data['reference'] ?? null,
                'status' => $result->status ?? 'unknown',
            ]);
        }

        return $result;
    }

    /**
     * Refund a payment
     */
    public function refundPayment(array $data): object
    {
        $provider = $data['provider'] ?? config('paygate.default_provider');
        $gateway = $this->getGateway($provider);

        // Process refund with gateway
        $result = $gateway->refundPayment($data);

        // Update payment status if stored
        if (config('paygate.store_payments', true) && !$result->errors) {
            $this->updatePaymentStatus(
                $data['reference'],
                'refunded',
                (array) $result
            );
        }

        return $result;
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $reference): object
    {
        $gateway = $this->getGateway(config('paygate.default_provider'));
        return $gateway->getPaymentStatus($reference);
    }

    /**
     * Get payment by reference
     */
    public function getPaymentByReference(string $reference): ?object
    {
        if (!config('paygate.store_payments', true)) {
            return null;
        }

        $payment = Payment::byReference($reference)->first();
        return $payment ? (object) $payment->toArray() : null;
    }

    /**
     * Store payment in database
     */
    public function storePayment(array $data, object $result): object
    {
        $paymentData = [
            'reference' => $data['reference'],
            'provider' => $data['provider'],
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? config('paygate.default_currency'),
            'status' => 'pending',
            'customer_email' => $data['email'],
            'customer_name' => $data['name'] ?? null,
            'customer_phone' => $data['phone_number'] ?? null,
            'redirect_url' => $data['redirect_url'] ?? null,
            'checkout_url' => $result->data->checkout_url ?? null,
            'access_code' => $result->data->access_code ?? null,
            'metadata' => $this->extractMetadata($data),
            'provider_response' => (array) $result,
            'initiated_at' => now(),
        ];

        $payment = Payment::create($paymentData);
        return (object) $payment->toArray();
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(string $reference, string $status, array $data = []): bool
    {
        if (!config('paygate.store_payments', true)) {
            return false;
        }

        $payment = Payment::byReference($reference)->first();
        if (!$payment) {
            return false;
        }

        $updateData = [
            'status' => $status,
            'provider_response' => array_merge($payment->provider_response ?? [], $data),
        ];

        if ($status === 'successful') {
            $updateData['completed_at'] = now();
            $updateData['charged_amount'] = $data['charged_amount'] ?? $payment->amount;
            $updateData['payment_method'] = $data['payment_method'] ?? null;
            $updateData['provider_reference'] = $data['provider_reference'] ?? null;
        } elseif ($status === 'failed') {
            $updateData['failed_at'] = now();
        }

        return $payment->update($updateData);
    }

    /**
     * Get payment history
     */
    public function getPaymentHistory(array $filters = []): object
    {
        if (!config('paygate.store_payments', true)) {
            return (object) ['data' => [], 'total' => 0];
        }

        $query = Payment::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['provider'])) {
            $query->where('provider', $filters['provider']);
        }

        if (isset($filters['customer_email'])) {
            $query->where('customer_email', $filters['customer_email']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        $payments = $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);

        return (object) [
            'data' => $payments->items(),
            'total' => $payments->total(),
            'per_page' => $payments->perPage(),
            'current_page' => $payments->currentPage(),
            'last_page' => $payments->lastPage(),
        ];
    }

    /**
     * Get available gateways
     */
    public function getAvailableGateways(): array
    {
        return array_filter($this->gateways, function ($gateway) {
            return $gateway->isEnabled();
        });
    }

    /**
     * Get gateway by name
     */
    public function getGateway(string $name): PaymentGatewayInterface
    {
        if (!isset($this->gateways[$name])) {
            throw new \InvalidArgumentException("Gateway '{$name}' not found");
        }

        return $this->gateways[$name];
    }

    /**
     * Add default values to payment data
     */
    protected function addDefaultValues(array $data): array
    {
        $data['currency'] = $data['currency'] ?? config('paygate.default_currency');
        $data['provider'] = $data['provider'] ?? config('paygate.default_provider');
        
        return $data;
    }

    /**
     * Extract metadata from payment data
     */
    protected function extractMetadata(array $data): array
    {
        $metadata = [];
        $allowedFields = ['payment_methods', 'contract_code', 'pass_charge', 'title', 'logo'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $metadata[$field] = $data[$field];
            }
        }
        
        return $metadata;
    }
}
