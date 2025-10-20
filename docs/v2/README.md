# Paygate v2.0.0 Documentation

Welcome to Paygate v2.0.0 - the most comprehensive Laravel payment gateway package with 7 payment providers, complete frontend integration, advanced security features, and extensible plugin system.

## 🎉 What's New in v2.0.0

### **7 Payment Gateways**
- **Paystack** - Complete payment processing with refunds
- **GTPay** - Full payment support with refunds
- **Flutterwave** - Comprehensive payment processing with refunds
- **Monnify** - Complete payment gateway with refunds
- **Interswitch** - OAuth-based payment processing with refunds
- **Remita** - Payment processing (refund via support)
- **VTPass** - Bill payments for utilities and services

### **Complete Frontend Integration**
- **JavaScript SDK** - Full client-side payment handling
- **Auto-Initialization** - Automatic form handling with data attributes
- **Real-time Notifications** - Built-in notification system
- **Responsive Design** - Mobile-friendly payment forms
- **CSS Framework** - Complete styling with dark mode support

### **Advanced Features**
- **Caching System** - Redis support with configurable TTL
- **Security Suite** - Data encryption, log masking, and fraud detection
- **Plugin System** - Extensible architecture for custom features
- **Performance Optimization** - Caching, retry logic, and rate limiting
- **Fraud Detection** - Advanced risk scoring and prevention

## 📚 Documentation Structure

### **Getting Started**
- [Installation Guide](#installation)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [Migration from v1.x](#migration-from-v1x)

### **Core Features**
- [Payment Processing](payment-processing.md)
- [Database Integration](database-integration.md)
- [Laravel Events](laravel-events.md)
- [Webhook System](webhook-system.md)

### **Payment Gateways**
- [Paystack Integration](gateways/paystack.md)
- [GTPay Integration](gateways/gtpay.md)
- [Flutterwave Integration](gateways/flutterwave.md)
- [Monnify Integration](gateways/monnify.md)
- [Interswitch Integration](gateways/interswitch.md)
- [Remita Integration](gateways/remita.md)
- [VTPass Integration](gateways/vtpass.md)

### **Frontend Integration**
- [JavaScript SDK](frontend/javascript-sdk.md)
- [Blade Components](frontend/blade-components.md)
- [Form Integration](frontend/form-integration.md)
- [Responsive Design](frontend/responsive-design.md)

### **Advanced Features**
- [Caching System](advanced/caching.md)
- [Security Suite](advanced/security.md)
- [Plugin System](advanced/plugin-system.md)
- [Performance Optimization](advanced/performance.md)

### **API Reference**
- [REST API](api/rest-api.md)
- [Web Routes](api/web-routes.md)
- [Webhook Endpoints](api/webhook-endpoints.md)

### **Development**
- [Testing Guide](development/testing.md)
- [Contributing](development/contributing.md)
- [Changelog](development/changelog.md)

## 🚀 Quick Start

### 1. Installation

```bash
composer require obrainwave/paygate:^2.0
php artisan paygate:install
```

### 2. Configuration

Add your gateway credentials to `.env`:

```env
# Paystack
PAYSTACK_SECRET_KEY=sk_test_...
PAYSTACK_WEBHOOK_SECRET=whsec_...

# GTPay
GTPAY_SECRET_KEY=sk_...
GTPAY_WEBHOOK_SECRET=...

# Flutterwave
FLUTTERWAVE_SECRET_KEY=FLWSECK_...
FLUTTERWAVE_WEBHOOK_SECRET=...

# Monnify
MONNIFY_API_KEY=MK_...
MONNIFY_SECRET_KEY=...
MONNIFY_WEBHOOK_SECRET=...

# Interswitch
INTERSWITCH_CLIENT_ID=your_client_id
INTERSWITCH_CLIENT_SECRET=your_client_secret
INTERSWITCH_WEBHOOK_SECRET=...

# Remita
REMITA_MERCHANT_ID=your_merchant_id
REMITA_API_KEY=your_api_key
REMITA_SERVICE_TYPE_ID=your_service_type_id
REMITA_WEBHOOK_SECRET=...

# VTPass
VTPASS_API_KEY=your_api_key
VTPASS_SECRET_KEY=your_secret_key
VTPASS_WEBHOOK_SECRET=...

# Security & Performance
PAYGATE_ENCRYPT_SENSITIVE_DATA=true
PAYGATE_MASK_SENSITIVE_LOGS=true
PAYGATE_ENABLE_CACHING=true
PAYGATE_CACHE_TTL=300
PAYGATE_FRAUD_DETECTION=true
PAYGATE_RISK_THRESHOLD=6
```

### 3. Basic Usage

```php
use Paygate;

// Initiate payment
$response = Paygate::initiatePayment([
    'provider' => 'paystack',
    'amount' => 250.00,
    'email' => 'customer@example.com',
    'reference' => 'TXN_1234567890'
]);

// Verify payment
$verification = Paygate::verifyPayment('TXN_1234567890');

// Refund payment
$refund = Paygate::refundPayment([
    'reference' => 'TXN_1234567890',
    'amount' => 100.00,
    'reason' => 'Customer requested refund'
]);
```

### 4. Frontend Integration

```html
<!-- Include JavaScript SDK -->
<script src="{{ asset('vendor/paygate/js/paygate.js') }}"></script>
<link rel="stylesheet" href="{{ asset('vendor/paygate/css/paygate.css') }}">

<!-- Auto-initialize payment form -->
<form data-paygate-form>
    <input type="email" name="email" required>
    <input type="number" name="amount" required>
    <select name="provider">
        <option value="paystack">Paystack</option>
        <option value="flutterwave">Flutterwave</option>
        <option value="monnify">Monnify</option>
    </select>
    <button type="submit">Pay Now</button>
</form>
```

## 🔄 Migration from v1.x

### Breaking Changes

1. **Service Layer Architecture**
   - Old: Trait-based approach
   - New: Service layer with interfaces

2. **Configuration Structure**
   - Old: Basic configuration
   - New: Comprehensive configuration with security settings

3. **Response Format**
   - Old: Provider-specific responses
   - New: Standardized response format

### Migration Steps

1. **Update Composer**
   ```bash
   composer require obrainwave/paygate:^2.0
   ```

2. **Run Migration Command**
   ```bash
   php artisan paygate:install
   ```

3. **Update Configuration**
   - Add new environment variables
   - Update config file structure

4. **Update Code**
   - Replace trait usage with service injection
   - Update response handling
   - Add new features as needed

### Backward Compatibility

The package maintains backward compatibility with v1.x API:

```php
// This still works in v2.0
$payment = Paygate::initiate($payload);
$verification = Paygate::verify($reference);
```

## 🎯 Key Features

### **Unified API**
Single interface for all 7 payment gateways:

```php
// Works with any gateway
$response = Paygate::initiatePayment([
    'provider' => 'paystack', // or 'flutterwave', 'monnify', etc.
    'amount' => 250.00,
    'email' => 'customer@example.com',
    'reference' => 'TXN_1234567890'
]);
```

### **Database Integration**
Automatic payment storage and tracking:

```php
// Get payment by reference
$payment = Paygate::getPaymentByReference('TXN_1234567890');

// Get payment history
$history = Paygate::getPaymentHistory([
    'status' => 'successful',
    'provider' => 'paystack',
    'date_from' => '2024-01-01'
]);
```

### **Laravel Events**
Event-driven payment handling:

```php
// Listen to payment events
Event::listen(PaymentCompleted::class, function($payment) {
    // Send confirmation email
    Mail::to($payment->customer_email)->send(new PaymentConfirmation($payment));
});
```

### **JavaScript SDK**
Complete client-side solution:

```javascript
const paygate = new Paygate({
    baseUrl: '/api/paygate',
    onSuccess: function(response) {
        console.log('Payment successful:', response);
    }
});

// Initiate payment
const response = await paygate.initiatePayment({
    provider: 'paystack',
    amount: 250.00,
    email: 'customer@example.com'
});
```

### **Plugin System**
Extensible architecture:

```php
// Create custom plugin
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
```

## 🔒 Security Features

### **Data Encryption**
Automatic encryption of sensitive data:

```php
'encrypt_sensitive_data' => true,
'mask_sensitive_logs' => true,
```

### **Fraud Detection**
Advanced risk scoring and prevention:

```php
$fraudService = app(FraudDetectionService::class);
$riskAnalysis = $fraudService->analyzePayment($paymentData);

if ($riskAnalysis['recommendation'] === 'decline') {
    // Handle high-risk payment
}
```

### **Webhook Security**
HMAC signature verification:

```php
// Automatic signature verification
$webhookController = new WebhookController();
$result = $webhookController->handle($request);
```

## ⚡ Performance Features

### **Caching System**
Redis support with configurable TTL:

```php
'enable_caching' => true,
'cache_ttl' => 300, // 5 minutes
```

### **Rate Limiting**
Built-in protection against abuse:

```php
'rate_limit' => 60, // requests per minute
```

### **Retry Logic**
Automatic retry for failed requests:

```php
'retry_attempts' => 3,
'retry_delay' => 1000, // milliseconds
```

## 🧪 Testing

### **Comprehensive Test Suite**
100% test coverage with feature and unit tests:

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage
```

### **Test Examples**
```php
public function test_can_initiate_payment()
{
    $response = $this->postJson('/api/paygate/initiate', [
        'provider' => 'paystack',
        'amount' => 250.00,
        'email' => 'test@example.com',
        'reference' => 'TXN_1234567890'
    ]);
    
    $response->assertStatus(200)
             ->assertJson(['errors' => false]);
}
```

## 📊 Monitoring & Analytics

### **Comprehensive Logging**
Detailed payment logging and monitoring:

```php
'enable_logging' => true,

// Security event logging
Log::channel('security')->info('Payment event', $data);
```

### **Payment Analytics**
Built-in analytics and reporting:

```php
$stats = Paygate::getPaymentHistory([
    'date_from' => '2024-01-01',
    'date_to' => '2024-12-31'
]);
```

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](development/contributing.md) for details.

## 📄 License

This package is licensed under the [MIT License](LICENSE.md).

## 🆘 Support

- **Documentation**: [GitHub Wiki](https://github.com/obrainwave/paygate/wiki)
- **Issues**: [GitHub Issues](https://github.com/obrainwave/paygate/issues)
- **Discussions**: [GitHub Discussions](https://github.com/obrainwave/paygate/discussions)

---

**Paygate v2.0.0 - The Complete Laravel Payment Solution** 🚀
