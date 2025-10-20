<?php

namespace Obrainwave\Paygate;

use Obrainwave\Paygate\Contracts\PaymentServiceInterface;
use Obrainwave\Paygate\Traits\InitiateTrait;
use Obrainwave\Paygate\Traits\VerifyTrait;

class PaygateManager
{
    use InitiateTrait, VerifyTrait;

    protected PaymentServiceInterface $paymentService;

    public function __construct(PaymentServiceInterface $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Initiate a payment using the new service
     */
    public function initiatePayment($data)
    {
        return $this->paymentService->initiatePayment($data);
    }

    /**
     * Verify a payment using the new service
     */
    public function verifyPayment($data)
    {
        return $this->paymentService->verifyPayment($data);
    }

    /**
     * Refund a payment
     */
    public function refundPayment($data)
    {
        return $this->paymentService->refundPayment($data);
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus($reference)
    {
        return $this->paymentService->getPaymentStatus($reference);
    }

    /**
     * Get payment by reference
     */
    public function getPaymentByReference($reference)
    {
        return $this->paymentService->getPaymentByReference($reference);
    }

    /**
     * Get payment history
     */
    public function getPaymentHistory($filters = [])
    {
        return $this->paymentService->getPaymentHistory($filters);
    }

    /**
     * Get available gateways
     */
    public function getAvailableGateways()
    {
        return $this->paymentService->getAvailableGateways();
    }

    /**
     * Legacy methods for backward compatibility
     */
    public function initiate($data)
    {
        return $this->initiatePayment($data);
    }

    public function verify($data)
    {
        return $this->verifyPayment($data);
    }
}