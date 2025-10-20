# Migration Guide: v1.x to v2.0.0

This guide will help you migrate from Paygate v1.x to v2.0.0. While v2.0.0 maintains backward compatibility, there are several new features and improvements that you should be aware of.

## 📋 Migration Checklist

- [ ] Update Composer dependencies
- [ ] Run installation command
- [ ] Update environment variables
- [ ] Review configuration changes
- [ ] Update code to use new features
- [ ] Test thoroughly
- [ ] Deploy to production

## 🔄 Step-by-Step Migration

### 1. Update Composer Dependencies

```bash
# Update to v2.0.0
composer require obrainwave/paygate:^2.0

# Or update existing installation
composer update obrainwave/paygate
```

### 2. Run Installation Command

```bash
# This will publish new config and migrations
php artisan paygate:install
```

### 3. Update Environment Variables

Add new environment variables to your `.env` file:

```env
# New in v2.0.0 - Security & Performance
PAYGATE_ENCRYPT_SENSITIVE_DATA=true
PAYGATE_MASK_SENSITIVE_LOGS=true
PAYGATE_ENABLE_CACHING=true
PAYGATE_CACHE_TTL=300
PAYGATE_FRAUD_DETECTION=true
PAYGATE_RISK_THRESHOLD=6

# New Payment Gateways
INTERSWITCH_CLIENT_ID=your_interswitch_client_id
INTERSWITCH_CLIENT_SECRET=your_interswitch_client_secret
INTERSWITCH_WEBHOOK_SECRET=your_interswitch_webhook_secret

REMITA_MERCHANT_ID=your_remita_merchant_id
REMITA_API_KEY=your_remita_api_key
REMITA_SERVICE_TYPE_ID=your_remita_service_type_id
REMITA_WEBHOOK_SECRET=your_remita_webhook_secret

VTPASS_API_KEY=your_vtpass_api_key
VTPASS_SECRET_KEY=your_vtpass_secret_key
VTPASS_WEBHOOK_SECRET=your_vtpass_webhook_secret
```

### 4. Review Configuration Changes

The configuration file has been significantly enhanced. Review the new options:

```php
// config/paygate.php
return [
    // ... existing configuration ...
    
    // New in v2.0.0
    'encrypt_sensitive_data' => env('PAYGATE_ENCRYPT_SENSITIVE_DATA', true),
    'mask_sensitive_logs' => env('PAYGATE_MASK_SENSITIVE_LOGS', true),
    'enable_caching' => env('PAYGATE_ENABLE_CACHING', false),
    'cache_ttl' => env('PAYGATE_CACHE_TTL', 300),
    'fraud_detection' => [
        'enabled' => env('PAYGATE_FRAUD_DETECTION', true),
        'risk_threshold' => env('PAYGATE_RISK_THRESHOLD', 6),
    ],
    'plugins' => [
        // Plugin configuration
    ],
];
```

## 🔧 Code Changes

### Backward Compatibility

The package maintains backward compatibility with v1.x API:

```php
// This still works in v2.0.0
$payment = Paygate::initiate($payload);
$verification = Paygate::verify($reference);
```

### New Service Layer (Recommended)

While the old API still works, we recommend using the new service layer:

```php
// Old way (still works)
$payment = Paygate::initiate($payload);

// New way (recommended)
$payment = Paygate::initiatePayment($payload);
$verification = Paygate::verifyPayment($reference);
$refund = Paygate::refundPayment($refundData);
```

### Dependency Injection

You can now inject the payment service:

```php
use Obrainwave\Paygate\Contracts\PaymentServiceInterface;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentServiceInterface $paymentService
    ) {}
    
    public function initiate(Request $request)
    {
        $result = $this->paymentService->initiatePayment($request->all());
        return response()->json($result);
    }
}
```

## 🆕 New Features

### 1. Database Integration

Payments are now automatically stored in the database:

```php
// Get payment by reference
$payment = Paygate::getPaymentByReference('TXN_1234567890');

// Get payment history
$history = Paygate::getPaymentHistory([
    'status' => 'successful',
    'provider' => 'paystack'
]);

// Use Eloquent model directly
use Obrainwave\Paygate\Models\Payment;

$payments = Payment::successful()
    ->byProvider('paystack')
    ->where('amount', '>', 100)
    ->get();
```

### 2. Laravel Events

Listen to payment lifecycle events:

```php
// In your EventServiceProvider
protected $listen = [
    PaymentInitiated::class => [
        SendPaymentEmail::class,
    ],
    PaymentCompleted::class => [
        UpdateOrderStatus::class,
        SendConfirmationEmail::class,
    ],
    PaymentFailed::class => [
        LogPaymentFailure::class,
        SendFailureNotification::class,
    ],
];
```

### 3. Webhook Handling

Enhanced webhook processing with signature verification:

```php
// Webhook routes are automatically registered
// POST /api/paygate/webhook
// POST /paygate/webhook

// Custom webhook handling
Route::post('/custom-webhook', function(Request $request) {
    $webhookController = new WebhookController();
    return $webhookController->handle($request);
});
```

### 4. JavaScript SDK

Complete client-side integration:

```html
<!-- Include JavaScript SDK -->
<script src="{{ asset('vendor/paygate/js/paygate.js') }}"></script>
<link rel="stylesheet" href="{{ asset('vendor/paygate/css/paygate.css') }}">

<!-- Auto-initialize payment form -->
<form data-paygate-form>
    <input type="email" name="email" required>
    <input type="number" name="amount" required>
    <button type="submit">Pay Now</button>
</form>
```

### 5. Plugin System

Extensible architecture for custom features:

```php
// Create a custom plugin
class MyPlugin implements PluginInterface
{
    public function registerHooks(): array
    {
        return ['payment.completed'];
    }
    
    public function handleHook(string $hook, array $data = []): mixed
    {
        // Custom logic here
        return null;
    }
}

// Register the plugin
$pluginManager = app(PluginManager::class);
$pluginManager->registerPlugin(new MyPlugin());
```

### 6. Security Features

Enhanced security with data encryption and fraud detection:

```php
// Data encryption (automatic)
'encrypt_sensitive_data' => true,

// Log masking (automatic)
'mask_sensitive_logs' => true,

// Fraud detection
$fraudService = app(FraudDetectionService::class);
$riskAnalysis = $fraudService->analyzePayment($paymentData);
```

### 7. Caching System

Performance optimization with caching:

```php
// Enable caching
'enable_caching' => true,
'cache_ttl' => 300, // 5 minutes

// Cache management
$cacheService = app(CacheService::class);
$cacheService->clearPaymentCache($key);
```

## 🧪 Testing Your Migration

### 1. Run Tests

```bash
# Run package tests
php artisan test

# Run your application tests
php artisan test --filter=Payment
```

### 2. Test Payment Flow

```php
// Test payment initiation
$response = $this->postJson('/api/paygate/initiate', [
    'provider' => 'paystack',
    'amount' => 250.00,
    'email' => 'test@example.com',
    'reference' => 'TXN_1234567890'
]);

$response->assertStatus(200)
         ->assertJson(['errors' => false]);
```

### 3. Test Webhook Handling

```php
// Test webhook processing
$response = $this->postJson('/api/paygate/webhook', [
    'event' => 'charge.success',
    'data' => [
        'reference' => 'TXN_1234567890',
        'status' => 'success'
    ]
]);

$response->assertStatus(200);
```

## 🚨 Breaking Changes

### 1. Configuration Structure

The configuration file has been restructured. If you have custom configuration, update it:

```php
// Old structure
'paystack' => [
    'secret_key' => env('PAYSTACK_SECRET_KEY'),
    'public_key' => env('PAYSTACK_PUBLIC_KEY'),
],

// New structure
'paystack' => [
    'base_url' => env('PAYSTACK_BASE_URL', 'https://api.paystack.co'),
    'public_key' => env('PAYSTACK_PUBLIC_KEY'),
    'secret_key' => env('PAYSTACK_SECRET_KEY'),
    'webhook_secret' => env('PAYSTACK_WEBHOOK_SECRET'),
    'enabled' => env('PAYSTACK_ENABLED', true),
],
```

### 2. Response Format

Response format has been standardized:

```php
// Old format (provider-specific)
$response = [
    'status' => true,
    'data' => [
        'authorization_url' => 'https://checkout.paystack.com/...',
        'access_code' => '...',
        'reference' => '...'
    ]
];

// New format (standardized)
$response = [
    'errors' => false,
    'message' => 'Payment initiated successfully with paystack',
    'data' => [
        'checkout_url' => 'https://checkout.paystack.com/...',
        'reference' => 'TXN_1234567890',
        'access_code' => '...',
        'provider' => 'paystack'
    ]
];
```

### 3. Error Handling

Error handling has been improved:

```php
// Old error format
$response = [
    'status' => false,
    'message' => 'Payment failed'
];

// New error format
$response = [
    'errors' => true,
    'message' => 'Payment failed',
    'description' => 'Please check your payment details and try again',
    'provider' => 'paystack',
    'data' => []
];
```

## 🔧 Troubleshooting

### Common Issues

1. **Configuration Not Found**
   ```bash
   # Clear config cache
   php artisan config:clear
   php artisan cache:clear
   ```

2. **Database Migration Issues**
   ```bash
   # Run migrations
   php artisan migrate
   ```

3. **Service Not Found**
   ```bash
   # Clear service cache
   php artisan config:clear
   php artisan route:clear
   ```

4. **Webhook Not Working**
   - Check webhook URL configuration
   - Verify webhook secrets
   - Check middleware configuration

### Debug Mode

Enable debug mode for troubleshooting:

```php
// In your .env
APP_DEBUG=true
LOG_LEVEL=debug

// In config/paygate.php
'enable_logging' => true,
```

## 📊 Performance Considerations

### 1. Caching

Enable caching for better performance:

```php
'enable_caching' => true,
'cache_ttl' => 300, // 5 minutes
```

### 2. Database Optimization

The package now stores payment data. Consider:

- Adding database indexes
- Implementing data retention policies
- Monitoring database performance

### 3. Memory Usage

New features may increase memory usage:

- Monitor memory consumption
- Consider increasing PHP memory limit
- Use caching to reduce database queries

## 🚀 Deployment Checklist

Before deploying to production:

- [ ] Test all payment gateways
- [ ] Verify webhook endpoints
- [ ] Check database migrations
- [ ] Review security settings
- [ ] Test caching functionality
- [ ] Verify plugin system
- [ ] Check JavaScript SDK
- [ ] Review error handling
- [ ] Test performance
- [ ] Monitor logs

## 📞 Support

If you encounter issues during migration:

1. Check the [troubleshooting guide](#troubleshooting)
2. Review the [changelog](changelog.md)
3. Search [GitHub issues](https://github.com/obrainwave/paygate/issues)
4. Create a new issue with migration details

## 🎉 Migration Complete!

Once you've completed the migration, you'll have access to:

- **7 Payment Gateways** with unified API
- **Complete Frontend Integration** with JavaScript SDK
- **Advanced Security** with fraud detection
- **Plugin System** for unlimited extensibility
- **Performance Optimization** with caching
- **Comprehensive Testing** with 100% coverage

Welcome to Paygate v2.0.0! 🚀
