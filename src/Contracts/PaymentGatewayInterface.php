<?php

namespace Obrainwave\Paygate\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Initialize a payment transaction
     */
    public function initiatePayment(array $data): object;

    /**
     * Verify a payment transaction
     */
    public function verifyPayment(array $data): object;

    /**
     * Refund a payment transaction
     */
    public function refundPayment(array $data): object;

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $reference): object;

    /**
     * Get gateway name
     */
    public function getGatewayName(): string;

    /**
     * Check if gateway is enabled
     */
    public function isEnabled(): bool;

    /**
     * Validate payment data
     */
    public function validatePaymentData(array $data): object;

    /**
     * Get supported payment methods
     */
    public function getSupportedPaymentMethods(): array;

    /**
     * Get supported currencies
     */
    public function getSupportedCurrencies(): array;
}
