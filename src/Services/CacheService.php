<?php

namespace Obrainwave\Paygate\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheService
{
    protected int $ttl;
    protected bool $enabled;

    public function __construct()
    {
        $this->ttl = config('paygate.cache_ttl', 300);
        $this->enabled = config('paygate.enable_caching', false);
    }

    /**
     * Cache payment response
     */
    public function cachePaymentResponse(string $key, $data, ?int $ttl = null): void
    {
        if (!$this->enabled) {
            return;
        }

        $cacheKey = $this->getCacheKey($key);
        $cacheTtl = $ttl ?? $this->ttl;

        try {
            Cache::put($cacheKey, $data, $cacheTtl);
            
            if (config('paygate.enable_logging', true)) {
                Log::debug('Payment response cached', [
                    'key' => $cacheKey,
                    'ttl' => $cacheTtl
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to cache payment response', [
                'key' => $cacheKey,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get cached payment response
     */
    public function getCachedPaymentResponse(string $key)
    {
        if (!$this->enabled) {
            return null;
        }

        $cacheKey = $this->getCacheKey($key);

        try {
            $cached = Cache::get($cacheKey);
            
            if ($cached && config('paygate.enable_logging', true)) {
                Log::debug('Payment response retrieved from cache', [
                    'key' => $cacheKey
                ]);
            }

            return $cached;
        } catch (\Exception $e) {
            Log::error('Failed to retrieve cached payment response', [
                'key' => $cacheKey,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Cache gateway status
     */
    public function cacheGatewayStatus(string $gateway, bool $status, ?int $ttl = null): void
    {
        if (!$this->enabled) {
            return;
        }

        $cacheKey = $this->getCacheKey("gateway_status_{$gateway}");
        $cacheTtl = $ttl ?? 60; // 1 minute for gateway status

        try {
            Cache::put($cacheKey, $status, $cacheTtl);
        } catch (\Exception $e) {
            Log::error('Failed to cache gateway status', [
                'gateway' => $gateway,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get cached gateway status
     */
    public function getCachedGatewayStatus(string $gateway): ?bool
    {
        if (!$this->enabled) {
            return null;
        }

        $cacheKey = $this->getCacheKey("gateway_status_{$gateway}");

        try {
            return Cache::get($cacheKey);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve cached gateway status', [
                'gateway' => $gateway,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Cache payment methods for gateway
     */
    public function cachePaymentMethods(string $gateway, array $methods, ?int $ttl = null): void
    {
        if (!$this->enabled) {
            return;
        }

        $cacheKey = $this->getCacheKey("payment_methods_{$gateway}");
        $cacheTtl = $ttl ?? 3600; // 1 hour for payment methods

        try {
            Cache::put($cacheKey, $methods, $cacheTtl);
        } catch (\Exception $e) {
            Log::error('Failed to cache payment methods', [
                'gateway' => $gateway,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get cached payment methods for gateway
     */
    public function getCachedPaymentMethods(string $gateway): ?array
    {
        if (!$this->enabled) {
            return null;
        }

        $cacheKey = $this->getCacheKey("payment_methods_{$gateway}");

        try {
            return Cache::get($cacheKey);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve cached payment methods', [
                'gateway' => $gateway,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Clear payment cache
     */
    public function clearPaymentCache(string $key): void
    {
        if (!$this->enabled) {
            return;
        }

        $cacheKey = $this->getCacheKey($key);

        try {
            Cache::forget($cacheKey);
            
            if (config('paygate.enable_logging', true)) {
                Log::debug('Payment cache cleared', [
                    'key' => $cacheKey
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to clear payment cache', [
                'key' => $cacheKey,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Clear all payment caches
     */
    public function clearAllPaymentCaches(): void
    {
        if (!$this->enabled) {
            return;
        }

        try {
            $pattern = config('paygate.prefix', 'paygate') . '_*';
            Cache::forget($pattern);
            
            if (config('paygate.enable_logging', true)) {
                Log::debug('All payment caches cleared');
            }
        } catch (\Exception $e) {
            Log::error('Failed to clear all payment caches', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get cache key with prefix
     */
    protected function getCacheKey(string $key): string
    {
        $prefix = config('paygate.prefix', 'paygate');
        return "{$prefix}_{$key}";
    }

    /**
     * Check if caching is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get cache TTL
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }
}
