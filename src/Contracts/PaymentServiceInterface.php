<?php

namespace Obrainwave\Paygate\Contracts;

interface PaymentServiceInterface
{
    /**
     * Initiate a payment
     */
    public function initiatePayment(array $data): object;

    /**
     * Verify a payment
     */
    public function verifyPayment(array $data): object;

    /**
     * Refund a payment
     */
    public function refundPayment(array $data): object;

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $reference): object;

    /**
     * Get payment by reference
     */
    public function getPaymentByReference(string $reference): ?object;

    /**
     * Store payment in database
     */
    public function storePayment(array $data): object;

    /**
     * Update payment status
     */
    public function updatePaymentStatus(string $reference, string $status, array $data = []): bool;

    /**
     * Get payment history
     */
    public function getPaymentHistory(array $filters = []): object;

    /**
     * Get available gateways
     */
    public function getAvailableGateways(): array;

    /**
     * Get gateway by name
     */
    public function getGateway(string $name): PaymentGatewayInterface;
}
