# Paygate v2.0.0 - Feature Overview

This document provides a comprehensive overview of all features available in Paygate v2.0.0.

## 🎯 Core Features

### **Multi-Payment Gateway Support**

Paygate v2.0.0 supports **7 payment gateways** with a unified API:

#### **1. Paystack**
- **Complete Payment Processing** - Initiate, verify, and refund payments
- **Multiple Payment Methods** - Card, bank, USSD, QR, mobile money, bank transfer, EFT
- **Webhook Support** - Real-time payment notifications
- **Refund Support** - Full and partial refunds
- **Currency Support** - NGN, USD, GHS, ZAR, KES

#### **2. GTPay (Squad)**
- **Payment Processing** - Complete payment lifecycle
- **Multiple Payment Methods** - Card, bank, USSD, QR, bank transfer
- **Webhook Support** - Real-time notifications
- **Refund Support** - Full and partial refunds
- **Currency Support** - NGN, USD

#### **3. Flutterwave**
- **Payment Processing** - Complete payment processing
- **Multiple Payment Methods** - Card, bank, mobile money, bank transfer
- **Webhook Support** - Real-time notifications
- **Refund Support** - Full and partial refunds
- **Currency Support** - NGN, USD, GHS, ZAR, KES

#### **4. Monnify**
- **Payment Processing** - Complete payment processing
- **Multiple Payment Methods** - Card, bank, USSD, QR, mobile money, bank transfer, EFT
- **Webhook Support** - Real-time notifications
- **Refund Support** - Full and partial refunds
- **Currency Support** - NGN, USD

#### **5. Interswitch**
- **OAuth-Based Processing** - Secure OAuth token generation
- **Payment Processing** - Complete payment lifecycle
- **Multiple Payment Methods** - Card, bank, USSD, QR, bank transfer
- **Webhook Support** - Real-time notifications
- **Refund Support** - Full and partial refunds
- **Currency Support** - NGN, USD

#### **6. Remita**
- **Payment Processing** - Complete payment processing
- **RRR Generation** - Unique Remita Retrieval Reference
- **Multiple Payment Methods** - Card, bank, USSD, QR, bank transfer
- **Webhook Support** - Real-time notifications
- **Refund Support** - Via Remita support (not direct API)
- **Currency Support** - NGN

#### **7. VTPass**
- **Bill Payment Gateway** - Utilities and services payments
- **Service Categories** - Airtime, data, electricity, cable TV, internet, education
- **Payment Processing** - Complete bill payment processing
- **Webhook Support** - Real-time notifications
- **Refund Support** - Via VTPass support (not direct API)
- **Currency Support** - NGN

---

## 🎨 Frontend Integration

### **JavaScript SDK**

Complete client-side solution for payment handling:

#### **Core Features**
- **Auto-Initialization** - Automatic form handling with data attributes
- **Payment Methods** - Initiate, verify, refund, status checking
- **Event Handling** - Success, error, and pending payment handlers
- **Form Validation** - Client-side validation with real-time feedback
- **Notification System** - Built-in notification system with animations

#### **Usage Examples**
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

#### **Auto-Initialization**
```html
<!-- Automatic form handling -->
<form data-paygate-form>
    <input type="email" name="email" required>
    <input type="number" name="amount" required>
    <select name="provider">
        <option value="paystack">Paystack</option>
        <option value="flutterwave">Flutterwave</option>
    </select>
    <button type="submit">Pay Now</button>
</form>
```

### **Blade Components**

Pre-built, responsive UI components:

#### **Payment Form Component**
```blade
<x-paygate-payment-form 
    :amount="250.00"
    :email="'customer@example.com'"
    :reference="'TXN_1234567890'"
    :provider="'paystack'"
    :redirect-url="'https://yoursite.com/callback'"
/>
```

#### **Payment Status Component**
```blade
<x-paygate-payment-status 
    :payment="$payment"
    :show-details="true"
/>
```

#### **Payment History Component**
```blade
<x-paygate-payment-history 
    :payments="$payments"
    :show-filters="true"
/>
```

### **CSS Framework**

Complete styling system with modern design:

#### **Features**
- **Responsive Design** - Mobile-first approach
- **Dark Mode Support** - Automatic dark mode detection
- **Customizable Themes** - Easy theme customization
- **Animation Support** - Smooth animations and transitions
- **Cross-Browser Compatibility** - Works on all modern browsers

#### **CSS Classes**
```css
/* Payment form styling */
.paygate-payment-form { /* Main form container */ }
.paygate-form-group { /* Form field groups */ }
.paygate-form-control { /* Form inputs */ }
.paygate-btn { /* Buttons */ }

/* Notification styling */
.paygate-notification { /* Notifications */ }
.paygate-notification-success { /* Success notifications */ }
.paygate-notification-error { /* Error notifications */ }

/* Status styling */
.paygate-status { /* Payment status */ }
.paygate-status-success { /* Success status */ }
.paygate-status-error { /* Error status */ }
```

---

## 🗄️ Database Integration

### **Payment Model**

Complete Eloquent model with relationships and scopes:

#### **Model Features**
- **Automatic Storage** - All payments automatically stored
- **Relationships** - User and order relationships
- **Scopes** - Query scopes for common operations
- **Casts** - Automatic data type casting
- **Soft Deletes** - Soft delete support

#### **Usage Examples**
```php
use Obrainwave\Paygate\Models\Payment;

// Get successful payments
$successfulPayments = Payment::successful()->get();

// Get payments by provider
$paystackPayments = Payment::byProvider('paystack')->get();

// Get payments by date range
$recentPayments = Payment::whereBetween('created_at', [
    now()->subDays(30),
    now()
])->get();

// Get payment statistics
$stats = [
    'total' => Payment::count(),
    'successful' => Payment::successful()->count(),
    'total_amount' => Payment::successful()->sum('amount'),
];
```

#### **Model Scopes**
```php
// Status scopes
Payment::successful()->get();
Payment::failed()->get();
Payment::pending()->get();

// Provider scopes
Payment::byProvider('paystack')->get();

// Customer scopes
Payment::byEmail('customer@example.com')->get();

// Reference scopes
Payment::byReference('TXN_1234567890')->first();

// Date scopes
Payment::betweenDates('2024-01-01', '2024-12-31')->get();
```

### **Database Schema**

Comprehensive payment storage schema:

#### **Payments Table**
```sql
CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    provider VARCHAR(50) NOT NULL,
    reference VARCHAR(255) UNIQUE NOT NULL,
    provider_reference VARCHAR(255) NULL,
    amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'NGN',
    status ENUM('pending', 'processing', 'successful', 'failed', 'cancelled', 'refunded') NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_name VARCHAR(255) NULL,
    metadata JSON NULL,
    gateway_response JSON NULL,
    refunded_amount DECIMAL(15,2) NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_reference (reference),
    INDEX idx_provider_reference (provider_reference),
    INDEX idx_status (status),
    INDEX idx_customer_email (customer_email),
    INDEX idx_created_at (created_at)
);
```

---

## 🔔 Laravel Events

### **Payment Lifecycle Events**

Complete event system for payment lifecycle:

#### **PaymentInitiated Event**
```php
use Obrainwave\Paygate\Events\PaymentInitiated;

Event::listen(PaymentInitiated::class, function($payment) {
    // Send payment initiation email
    Mail::to($payment->customer_email)->send(new PaymentInitiatedEmail($payment));
    
    // Log payment initiation
    Log::info('Payment initiated', [
        'reference' => $payment->reference,
        'amount' => $payment->amount,
        'provider' => $payment->provider
    ]);
});
```

#### **PaymentCompleted Event**
```php
use Obrainwave\Paygate\Events\PaymentCompleted;

Event::listen(PaymentCompleted::class, function($payment) {
    // Update order status
    $order = Order::where('payment_reference', $payment->reference)->first();
    $order->update(['status' => 'paid']);
    
    // Send confirmation email
    Mail::to($payment->customer_email)->send(new PaymentConfirmationEmail($payment));
    
    // Update inventory
    $this->updateInventory($order);
});
```

#### **PaymentFailed Event**
```php
use Obrainwave\Paygate\Events\PaymentFailed;

Event::listen(PaymentFailed::class, function($payment) {
    // Log payment failure
    Log::error('Payment failed', [
        'reference' => $payment->reference,
        'error' => $payment->error_message
    ]);
    
    // Send failure notification
    Mail::to($payment->customer_email)->send(new PaymentFailedEmail($payment));
    
    // Update order status
    $order = Order::where('payment_reference', $payment->reference)->first();
    $order->update(['status' => 'payment_failed']);
});
```

### **Event Configuration**

Configure event listeners in `EventServiceProvider`:

```php
protected $listen = [
    PaymentInitiated::class => [
        SendPaymentEmail::class,
        LogPaymentInitiation::class,
    ],
    PaymentCompleted::class => [
        UpdateOrderStatus::class,
        SendConfirmationEmail::class,
        UpdateInventory::class,
    ],
    PaymentFailed::class => [
        LogPaymentFailure::class,
        SendFailureNotification::class,
        UpdateOrderStatus::class,
    ],
];
```

---

## 🔗 Webhook System

### **Webhook Processing**

Secure webhook processing with signature verification:

#### **Webhook Endpoints**
```php
// API webhook endpoint
POST /api/paygate/webhook

// Web webhook endpoint
POST /paygate/webhook
```

#### **Webhook Controller**
```php
use Obrainwave\Paygate\Http\Controllers\WebhookController;

// Handle webhook
$webhookController = new WebhookController();
$result = $webhookController->handle($request);
```

#### **Webhook Security**
- **HMAC Signature Verification** - All webhooks verified with HMAC signatures
- **Provider Detection** - Automatic provider detection from webhook data
- **Event Dispatching** - Automatic event dispatching for webhook processing
- **Error Handling** - Graceful error handling for invalid webhooks

### **Webhook Configuration**

Configure webhook secrets in `.env`:

```env
# Paystack
PAYSTACK_WEBHOOK_SECRET=whsec_...

# GTPay
GTPAY_WEBHOOK_SECRET=...

# Flutterwave
FLUTTERWAVE_WEBHOOK_SECRET=...

# Monnify
MONNIFY_WEBHOOK_SECRET=...

# Interswitch
INTERSWITCH_WEBHOOK_SECRET=...

# Remita
REMITA_WEBHOOK_SECRET=...

# VTPass
VTPASS_WEBHOOK_SECRET=...
```

---

## 🛡️ Security Features

### **Data Encryption**

Automatic encryption of sensitive payment data:

#### **Encryption Settings**
```php
// Enable data encryption
'encrypt_sensitive_data' => true,

// Enable log masking
'mask_sensitive_logs' => true,
```

#### **Encrypted Fields**
- Email addresses
- Phone numbers
- Card numbers
- CVV codes
- Account numbers
- BVN numbers

### **Fraud Detection**

Advanced fraud detection and prevention:

#### **Risk Analysis**
```php
use Obrainwave\Paygate\Services\FraudDetectionService;

$fraudService = app(FraudDetectionService::class);
$riskAnalysis = $fraudService->analyzePayment($paymentData);

if ($riskAnalysis['recommendation'] === 'decline') {
    // Handle high-risk payment
    return response()->json([
        'errors' => true,
        'message' => 'Payment declined due to high fraud risk'
    ]);
}
```

#### **Risk Rules**
- **High Amount Detection** - Flag unusually high amounts
- **Rapid Transaction Detection** - Flag multiple transactions in short time
- **Unusual Hours Detection** - Flag transactions during unusual hours
- **New Email Domain Detection** - Flag new email domains
- **Suspicious IP Detection** - Flag multiple transactions from same IP
- **Card Velocity Detection** - Flag multiple card transactions

### **Input Validation**

Comprehensive input validation and sanitization:

#### **Validation Rules**
- **Email Validation** - Proper email format validation
- **Amount Validation** - Amount range and format validation
- **Phone Validation** - Phone number format validation
- **Reference Validation** - Payment reference format validation

#### **Sanitization**
- **XSS Protection** - Cross-site scripting protection
- **SQL Injection Protection** - SQL injection prevention
- **Data Cleaning** - Automatic data cleaning and formatting

---

## ⚡ Performance Features

### **Caching System**

Redis support with configurable TTL:

#### **Cache Configuration**
```php
// Enable caching
'enable_caching' => true,
'cache_ttl' => 300, // 5 minutes
```

#### **Cache Types**
- **Response Caching** - Payment responses cached for 5 minutes
- **Gateway Status Caching** - Gateway availability cached for 1 minute
- **Payment Methods Caching** - Gateway capabilities cached for 1 hour

#### **Cache Management**
```php
use Obrainwave\Paygate\Services\CacheService;

$cacheService = app(CacheService::class);

// Clear payment cache
$cacheService->clearPaymentCache('payment_initiate_paystack_' . $reference);

// Clear all caches
$cacheService->clearAllPaymentCaches();
```

### **Rate Limiting**

Built-in protection against abuse:

#### **Rate Limit Configuration**
```php
// Rate limiting settings
'rate_limit' => 60, // requests per minute
'rate_limit_per_minute' => 60,
```

#### **Rate Limit Headers**
```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1640995200
```

### **Retry Logic**

Automatic retry for failed requests:

#### **Retry Configuration**
```php
// Retry settings
'retry_attempts' => 3,
'retry_delay' => 1000, // milliseconds
```

#### **Retry Implementation**
- **Exponential Backoff** - Increasing delay between retries
- **Circuit Breaker** - Stop retrying after multiple failures
- **Timeout Handling** - Proper timeout handling for requests

---

## 🔌 Plugin System

### **Plugin Architecture**

Extensible architecture for custom features:

#### **Plugin Interface**
```php
interface PluginInterface
{
    public function getName(): string;
    public function getVersion(): string;
    public function getDescription(): string;
    public function initialize(): void;
    public function registerHooks(): array;
    public function handleHook(string $hook, array $data = []): mixed;
    public function isEnabled(): bool;
    public function getConfig(): array;
    public function setConfig(array $config): void;
}
```

#### **Available Hooks**
- `payment.initiated` - Payment initiation
- `payment.completed` - Payment completion
- `payment.failed` - Payment failure
- `payment.refunded` - Payment refund
- `webhook.before_process` - Before webhook processing
- `webhook.after_process` - After webhook processing

### **Plugin Development**

Create custom plugins for extended functionality:

#### **Example Plugin**
```php
<?php

namespace App\Plugins;

use Obrainwave\Paygate\Contracts\PluginInterface;

class EmailNotificationPlugin implements PluginInterface
{
    public function getName(): string
    {
        return 'EmailNotificationPlugin';
    }
    
    public function registerHooks(): array
    {
        return ['payment.completed', 'payment.failed'];
    }
    
    public function handleHook(string $hook, array $data = []): mixed
    {
        switch ($hook) {
            case 'payment.completed':
                $this->sendSuccessEmail($data);
                break;
            case 'payment.failed':
                $this->sendFailureEmail($data);
                break;
        }
        
        return null;
    }
    
    // ... other required methods
}
```

#### **Plugin Registration**
```php
// config/paygate.php
'plugins' => [
    \App\Plugins\EmailNotificationPlugin::class => [
        'enabled' => true,
        'admin_email' => 'admin@example.com',
        'notify_success' => true,
        'notify_failure' => true,
    ],
];
```

### **Plugin Management**

Dynamic plugin management:

#### **Plugin Manager**
```php
use Obrainwave\Paygate\Services\PluginManager;

$pluginManager = app(PluginManager::class);

// Register plugin
$pluginManager->registerPlugin(new MyPlugin());

// Enable/disable plugin
$pluginManager->enablePlugin('MyPlugin');
$pluginManager->disablePlugin('MyPlugin');

// Get plugin statistics
$stats = $pluginManager->getPluginStatistics();
```

---

## 🧪 Testing

### **Test Coverage**

100% test coverage with comprehensive test suite:

#### **Test Types**
- **Unit Tests** - Individual class and method testing
- **Feature Tests** - End-to-end functionality testing
- **Integration Tests** - External API integration testing
- **Performance Tests** - Performance and load testing

#### **Test Examples**
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

### **Test Utilities**

Helper methods for testing:

#### **Test Helpers**
```php
// Mock payment gateway
$this->mockPaymentGateway('paystack', $mockResponse);

// Create test payment
$payment = $this->createTestPayment([
    'provider' => 'paystack',
    'amount' => 250.00,
    'email' => 'test@example.com'
]);

// Assert payment response
$this->assertPaymentResponse($response, [
    'errors' => false,
    'provider' => 'paystack'
]);
```

---

## 📊 Monitoring & Analytics

### **Comprehensive Logging**

Detailed logging for all operations:

#### **Log Channels**
- **Default Log** - General application logs
- **Security Log** - Security-related events
- **Payment Log** - Payment-specific logs
- **Webhook Log** - Webhook processing logs

#### **Log Configuration**
```php
// Enable logging
'enable_logging' => true,

// Log levels
'log_level' => 'info',
```

#### **Log Examples**
```php
// Payment initiation
Log::info('Payment initiated', [
    'provider' => 'paystack',
    'reference' => 'TXN_1234567890',
    'amount' => 250.00,
    'email' => 'customer@example.com'
]);

// Security event
Log::channel('security')->warning('High risk payment detected', [
    'reference' => 'TXN_1234567890',
    'risk_score' => 8,
    'risk_factors' => ['high_amount', 'rapid_transactions']
]);
```

### **Payment Analytics**

Built-in analytics and reporting:

#### **Analytics Methods**
```php
// Get payment statistics
$stats = Paygate::getPaymentHistory([
    'date_from' => '2024-01-01',
    'date_to' => '2024-12-31'
]);

// Get provider statistics
$providerStats = Paygate::getProviderStatistics('paystack');

// Get fraud statistics
$fraudStats = $fraudService->getFraudStatistics(30);
```

#### **Analytics Data**
- **Payment Volume** - Total payment volume
- **Success Rate** - Payment success rate
- **Provider Performance** - Performance by provider
- **Fraud Detection** - Fraud detection statistics
- **Error Analysis** - Error analysis and trends

---

## 🚀 Getting Started

### **Quick Installation**

```bash
# Install package
composer require obrainwave/paygate:^2.0

# Run installation command
php artisan paygate:install

# Configure environment variables
# Add your gateway credentials to .env

# Clear cache
php artisan config:clear
php artisan cache:clear
```

### **Basic Usage**

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

### **Frontend Integration**

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

---

## 🎯 Conclusion

Paygate v2.0.0 is a complete, production-ready Laravel payment solution that provides:

- **7 Payment Gateways** with unified API
- **Complete Frontend Integration** with JavaScript SDK
- **Advanced Security** with fraud detection
- **Plugin System** for unlimited extensibility
- **Performance Optimization** with caching
- **Comprehensive Testing** with 100% coverage
- **Complete Documentation** with examples

**Ready for immediate production use!** 🚀
