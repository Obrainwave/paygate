# Paygate Documentation

Welcome to the comprehensive documentation for the Paygate Laravel package. This documentation covers all aspects of the package, from basic usage to advanced features.

## 📚 Documentation Index

### **Getting Started**
- [Installation Guide](README.md#installation) - How to install and configure Paygate
- [Quick Start](README.md#quick-start) - Get up and running in minutes
- [Configuration](README.md#configuration) - Complete configuration reference

### **Core Features**
- [Payment Processing](README.md#usage) - Initiate, verify, and refund payments
- [Database Integration](README.md#database-integration) - Payment storage and tracking
- [Laravel Events](README.md#laravel-events) - Event-driven payment handling
- [Webhook System](README.md#webhook-handling) - Secure webhook processing

### **Payment Gateways**
- [Paystack Integration](README.md#paystack) - Complete Paystack support
- [GTPay Integration](README.md#gtpay) - GTPay payment processing
- [Flutterwave Integration](README.md#flutterwave) - Flutterwave support
- [Monnify Integration](README.md#monnify) - Monnify payment gateway
- [Interswitch Integration](README.md#interswitch) - OAuth-based payments
- [Remita Integration](README.md#remita) - Remita payment processing
- [VTPass Integration](README.md#vtpass) - Bill payment gateway

### **Frontend Integration**
- [JavaScript SDK](JavaScript-SDK.md) - Complete client-side solution
- [Blade Components](README.md#blade-components) - Pre-built UI components
- [Form Integration](README.md#form-integration) - Easy form handling
- [Responsive Design](README.md#responsive-design) - Mobile-friendly forms

### **Advanced Features**
- [Caching System](README.md#caching-system) - Performance optimization
- [Security Suite](README.md#security-suite) - Data protection and fraud detection
- [Plugin System](Plugin-System.md) - Extensible architecture
- [Performance Optimization](README.md#performance-optimization) - Speed and efficiency

### **API Reference**
- [REST API](API.md) - Complete API documentation
- [Web Routes](README.md#web-routes) - Web interface endpoints
- [API Routes](README.md#api-routes) - API endpoints
- [Webhook Endpoints](README.md#webhook-endpoints) - Webhook handling

### **Development**
- [Testing](README.md#testing) - How to test your implementation
- [Contributing](CONTRIBUTING.md) - How to contribute to the project
- [Changelog](CHANGELOG.md) - Version history and changes
- [License](LICENSE.md) - License information

## 🚀 Quick Start

### 1. Installation

```bash
composer require obrainwave/paygate
php artisan paygate:install
```

### 2. Configuration

Add your gateway credentials to `.env`:

```env
PAYSTACK_SECRET_KEY=sk_test_...
PAYSTACK_WEBHOOK_SECRET=whsec_...
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
    <button type="submit">Pay Now</button>
</form>
```

## 📖 Feature Overview

### **Payment Gateways (7 Total)**
- **Paystack** - Complete payment processing with refunds
- **GTPay** - Full payment support with refunds
- **Flutterwave** - Comprehensive payment processing with refunds
- **Monnify** - Complete payment gateway with refunds
- **Interswitch** - OAuth-based payment processing with refunds
- **Remita** - Payment processing (refund via support)
- **VTPass** - Bill payments for utilities and services

### **Core Features**
- **Unified API** - Single interface for all payment gateways
- **Database Integration** - Automatic payment storage and tracking
- **Laravel Events** - Payment lifecycle event dispatching
- **Webhook Handling** - Secure webhook processing with signature verification
- **Middleware Protection** - Route protection and payment verification

### **Frontend Integration**
- **JavaScript SDK** - Complete client-side payment handling
- **Blade Components** - Pre-built frontend components
- **Responsive Design** - Mobile-friendly payment forms
- **Real-time Notifications** - Built-in notification system
- **Form Validation** - Client-side validation and error handling

### **Advanced Features**
- **Caching System** - Redis support with configurable TTL
- **Security Suite** - Data encryption, log masking, and fraud detection
- **Plugin System** - Extensible architecture for custom features
- **Performance Optimization** - Caching, retry logic, and rate limiting

## 🔧 Configuration

### Environment Variables

```env
# Paystack
PAYSTACK_PUBLIC_KEY=your_paystack_public_key
PAYSTACK_SECRET_KEY=your_paystack_secret_key
PAYSTACK_WEBHOOK_SECRET=your_paystack_webhook_secret

# GTPay
GTPAY_PUBLIC_KEY=your_gtpay_public_key
GTPAY_SECRET_KEY=your_gtpay_secret_key
GTPAY_WEBHOOK_SECRET=your_gtpay_webhook_secret

# Flutterwave
FLUTTERWAVE_PUBLIC_KEY=your_flutterwave_public_key
FLUTTERWAVE_SECRET_KEY=your_flutterwave_secret_key
FLUTTERWAVE_WEBHOOK_SECRET=your_flutterwave_webhook_secret

# Monnify
MONNIFY_API_KEY=your_monnify_api_key
MONNIFY_SECRET_KEY=your_monnify_secret_key
MONNIFY_WEBHOOK_SECRET=your_monnify_webhook_secret

# Interswitch
INTERSWITCH_CLIENT_ID=your_interswitch_client_id
INTERSWITCH_CLIENT_SECRET=your_interswitch_client_secret
INTERSWITCH_WEBHOOK_SECRET=your_interswitch_webhook_secret

# Remita
REMITA_MERCHANT_ID=your_remita_merchant_id
REMITA_API_KEY=your_remita_api_key
REMITA_SERVICE_TYPE_ID=your_remita_service_type_id
REMITA_WEBHOOK_SECRET=your_remita_webhook_secret

# VTPass
VTPASS_API_KEY=your_vtpass_api_key
VTPASS_SECRET_KEY=your_vtpass_secret_key
VTPASS_WEBHOOK_SECRET=your_vtpass_webhook_secret

# Security & Performance
PAYGATE_ENCRYPT_SENSITIVE_DATA=true
PAYGATE_MASK_SENSITIVE_LOGS=true
PAYGATE_ENABLE_CACHING=true
PAYGATE_CACHE_TTL=300
PAYGATE_FRAUD_DETECTION=true
PAYGATE_RISK_THRESHOLD=6
```

## 📱 Frontend Integration

### JavaScript SDK

```javascript
// Initialize Paygate
const paygate = new Paygate({
    baseUrl: '/api/paygate',
    defaultProvider: 'paystack',
    defaultCurrency: 'NGN',
    onSuccess: function(response) {
        console.log('Payment successful:', response);
    },
    onError: function(response) {
        console.error('Payment failed:', response);
    }
});

// Initiate payment
const response = await paygate.initiatePayment({
    provider: 'paystack',
    amount: 250.00,
    email: 'customer@example.com',
    reference: 'TXN_1234567890'
});
```

### Blade Components

```blade
<!-- Payment Form Component -->
<x-paygate-payment-form 
    :amount="250.00"
    :email="'customer@example.com'"
    :reference="'TXN_1234567890'"
    :provider="'paystack'"
/>
```

## 🔌 Plugin System

### Creating a Plugin

```php
<?php

namespace App\Plugins;

use Obrainwave\Paygate\Contracts\PluginInterface;

class MyCustomPlugin implements PluginInterface
{
    public function getName(): string
    {
        return 'MyCustomPlugin';
    }
    
    public function registerHooks(): array
    {
        return ['payment.completed'];
    }
    
    public function handleHook(string $hook, array $data = []): mixed
    {
        // Custom logic here
        return null;
    }
    
    // ... other required methods
}
```

### Registering Plugins

```php
// config/paygate.php
'plugins' => [
    \App\Plugins\MyCustomPlugin::class => [
        'enabled' => true,
        'custom_setting' => 'value'
    ]
];
```

## 🧪 Testing

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test --filter=PaymentTest
php artisan test --filter=PaymentServiceTest

# Run with coverage
php artisan test --coverage
```

### Test Examples

```php
// Feature test
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

// Unit test
public function test_payment_service_initiates_payment()
{
    $service = new PaymentService();
    $result = $service->initiatePayment([
        'provider' => 'paystack',
        'amount' => 250.00,
        'email' => 'test@example.com',
        'reference' => 'TXN_1234567890'
    ]);
    
    $this->assertFalse($result->errors);
}
```

## 🚀 Performance

### Caching

```php
// Enable caching
'enable_caching' => true,
'cache_ttl' => 300, // 5 minutes

// Cache management
$cacheService = app(CacheService::class);
$cacheService->clearPaymentCache($key);
```

### Rate Limiting

```php
// Configure rate limiting
'rate_limit' => 60, // requests per minute
'rate_limit_per_minute' => 60,
```

## 🔒 Security

### Data Encryption

```php
// Automatic data encryption
'encrypt_sensitive_data' => true,
'mask_sensitive_logs' => true,
```

### Fraud Detection

```php
// Enable fraud detection
'fraud_detection' => [
    'enabled' => true,
    'risk_threshold' => 6,
    'auto_decline_threshold' => 10
],
```

## 📊 Monitoring

### Logging

```php
// Enable comprehensive logging
'enable_logging' => true,

// Security event logging
Log::channel('security')->info('Payment event', $data);
```

### Analytics

```php
// Get payment statistics
$stats = Paygate::getPaymentHistory([
    'date_from' => '2024-01-01',
    'date_to' => '2024-12-31'
]);
```

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details on how to:

- Report bugs
- Suggest features
- Submit pull requests
- Follow coding standards

## 📄 License

This package is licensed under the [MIT License](LICENSE.md).

## 🆘 Support

- **Documentation**: [GitHub Wiki](https://github.com/obrainwave/paygate/wiki)
- **Issues**: [GitHub Issues](https://github.com/obrainwave/paygate/issues)
- **Discussions**: [GitHub Discussions](https://github.com/obrainwave/paygate/discussions)

## 🎯 Roadmap

### Upcoming Features
- **International Gateways**: Stripe, PayPal integration
- **Mobile Money**: M-Pesa, Orange Money support
- **Advanced Analytics**: Payment analytics and reporting
- **Multi-Currency**: Enhanced multi-currency support
- **Subscription Payments**: Recurring payment support
- **Payment Splits**: Multi-party payment splitting

---

**Happy Coding!** 🚀

For more detailed information, please refer to the specific documentation files linked above.
