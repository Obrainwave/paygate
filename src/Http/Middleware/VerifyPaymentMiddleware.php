<?php

namespace Obrainwave\Paygate\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Obrainwave\Paygate\Contracts\PaymentServiceInterface;

class VerifyPaymentMiddleware
{
    protected PaymentServiceInterface $paymentService;

    public function __construct(PaymentServiceInterface $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $reference = $request->route('reference') ?? $request->get('reference');
        
        if (!$reference) {
            return response()->json([
                'errors' => true,
                'message' => 'Payment reference is required'
            ], 400);
        }

        // Check if payment exists and is successful
        $payment = $this->paymentService->getPaymentByReference($reference);
        
        if (!$payment) {
            return response()->json([
                'errors' => true,
                'message' => 'Payment not found'
            ], 404);
        }

        if ($payment->status !== 'successful') {
            return response()->json([
                'errors' => true,
                'message' => 'Payment not completed',
                'status' => $payment->status
            ], 402);
        }

        // Add payment data to request
        $request->merge(['payment_data' => $payment]);

        return $next($request);
    }
}
