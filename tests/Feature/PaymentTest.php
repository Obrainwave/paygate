<?php

namespace Obrainwave\Paygate\Tests\Feature;

use Tests\TestCase;
use Obrainwave\Paygate\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Obrainwave\Paygate\Events\PaymentInitiated;
use Obrainwave\Paygate\Events\PaymentCompleted;
use Obrainwave\Paygate\Events\PaymentFailed;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up test configuration
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

        $response = $this->postJson('/paygate/initiate', [
            'provider' => 'paystack',
            'amount' => 100,
            'email' => 'test@example.com',
            'reference' => 'TEST_REF_123',
            'redirect_url' => 'https://example.com/callback'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'errors',
                    'message',
                    'data' => [
                        'checkout_url',
                        'reference',
                        'access_code',
                        'provider'
                    ]
                ]);

        Event::assertDispatched(PaymentInitiated::class);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->postJson('/paygate/initiate', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['provider', 'amount', 'email']);
    }

    /** @test */
    public function it_stores_payment_in_database()
    {
        $this->postJson('/paygate/initiate', [
            'provider' => 'paystack',
            'amount' => 100,
            'email' => 'test@example.com',
            'reference' => 'TEST_REF_123',
            'redirect_url' => 'https://example.com/callback'
        ]);

        $this->assertDatabaseHas('payments', [
            'reference' => 'TEST_REF_123',
            'provider' => 'paystack',
            'amount' => 100,
            'customer_email' => 'test@example.com',
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function it_can_verify_payment()
    {
        // Create a payment record
        Payment::create([
            'reference' => 'TEST_REF_123',
            'provider' => 'paystack',
            'amount' => 100,
            'currency' => 'NGN',
            'status' => 'pending',
            'customer_email' => 'test@example.com',
            'initiated_at' => now(),
        ]);

        $response = $this->getJson('/paygate/verify/TEST_REF_123');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'errors',
                    'message',
                    'provider',
                    'status',
                    'amount',
                    'reference'
                ]);
    }

    /** @test */
    public function it_can_get_payment_status()
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

        $response = $this->getJson('/paygate/status/TEST_REF_123');

        $response->assertStatus(200)
                ->assertJson([
                    'errors' => false,
                    'status' => 'successful'
                ]);
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

        $response = $this->getJson('/paygate/history');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data',
                    'total',
                    'per_page',
                    'current_page',
                    'last_page'
                ]);
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

        $response = $this->getJson('/paygate/history?status=successful');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('successful', $data[0]['status']);
    }

    /** @test */
    public function it_can_get_available_gateways()
    {
        $response = $this->getJson('/api/paygate/gateways');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'errors',
                    'message',
                    'data' => [
                        '*' => [
                            'name',
                            'display_name',
                            'enabled',
                            'supported_methods',
                            'supported_currencies'
                        ]
                    ]
                ]);
    }

    /** @test */
    public function it_handles_webhook_correctly()
    {
        Event::fake();

        $webhookData = [
            'event' => 'charge.success',
            'data' => [
                'reference' => 'TEST_REF_123',
                'amount' => 10000,
                'status' => 'success'
            ]
        ];

        $response = $this->postJson('/paygate/webhook', $webhookData, [
            'X-Paystack-Signature' => 'test_signature'
        ]);

        $response->assertStatus(200)
                ->assertJson(['status' => 'success']);

        Event::assertDispatched(PaymentCompleted::class);
    }

    /** @test */
    public function it_requires_successful_payment_for_protected_routes()
    {
        // Create a failed payment
        Payment::create([
            'reference' => 'TEST_REF_123',
            'provider' => 'paystack',
            'amount' => 100,
            'currency' => 'NGN',
            'status' => 'failed',
            'customer_email' => 'test@example.com',
            'initiated_at' => now(),
            'failed_at' => now(),
        ]);

        $response = $this->getJson('/paygate/success/TEST_REF_123');

        $response->assertStatus(402)
                ->assertJson([
                    'errors' => true,
                    'message' => 'Payment not completed'
                ]);
    }

    /** @test */
    public function it_allows_access_to_protected_routes_with_successful_payment()
    {
        // Create a successful payment
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

        $response = $this->getJson('/paygate/success/TEST_REF_123');

        $response->assertStatus(200)
                ->assertJson([
                    'errors' => false,
                    'message' => 'Payment retrieved successfully'
                ]);
    }
}
