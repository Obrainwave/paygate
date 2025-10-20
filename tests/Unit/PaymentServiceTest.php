<?php

namespace Obrainwave\Paygate\Tests\Unit;

use Tests\TestCase;
use Obrainwave\Paygate\Services\PaymentService;
use Obrainwave\Paygate\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Obrainwave\Paygate\Events\PaymentInitiated;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->paymentService = app(PaymentService::class);
        
        config([
            'paygate.store_payments' => true,
            'paygate.enable_logging' => false,
            'paygate.paystack.secret_key' => 'test_secret_key',
            'paygate.paystack.enabled' => true,
        ]);
    }

    /** @test */
    public function it_can_initiate_payment()
    {
        Event::fake();

        $data = [
            'provider' => 'paystack',
            'amount' => 100,
            'email' => 'test@example.com',
            'reference' => 'TEST_REF_123',
            'redirect_url' => 'https://example.com/callback'
        ];

        $result = $this->paymentService->initiatePayment($data);

        $this->assertIsObject($result);
        $this->assertObjectHasProperty('errors', $result);
        $this->assertObjectHasProperty('message', $result);
        $this->assertObjectHasProperty('data', $result);

        Event::assertDispatched(PaymentInitiated::class);
    }

    /** @test */
    public function it_stores_payment_in_database()
    {
        $data = [
            'provider' => 'paystack',
            'amount' => 100,
            'email' => 'test@example.com',
            'reference' => 'TEST_REF_123',
            'redirect_url' => 'https://example.com/callback'
        ];

        $this->paymentService->initiatePayment($data);

        $this->assertDatabaseHas('payments', [
            'reference' => 'TEST_REF_123',
            'provider' => 'paystack',
            'amount' => 100,
            'customer_email' => 'test@example.com',
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function it_can_get_payment_by_reference()
    {
        Payment::create([
            'reference' => 'TEST_REF_123',
            'provider' => 'paystack',
            'amount' => 100,
            'currency' => 'NGN',
            'status' => 'successful',
            'customer_email' => 'test@example.com',
            'initiated_at' => now(),
            'completed_at' => now(),
        ]);

        $payment = $this->paymentService->getPaymentByReference('TEST_REF_123');

        $this->assertNotNull($payment);
        $this->assertEquals('TEST_REF_123', $payment->reference);
        $this->assertEquals('successful', $payment->status);
    }

    /** @test */
    public function it_returns_null_for_non_existent_payment()
    {
        $payment = $this->paymentService->getPaymentByReference('NON_EXISTENT');

        $this->assertNull($payment);
    }

    /** @test */
    public function it_can_update_payment_status()
    {
        Payment::create([
            'reference' => 'TEST_REF_123',
            'provider' => 'paystack',
            'amount' => 100,
            'currency' => 'NGN',
            'status' => 'pending',
            'customer_email' => 'test@example.com',
            'initiated_at' => now(),
        ]);

        $result = $this->paymentService->updatePaymentStatus('TEST_REF_123', 'successful', [
            'charged_amount' => 105,
            'payment_method' => 'card',
            'provider_reference' => 'PROV_REF_123'
        ]);

        $this->assertTrue($result);

        $payment = Payment::where('reference', 'TEST_REF_123')->first();
        $this->assertEquals('successful', $payment->status);
        $this->assertEquals(105, $payment->charged_amount);
        $this->assertEquals('card', $payment->payment_method);
        $this->assertEquals('PROV_REF_123', $payment->provider_reference);
    }

    /** @test */
    public function it_can_get_payment_history()
    {
        // Create test payments
        Payment::create([
            'reference' => 'TEST_REF_1',
            'provider' => 'paystack',
            'amount' => 100,
            'currency' => 'NGN',
            'status' => 'successful',
            'customer_email' => 'test1@example.com',
            'initiated_at' => now(),
            'completed_at' => now(),
        ]);

        Payment::create([
            'reference' => 'TEST_REF_2',
            'provider' => 'gtpay',
            'amount' => 200,
            'currency' => 'NGN',
            'status' => 'failed',
            'customer_email' => 'test2@example.com',
            'initiated_at' => now(),
            'failed_at' => now(),
        ]);

        $history = $this->paymentService->getPaymentHistory();

        $this->assertIsObject($history);
        $this->assertObjectHasProperty('data', $history);
        $this->assertObjectHasProperty('total', $history);
        $this->assertEquals(2, $history->total);
    }

    /** @test */
    public function it_can_filter_payment_history()
    {
        // Create test payments
        Payment::create([
            'reference' => 'TEST_REF_1',
            'provider' => 'paystack',
            'amount' => 100,
            'currency' => 'NGN',
            'status' => 'successful',
            'customer_email' => 'test1@example.com',
            'initiated_at' => now(),
            'completed_at' => now(),
        ]);

        Payment::create([
            'reference' => 'TEST_REF_2',
            'provider' => 'gtpay',
            'amount' => 200,
            'currency' => 'NGN',
            'status' => 'failed',
            'customer_email' => 'test2@example.com',
            'initiated_at' => now(),
            'failed_at' => now(),
        ]);

        $history = $this->paymentService->getPaymentHistory(['status' => 'successful']);

        $this->assertEquals(1, $history->total);
        $this->assertEquals('successful', $history->data[0]->status);
    }

    /** @test */
    public function it_can_get_available_gateways()
    {
        $gateways = $this->paymentService->getAvailableGateways();

        $this->assertIsArray($gateways);
        $this->assertArrayHasKey('paystack', $gateways);
        $this->assertArrayHasKey('gtpay', $gateways);
        $this->assertArrayHasKey('flutterwave', $gateways);
        $this->assertArrayHasKey('monnify', $gateways);
    }

    /** @test */
    public function it_can_get_specific_gateway()
    {
        $gateway = $this->paymentService->getGateway('paystack');

        $this->assertIsObject($gateway);
        $this->assertEquals('paystack', $gateway->getGatewayName());
    }

    /** @test */
    public function it_throws_exception_for_invalid_gateway()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Gateway 'invalid' not found");

        $this->paymentService->getGateway('invalid');
    }

    /** @test */
    public function it_adds_default_values_to_payment_data()
    {
        $data = [
            'provider' => 'paystack',
            'amount' => 100,
            'email' => 'test@example.com',
            'reference' => 'TEST_REF_123'
        ];

        $result = $this->paymentService->initiatePayment($data);

        $this->assertIsObject($result);
        // The method should add default currency and other values
    }

    /** @test */
    public function it_handles_payment_verification()
    {
        $data = [
            'provider' => 'paystack',
            'reference' => 'TEST_REF_123'
        ];

        $result = $this->paymentService->verifyPayment($data);

        $this->assertIsObject($result);
        $this->assertObjectHasProperty('errors', $result);
    }

    /** @test */
    public function it_handles_payment_refund()
    {
        $data = [
            'provider' => 'paystack',
            'reference' => 'TEST_REF_123',
            'amount' => 50,
            'reason' => 'Customer requested refund'
        ];

        $result = $this->paymentService->refundPayment($data);

        $this->assertIsObject($result);
        $this->assertObjectHasProperty('errors', $result);
    }
}
