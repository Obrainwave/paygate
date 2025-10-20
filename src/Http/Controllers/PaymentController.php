<?php

namespace Obrainwave\Paygate\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
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
     * Payment callback
     */
    public function callback(Request $request): View
    {
        $reference = $request->get('reference');
        $provider = $request->get('provider', config('paygate.default_provider'));

        if ($reference) {
            $data = [
                'provider' => $provider,
                'reference' => $reference,
                'provider_token' => $this->getProviderToken($provider),
            ];

            $result = $this->paymentService->verifyPayment($data);

            return view('paygate::callback', compact('result'));
        }

        return view('paygate::callback', ['result' => null]);
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
    public function history(Request $request): View|JsonResponse
    {
        $filters = $request->only(['status', 'provider', 'customer_email', 'date_from', 'date_to']);
        $result = $this->paymentService->getPaymentHistory($filters);

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        return view('paygate::history', compact('result'));
    }

    /**
     * Payment success page
     */
    public function success(string $reference): View
    {
        $payment = $this->paymentService->getPaymentByReference($reference);
        
        if (!$payment) {
            abort(404, 'Payment not found');
        }

        return view('paygate::success', compact('payment'));
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
