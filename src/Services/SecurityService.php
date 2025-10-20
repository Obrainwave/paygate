<?php

namespace Obrainwave\Paygate\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

class SecurityService
{
    protected bool $encryptSensitiveData;
    protected bool $maskSensitiveLogs;

    public function __construct()
    {
        $this->encryptSensitiveData = config('paygate.encrypt_sensitive_data', true);
        $this->maskSensitiveLogs = config('paygate.mask_sensitive_logs', true);
    }

    /**
     * Encrypt sensitive payment data
     */
    public function encryptSensitiveData(array $data): array
    {
        if (!$this->encryptSensitiveData) {
            return $data;
        }

        $sensitiveFields = [
            'email',
            'phone_number',
            'card_number',
            'cvv',
            'expiry_month',
            'expiry_year',
            'account_number',
            'bank_code',
            'bvn'
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                try {
                    $data[$field] = Crypt::encryptString($data[$field]);
                } catch (\Exception $e) {
                    Log::error('Failed to encrypt sensitive data', [
                        'field' => $field,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        return $data;
    }

    /**
     * Decrypt sensitive payment data
     */
    public function decryptSensitiveData(array $data): array
    {
        if (!$this->encryptSensitiveData) {
            return $data;
        }

        $sensitiveFields = [
            'email',
            'phone_number',
            'card_number',
            'cvv',
            'expiry_month',
            'expiry_year',
            'account_number',
            'bank_code',
            'bvn'
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                try {
                    $data[$field] = Crypt::decryptString($data[$field]);
                } catch (\Exception $e) {
                    Log::error('Failed to decrypt sensitive data', [
                        'field' => $field,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        return $data;
    }

    /**
     * Mask sensitive data for logging
     */
    public function maskSensitiveData(array $data): array
    {
        if (!$this->maskSensitiveLogs) {
            return $data;
        }

        $sensitiveFields = [
            'email' => 'email',
            'phone_number' => 'phone',
            'card_number' => 'card',
            'cvv' => 'cvv',
            'account_number' => 'account',
            'bvn' => 'bvn',
            'secret_key' => 'secret',
            'api_key' => 'api_key',
            'webhook_secret' => 'webhook_secret'
        ];

        foreach ($sensitiveFields as $field => $type) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $data[$field] = $this->maskValue($data[$field], $type);
            }
        }

        return $data;
    }

    /**
     * Mask a specific value based on type
     */
    protected function maskValue(string $value, string $type): string
    {
        $length = strlen($value);
        
        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        switch ($type) {
            case 'email':
                $parts = explode('@', $value);
                if (count($parts) === 2) {
                    $username = $parts[0];
                    $domain = $parts[1];
                    $maskedUsername = substr($username, 0, 2) . str_repeat('*', max(0, strlen($username) - 4)) . substr($username, -2);
                    return $maskedUsername . '@' . $domain;
                }
                return substr($value, 0, 2) . str_repeat('*', $length - 4) . substr($value, -2);

            case 'phone':
                return substr($value, 0, 3) . str_repeat('*', $length - 6) . substr($value, -3);

            case 'card':
                return substr($value, 0, 4) . str_repeat('*', $length - 8) . substr($value, -4);

            case 'cvv':
                return str_repeat('*', $length);

            case 'account':
                return str_repeat('*', $length - 4) . substr($value, -4);

            case 'bvn':
                return str_repeat('*', $length - 4) . substr($value, -4);

            case 'secret':
            case 'api_key':
            case 'webhook_secret':
                return substr($value, 0, 4) . str_repeat('*', $length - 8) . substr($value, -4);

            default:
                return substr($value, 0, 2) . str_repeat('*', $length - 4) . substr($value, -2);
        }
    }

    /**
     * Validate webhook signature
     */
    public function validateWebhookSignature(string $payload, string $signature, string $secret): bool
    {
        if (empty($secret)) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Generate secure random string
     */
    public function generateSecureRandomString(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Generate payment reference
     */
    public function generatePaymentReference(string $prefix = 'TXN'): string
    {
        $timestamp = time();
        $random = $this->generateSecureRandomString(8);
        return strtoupper($prefix . '_' . $timestamp . '_' . $random);
    }

    /**
     * Validate payment amount
     */
    public function validatePaymentAmount(float $amount, string $currency = 'NGN'): bool
    {
        $minAmounts = [
            'NGN' => 1.00,
            'USD' => 0.01,
            'EUR' => 0.01,
            'GBP' => 0.01,
            'GHS' => 0.01,
            'KES' => 0.01,
            'ZAR' => 0.01
        ];

        $maxAmounts = [
            'NGN' => 1000000.00,
            'USD' => 10000.00,
            'EUR' => 10000.00,
            'GBP' => 10000.00,
            'GHS' => 10000.00,
            'KES' => 10000.00,
            'ZAR' => 10000.00
        ];

        $minAmount = $minAmounts[$currency] ?? 0.01;
        $maxAmount = $maxAmounts[$currency] ?? 10000.00;

        return $amount >= $minAmount && $amount <= $maxAmount;
    }

    /**
     * Validate email address
     */
    public function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate phone number
     */
    public function validatePhoneNumber(string $phone, string $country = 'NG'): bool
    {
        $patterns = [
            'NG' => '/^(\+234|234|0)?[789][01]\d{8}$/',
            'US' => '/^(\+1|1)?[2-9]\d{2}[2-9]\d{2}\d{4}$/',
            'GB' => '/^(\+44|44|0)?[1-9]\d{8,9}$/',
            'KE' => '/^(\+254|254|0)?[17]\d{8}$/',
            'GH' => '/^(\+233|233|0)?[2-9]\d{8}$/',
            'ZA' => '/^(\+27|27|0)?[6-8]\d{8}$/'
        ];

        $pattern = $patterns[$country] ?? $patterns['NG'];
        return preg_match($pattern, $phone) === 1;
    }

    /**
     * Sanitize input data
     */
    public function sanitizeInput(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Remove null bytes and control characters
                $value = str_replace(["\0", "\x00"], '', $value);
                $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
                
                // Trim whitespace
                $value = trim($value);
                
                // Escape HTML entities
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
            
            $sanitized[$key] = $value;
        }

        return $sanitized;
    }

    /**
     * Log security event
     */
    public function logSecurityEvent(string $event, array $data = []): void
    {
        $logData = [
            'event' => $event,
            'timestamp' => now()->toISOString(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'data' => $this->maskSensitiveData($data)
        ];

        Log::channel('security')->info('Security event', $logData);
    }

    /**
     * Check for suspicious activity
     */
    public function checkSuspiciousActivity(string $ip, string $email, float $amount): bool
    {
        // Check for multiple failed attempts from same IP
        $failedAttempts = Log::channel('security')
            ->where('context.ip', $ip)
            ->where('context.event', 'payment_failed')
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($failedAttempts > 10) {
            $this->logSecurityEvent('suspicious_activity', [
                'type' => 'multiple_failed_attempts',
                'ip' => $ip,
                'attempts' => $failedAttempts
            ]);
            return true;
        }

        // Check for unusually high amount
        if ($amount > 100000) {
            $this->logSecurityEvent('suspicious_activity', [
                'type' => 'high_amount',
                'amount' => $amount,
                'email' => $email
            ]);
            return true;
        }

        return false;
    }

    /**
     * Generate audit hash
     */
    public function generateAuditHash(array $data): string
    {
        $dataString = json_encode($data, JSON_SORT_KEYS);
        return hash('sha256', $dataString . config('app.key'));
    }

    /**
     * Verify audit hash
     */
    public function verifyAuditHash(array $data, string $hash): bool
    {
        $expectedHash = $this->generateAuditHash($data);
        return hash_equals($expectedHash, $hash);
    }
}
