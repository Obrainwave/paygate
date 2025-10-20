<?php

namespace Obrainwave\Paygate\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Add payment-specific headers
        $request->headers->set('X-Payment-Request', 'true');
        
        // Log payment requests if enabled
        if (config('paygate.enable_logging', true)) {
            \Log::info('Payment request received', [
                'url' => $request->url(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        $response = $next($request);

        // Add payment-specific response headers
        $response->headers->set('X-Payment-Response', 'true');
        $response->headers->set('X-Payment-Timestamp', now()->toISOString());

        return $response;
    }
}
