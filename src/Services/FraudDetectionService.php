<?php

namespace Obrainwave\Paygate\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FraudDetectionService
{
    protected SecurityService $securityService;
    protected array $riskRules = [];

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
        $this->initializeRiskRules();
    }

    /**
     * Initialize fraud detection rules
     */
    protected function initializeRiskRules(): void
    {
        $this->riskRules = [
            'high_amount' => [
                'threshold' => 100000,
                'weight' => 3,
                'description' => 'High transaction amount'
            ],
            'rapid_transactions' => [
                'threshold' => 5,
                'timeframe' => 300, // 5 minutes
                'weight' => 4,
                'description' => 'Multiple transactions in short time'
            ],
            'unusual_hours' => [
                'start_hour' => 2,
                'end_hour' => 6,
                'weight' => 2,
                'description' => 'Transaction during unusual hours'
            ],
            'new_email_domain' => [
                'threshold' => 1,
                'timeframe' => 3600, // 1 hour
                'weight' => 3,
                'description' => 'New email domain detected'
            ],
            'suspicious_ip' => [
                'threshold' => 3,
                'timeframe' => 3600, // 1 hour
                'weight' => 5,
                'description' => 'Multiple transactions from same IP'
            ],
            'card_velocity' => [
                'threshold' => 3,
                'timeframe' => 1800, // 30 minutes
                'weight' => 4,
                'description' => 'Multiple card transactions'
            ]
        ];
    }

    /**
     * Analyze payment for fraud risk
     */
    public function analyzePayment(array $paymentData): array
    {
        $riskScore = 0;
        $riskFactors = [];
        $recommendation = 'approve';

        // Check high amount
        if (isset($paymentData['amount']) && $paymentData['amount'] > $this->riskRules['high_amount']['threshold']) {
            $riskScore += $this->riskRules['high_amount']['weight'];
            $riskFactors[] = $this->riskRules['high_amount']['description'];
        }

        // Check rapid transactions
        $rapidTransactions = $this->checkRapidTransactions($paymentData);
        if ($rapidTransactions > $this->riskRules['rapid_transactions']['threshold']) {
            $riskScore += $this->riskRules['rapid_transactions']['weight'];
            $riskFactors[] = $this->riskRules['rapid_transactions']['description'];
        }

        // Check unusual hours
        if ($this->isUnusualHours()) {
            $riskScore += $this->riskRules['unusual_hours']['weight'];
            $riskFactors[] = $this->riskRules['unusual_hours']['description'];
        }

        // Check new email domain
        if (isset($paymentData['email']) && $this->isNewEmailDomain($paymentData['email'])) {
            $riskScore += $this->riskRules['new_email_domain']['weight'];
            $riskFactors[] = $this->riskRules['new_email_domain']['description'];
        }

        // Check suspicious IP
        $ipTransactions = $this->checkSuspiciousIP();
        if ($ipTransactions > $this->riskRules['suspicious_ip']['threshold']) {
            $riskScore += $this->riskRules['suspicious_ip']['weight'];
            $riskFactors[] = $this->riskRules['suspicious_ip']['description'];
        }

        // Check card velocity
        if (isset($paymentData['card_number']) && $this->checkCardVelocity($paymentData['card_number'])) {
            $riskScore += $this->riskRules['card_velocity']['weight'];
            $riskFactors[] = $this->riskRules['card_velocity']['description'];
        }

        // Determine recommendation based on risk score
        if ($riskScore >= 10) {
            $recommendation = 'decline';
        } elseif ($riskScore >= 6) {
            $recommendation = 'review';
        } elseif ($riskScore >= 3) {
            $recommendation = 'monitor';
        }

        $result = [
            'risk_score' => $riskScore,
            'risk_factors' => $riskFactors,
            'recommendation' => $recommendation,
            'timestamp' => now()->toISOString()
        ];

        // Log fraud analysis
        $this->logFraudAnalysis($paymentData, $result);

        return $result;
    }

    /**
     * Check for rapid transactions
     */
    protected function checkRapidTransactions(array $paymentData): int
    {
        $cacheKey = 'fraud_rapid_transactions_' . request()->ip();
        $timeframe = $this->riskRules['rapid_transactions']['timeframe'];
        
        $transactions = Cache::get($cacheKey, []);
        $now = time();
        
        // Remove old transactions
        $transactions = array_filter($transactions, function($timestamp) use ($now, $timeframe) {
            return ($now - $timestamp) <= $timeframe;
        });
        
        // Add current transaction
        $transactions[] = $now;
        
        // Cache updated transactions
        Cache::put($cacheKey, $transactions, $timeframe);
        
        return count($transactions);
    }

    /**
     * Check if transaction is during unusual hours
     */
    protected function isUnusualHours(): bool
    {
        $currentHour = (int) now()->format('H');
        $startHour = $this->riskRules['unusual_hours']['start_hour'];
        $endHour = $this->riskRules['unusual_hours']['end_hour'];
        
        return $currentHour >= $startHour && $currentHour <= $endHour;
    }

    /**
     * Check if email domain is new
     */
    protected function isNewEmailDomain(string $email): bool
    {
        $domain = substr(strrchr($email, "@"), 1);
        $cacheKey = 'fraud_email_domain_' . $domain;
        $timeframe = $this->riskRules['new_email_domain']['timeframe'];
        
        $isNew = !Cache::has($cacheKey);
        
        if ($isNew) {
            Cache::put($cacheKey, true, $timeframe);
        }
        
        return $isNew;
    }

    /**
     * Check for suspicious IP activity
     */
    protected function checkSuspiciousIP(): int
    {
        $ip = request()->ip();
        $cacheKey = 'fraud_ip_transactions_' . $ip;
        $timeframe = $this->riskRules['suspicious_ip']['timeframe'];
        
        $transactions = Cache::get($cacheKey, []);
        $now = time();
        
        // Remove old transactions
        $transactions = array_filter($transactions, function($timestamp) use ($now, $timeframe) {
            return ($now - $timestamp) <= $timeframe;
        });
        
        // Add current transaction
        $transactions[] = $now;
        
        // Cache updated transactions
        Cache::put($cacheKey, $transactions, $timeframe);
        
        return count($transactions);
    }

    /**
     * Check card velocity
     */
    protected function checkCardVelocity(string $cardNumber): bool
    {
        $maskedCard = $this->securityService->maskValue($cardNumber, 'card');
        $cacheKey = 'fraud_card_velocity_' . md5($maskedCard);
        $timeframe = $this->riskRules['card_velocity']['timeframe'];
        $threshold = $this->riskRules['card_velocity']['threshold'];
        
        $transactions = Cache::get($cacheKey, []);
        $now = time();
        
        // Remove old transactions
        $transactions = array_filter($transactions, function($timestamp) use ($now, $timeframe) {
            return ($now - $timestamp) <= $timeframe;
        });
        
        // Add current transaction
        $transactions[] = $now;
        
        // Cache updated transactions
        Cache::put($cacheKey, $transactions, $timeframe);
        
        return count($transactions) > $threshold;
    }

    /**
     * Log fraud analysis
     */
    protected function logFraudAnalysis(array $paymentData, array $result): void
    {
        $logData = [
            'payment_reference' => $paymentData['reference'] ?? null,
            'amount' => $paymentData['amount'] ?? null,
            'email' => $paymentData['email'] ?? null,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'risk_score' => $result['risk_score'],
            'risk_factors' => $result['risk_factors'],
            'recommendation' => $result['recommendation']
        ];

        if ($result['risk_score'] >= 6) {
            Log::channel('security')->warning('High risk payment detected', $logData);
        } else {
            Log::channel('security')->info('Payment fraud analysis', $logData);
        }
    }

    /**
     * Get fraud statistics
     */
    public function getFraudStatistics(int $days = 30): array
    {
        $startDate = now()->subDays($days);
        
        // This would typically query a database for actual statistics
        // For now, return mock data
        return [
            'total_transactions' => 1000,
            'high_risk_transactions' => 25,
            'declined_transactions' => 15,
            'reviewed_transactions' => 10,
            'fraud_rate' => 2.5,
            'period' => $days . ' days'
        ];
    }

    /**
     * Update risk rules
     */
    public function updateRiskRules(array $rules): void
    {
        $this->riskRules = array_merge($this->riskRules, $rules);
        
        Log::info('Fraud detection rules updated', [
            'rules' => $rules
        ]);
    }

    /**
     * Get current risk rules
     */
    public function getRiskRules(): array
    {
        return $this->riskRules;
    }

    /**
     * Whitelist IP address
     */
    public function whitelistIP(string $ip): void
    {
        $whitelist = Cache::get('fraud_whitelist_ips', []);
        $whitelist[] = $ip;
        Cache::put('fraud_whitelist_ips', array_unique($whitelist), 86400 * 30); // 30 days
        
        Log::info('IP address whitelisted', ['ip' => $ip]);
    }

    /**
     * Blacklist IP address
     */
    public function blacklistIP(string $ip): void
    {
        $blacklist = Cache::get('fraud_blacklist_ips', []);
        $blacklist[] = $ip;
        Cache::put('fraud_blacklist_ips', array_unique($blacklist), 86400 * 30); // 30 days
        
        Log::info('IP address blacklisted', ['ip' => $ip]);
    }

    /**
     * Check if IP is whitelisted
     */
    public function isIPWhitelisted(string $ip): bool
    {
        $whitelist = Cache::get('fraud_whitelist_ips', []);
        return in_array($ip, $whitelist);
    }

    /**
     * Check if IP is blacklisted
     */
    public function isIPBlacklisted(string $ip): bool
    {
        $blacklist = Cache::get('fraud_blacklist_ips', []);
        return in_array($ip, $blacklist);
    }
}
