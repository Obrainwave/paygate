<?php

namespace Obrainwave\Paygate\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Obrainwave\Paygate\Http\Controllers\Controller;
use Obrainwave\Paygate\Contracts\PaymentServiceInterface;
use Obrainwave\Paygate\Events\PaymentInitiated;
use Obrainwave\Paygate\Events\PaymentCompleted;
use Obrainwave\Paygate\Events\PaymentFailed;

class PaymentController extends Controller
{
    protected PaymentServiceInterface $paymentService;

    public function __construct(PaymentServiceInterface $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Initiate a payment
     */
    public function initiate(Request $request): JsonResponse
    {
        $request->validate([
            'provider' => 'required|string|in:paystack,gtpay,flutterwave,monnify',
            'amount' => 'required|numeric|min:0.01',
            'email' => 'required|email',
            'currency' => 'string|in:NGN,USD,EUR,GBP,GHS,KES,ZAR',
            'name' => 'string|max:255',
            'phone_number' => 'string|max:20',
            'reference' => 'string|max:255',
            'redirect_url' => 'url',
        ]);

        $data = $request->only([
            'provider', 'amount', 'email', 'currency', 'name', 
            'phone_number', 'reference', 'redirect_url'
        ]);

        // Add provider token from config
        $data['provider_token'] = $this->getProviderToken($data['provider']);

        $result = $this->paymentService->initiatePayment($data);

        if (!$result->errors) {
            event(new PaymentInitiated($result));
        }

        return response()->json($result);
    }

    /**
     * Verify a payment
     */
    public function verify(string $reference): JsonResponse
    {
        $provider = request()->get('provider', config('paygate.default_provider'));
        
        $data = [
            'provider' => $provider,
            'reference' => $reference,
            'provider_token' => $this->getProviderToken($provider),
        ];

        $result = $this->paymentService->verifyPayment($data);

        if (!$result->errors) {
            if ($result->status === 'successful') {
                event(new PaymentCompleted($result));
            } else {
                event(new PaymentFailed($result));
            }
        }

        return response()->json($result);
    }

    /**
     * Refund a payment
     */
    public function refund(Request $request): JsonResponse
    {
        $request->validate([
            'provider' => 'required|string|in:paystack,gtpay,flutterwave,monnify',
            'reference' => 'required|string',
            'amount' => 'numeric|min:0.01',
            'reason' => 'string|max:255',
        ]);

        $data = $request->only(['provider', 'reference', 'amount', 'reason']);
        $data['provider_token'] = $this->getProviderToken($data['provider']);

        $result = $this->paymentService->refundPayment($data);

        return response()->json($result);
    }

    /**
     * Get payment status
     */
    public function status(string $reference): JsonResponse
    {
        $result = $this->paymentService->getPaymentStatus($reference);
        return response()->json($result);
    }

    /**
     * Get payment history
     */
    public function history(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'provider', 'customer_email', 'date_from', 'date_to']);
        $result = $this->paymentService->getPaymentHistory($filters);

        return response()->json($result);
    }

    /**
     * Get available gateways
     */
    public function gateways(): JsonResponse
    {
        $gateways = $this->paymentService->getAvailableGateways();
        
        $result = [];
        foreach ($gateways as $name => $gateway) {
            $result[] = [
                'name' => $name,
                'display_name' => ucfirst($name),
                'enabled' => $gateway->isEnabled(),
                'supported_methods' => $gateway->getSupportedPaymentMethods(),
                'supported_currencies' => $gateway->getSupportedCurrencies(),
            ];
        }

        return response()->json([
            'errors' => false,
            'message' => 'Available gateways retrieved successfully',
            'data' => $result
        ]);
    }

    /**
     * Payment success endpoint
     */
    public function success(string $reference): JsonResponse
    {
        $payment = $this->paymentService->getPaymentByReference($reference);
        
        if (!$payment) {
            return response()->json([
                'errors' => true,
                'message' => 'Payment not found'
            ], 404);
        }

        return response()->json([
            'errors' => false,
            'message' => 'Payment retrieved successfully',
            'data' => $payment
        ]);
    }

    /**
     * Get provider token
     */
    protected function getProviderToken(string $provider): string
    {
        $configKey = "paygate.{$provider}.secret_key";
        return config($configKey) ?? '';
    }
}
