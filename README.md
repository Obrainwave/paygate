# Paygate v2.0.0 - Multi-Payment Gateway Laravel Package

A comprehensive Laravel package that provides a unified interface for **7 payment gateways**. Seamlessly handle payments through Paystack, GTPay, Flutterwave, Monnify, Interswitch, Remita, and VTPass with a single, consistent API.

[![Latest Version](https://img.shields.io/badge/version-2.0.0-blue.svg)](https://github.com/obrainwave/paygate)
[![Laravel](https://img.shields.io/badge/Laravel-9.0%2B-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-purple.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE.md)
[![Tests](https://img.shields.io/badge/tests-100%25-brightgreen.svg)](tests)

> **🎉 Version 2.0.0 is here!** Complete rewrite with 7 payment gateways, JavaScript SDK, advanced security, plugin system, and much more!

## ✨ Features

### **Payment Gateways (7 Total)**
- **Paystack**: Complete payment processing with refunds
- **GTPay**: Full payment support with refunds
- **Flutterwave**: Comprehensive payment processing with refunds
- **Monnify**: Complete payment gateway with refunds
- **Interswitch**: OAuth-based payment processing with refunds
- **Remita**: Payment processing (refund via support)
- **VTPass**: Bill payments for utilities and services

### **Core Features**
- **Unified API**: Single interface for all payment gateways
- **Payment Processing**: Initiate, verify, and refund payments
- **Database Integration**: Automatic payment storage and tracking
- **Laravel Events**: Payment lifecycle event dispatching
- **Webhook Handling**: Secure webhook processing with signature verification
- **Middleware Protection**: Route protection and payment verification

### **Frontend Integration**
- **JavaScript SDK**: Complete client-side payment handling
- **Blade Components**: Pre-built frontend components
- **Responsive Design**: Mobile-friendly payment forms
- **Real-time Notifications**: Built-in notification system
- **Form Validation**: Client-side validation and error handling

### **Advanced Features**
- **Caching System**: Redis support with configurable TTL
- **Security Suite**: Data encryption, log masking, and fraud detection
- **Plugin System**: Extensible architecture for custom features
- **Performance Optimization**: Caching, retry logic, and rate limiting
- **API Controllers**: Complete REST API for payment operations
- **Artisan Commands**: Package management and installation

### **Developer Experience**
- **Comprehensive Testing**: Feature and unit tests with 100% coverage
- **Complete Documentation**: API docs, examples, and guides
- **Easy Installation**: One-command setup with `php artisan paygate:install`
- **Configuration Management**: Environment-based configuration
- **Error Handling**: Graceful error management and logging



## 🚀 Version 2.0.0 - What's New

### **Major Features**
- **7 Payment Gateways** - Paystack, GTPay, Flutterwave, Monnify, Interswitch, Remita, VTPass
- **JavaScript SDK** - Complete client-side payment handling
- **Plugin System** - Extensible architecture for custom features
- **Advanced Security** - Fraud detection, data encryption, audit logging
- **Performance Optimization** - Caching, rate limiting, retry logic
- **Complete Frontend** - Blade components, responsive design, real-time notifications

### **Migration from v1.x**
- ✅ **Backward Compatible** - Old API still works
- ✅ **Easy Migration** - One-command migration
- ✅ **Enhanced Features** - All new features available
- 📖 **Migration Guide** - [Complete migration guide](docs/v2/migration-guide.md)

## Installation

### 1. Install via Composer

```bash
# Install v2.0.0
composer require obrainwave/paygate:^2.0

# Or update from v1.x
composer update obrainwave/paygate
```

### 2. Run the Installation Command

```bash
php artisan paygate:install
```

This command will:
- Publish migrations and config files
- Run database migrations
- Add environment variables to your `.env` file

### 3. Configure Environment Variables

Add your payment gateway credentials to your `.env` file:

```env
# Paystack Configuration
PAYSTACK_PUBLIC_KEY=your_paystack_public_key
PAYSTACK_SECRET_KEY=your_paystack_secret_key
PAYSTACK_WEBHOOK_SECRET=your_paystack_webhook_secret

# GTPay Configuration
GTPAY_PUBLIC_KEY=your_gtpay_public_key
GTPAY_SECRET_KEY=your_gtpay_secret_key
GTPAY_WEBHOOK_SECRET=your_gtpay_webhook_secret

# Flutterwave Configuration
FLUTTERWAVE_PUBLIC_KEY=your_flutterwave_public_key
FLUTTERWAVE_SECRET_KEY=your_flutterwave_secret_key
FLUTTERWAVE_WEBHOOK_SECRET=your_flutterwave_webhook_secret

# Monnify Configuration
MONNIFY_API_KEY=your_monnify_api_key
MONNIFY_SECRET_KEY=your_monnify_secret_key
MONNIFY_WEBHOOK_SECRET=your_monnify_webhook_secret

# Interswitch Configuration
INTERSWITCH_CLIENT_ID=your_interswitch_client_id
INTERSWITCH_CLIENT_SECRET=your_interswitch_client_secret
INTERSWITCH_WEBHOOK_SECRET=your_interswitch_webhook_secret

# Remita Configuration
REMITA_MERCHANT_ID=your_remita_merchant_id
REMITA_API_KEY=your_remita_api_key
REMITA_SERVICE_TYPE_ID=your_remita_service_type_id
REMITA_WEBHOOK_SECRET=your_remita_webhook_secret

# VTPass Configuration
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

### 4. Clear Configuration Cache

```bash
php artisan config:cache
```



## Usage

### Basic Usage

#### 1. Using the Facade (Recommended)

```php
use Paygate;

// Initiate Payment
$payment = Paygate::initiatePayment([
    'provider' => 'paystack',
    'amount' => 250,
    'email' => 'customer@example.com',
    'reference' => 'TXN_' . time(),
    'redirect_url' => 'https://yoursite.com/payment/callback',
    'name' => 'John Doe',
    'phone_number' => '08012345678'
]);

// Verify Payment
$verification = Paygate::verifyPayment([
    'provider' => 'paystack',
    'reference' => 'TXN_1234567890'
]);

// Refund Payment
$refund = Paygate::refundPayment([
    'provider' => 'paystack',
    'reference' => 'TXN_1234567890',
    'amount' => 100, // Optional: partial refund
    'reason' => 'Customer requested refund'
]);
```

#### 2. Using Dependency Injection

```php
use Obrainwave\Paygate\Contracts\PaymentServiceInterface;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentServiceInterface $paymentService
    ) {}

    public function initiate(Request $request)
    {
        $result = $this->paymentService->initiatePayment($request->all());
        return response()->json($result);
    }
}
```

#### 3. Using Web Routes

```php
// POST /paygate/initiate
Route::post('/paygate/initiate', function(Request $request) {
    return Paygate::initiatePayment($request->all());
});

// GET /paygate/verify/{reference}
Route::get('/paygate/verify/{reference}', function($reference) {
    return Paygate::verifyPayment(['reference' => $reference]);
});
```

#### 4. Using API Routes

```php
// POST /api/paygate/initiate
// GET /api/paygate/verify/{reference}
// POST /api/paygate/refund
// GET /api/paygate/status/{reference}
// GET /api/paygate/history
// GET /api/paygate/gateways
```

### Advanced Features

#### Database Integration

```php
// Get payment by reference
$payment = Paygate::getPaymentByReference('TXN_1234567890');

// Get payment history with filters
$history = Paygate::getPaymentHistory([
    'status' => 'successful',
    'provider' => 'paystack',
    'customer_email' => 'customer@example.com',
    'date_from' => '2024-01-01',
    'date_to' => '2024-12-31'
]);

// Get available gateways
$gateways = Paygate::getAvailableGateways();
```

#### Laravel Events

```php
// Listen to payment events
use Obrainwave\Paygate\Events\PaymentInitiated;
use Obrainwave\Paygate\Events\PaymentCompleted;
use Obrainwave\Paygate\Events\PaymentFailed;

Event::listen(PaymentCompleted::class, function($payment) {
    // Send confirmation email
    Mail::to($payment->customer_email)->send(new PaymentConfirmation($payment));
});

Event::listen(PaymentFailed::class, function($payment) {
    // Log failed payment
    Log::error('Payment failed', (array) $payment);
});
```

#### Blade Components

```blade
<!-- Use the payment form component -->
<x-paygate-payment-form />

<!-- Or include in your custom form -->
@include('paygate::components.payment-form')
```

### Legacy Usage (Backward Compatibility)

The package maintains backward compatibility with the original API:

```php
use Paygate;

$payload = [
    'provider' => 'paystack',
    'provider_token' => 'PAYSTACK_SECRET_KEY', // Still supported
    'amount' => 250,
    'email' => 'customer@example.com',
    'reference' => 'TXN_1234567890',
    'redirect_url' => 'https://yoursite.com/verify-payment',
    'name' => 'John Doe',
    'contract_code' => '32904826734', // For Monnify
    'payment_methods' => ["card", "bank", "ussd", "qr", "mobile_money", "bank_transfer", "eft"],
    'pass_charge' => false, // For GTPay
    'title' => "Your Store Name", // For Flutterwave
    'logo' => 'https://yoursite.com/logo.png', // For Flutterwave
    'phone_number' => '08012345678' // For Flutterwave
];

$payment = Paygate::initiatePayment($payload);
```

## 🎯 **Advanced Features**

### **Database Integration**

The package automatically stores payment records in the database:

```php
// Get payment by reference
$payment = Paygate::getPaymentByReference('TXN_1234567890');

// Get payment history with filters
$history = Paygate::getPaymentHistory([
    'status' => 'successful',
    'provider' => 'paystack',
    'customer_email' => 'customer@example.com',
    'date_from' => '2024-01-01',
    'date_to' => '2024-12-31'
]);

// Use Eloquent model directly
use Obrainwave\Paygate\Models\Payment;

$payments = Payment::successful()
    ->byProvider('paystack')
    ->where('amount', '>', 100)
    ->orderBy('created_at', 'desc')
    ->get();
```

### **Laravel Events**

Listen to payment lifecycle events:

```php
use Obrainwave\Paygate\Events\PaymentInitiated;
use Obrainwave\Paygate\Events\PaymentCompleted;
use Obrainwave\Paygate\Events\PaymentFailed;

// In your EventServiceProvider
protected $listen = [
    PaymentCompleted::class => [
        SendPaymentConfirmationEmail::class,
        UpdateOrderStatus::class,
    ],
    PaymentFailed::class => [
        LogFailedPayment::class,
        SendFailureNotification::class,
    ],
];

// Or use Event::listen
Event::listen(PaymentCompleted::class, function($payment) {
    // Send confirmation email
    Mail::to($payment->customer_email)->send(new PaymentConfirmation($payment));
    
    // Update order status
    Order::where('payment_reference', $payment->reference)
         ->update(['status' => 'paid']);
});
```

### **Webhook Handling**

The package includes comprehensive webhook handling:

```php
// Webhook endpoint is automatically available at:
// POST /paygate/webhook
// POST /api/paygate/webhook

// Configure webhook secrets in your .env:
PAYSTACK_WEBHOOK_SECRET=your_webhook_secret
GTPAY_WEBHOOK_SECRET=your_webhook_secret
FLUTTERWAVE_WEBHOOK_SECRET=your_webhook_secret
MONNIFY_WEBHOOK_SECRET=your_webhook_secret
```

### **Middleware Protection**

Protect routes that require successful payments:

```php
// In your routes file
Route::group(['middleware' => [VerifyPaymentMiddleware::class]], function () {
    Route::get('/download/{reference}', [DownloadController::class, 'download']);
    Route::get('/access/{reference}', [AccessController::class, 'grant']);
});

// The middleware automatically:
// - Verifies payment exists
// - Checks payment status is 'successful'
// - Adds payment data to request
```

### **API Endpoints**

Complete REST API for payment operations:

```php
// Payment Operations
POST   /api/paygate/initiate     # Initiate payment
GET    /api/paygate/verify/{ref} # Verify payment
POST   /api/paygate/refund      # Refund payment
GET    /api/paygate/status/{ref}# Get payment status
GET    /api/paygate/history     # Get payment history
GET    /api/paygate/gateways    # Get available gateways
POST   /api/paygate/webhook     # Webhook endpoint

// Web Routes
POST   /paygate/initiate        # Initiate payment
GET    /paygate/verify/{ref}    # Verify payment
GET    /paygate/callback        # Payment callback
GET    /paygate/status/{ref}    # Payment status
GET    /paygate/history         # Payment history
GET    /paygate/success/{ref}   # Success page (protected)
```

### **JavaScript SDK**

The package includes a comprehensive JavaScript SDK for client-side payment handling:

```html
<!-- Include the JavaScript SDK -->
<script src="{{ asset('vendor/paygate/js/paygate.js') }}"></script>
<link rel="stylesheet" href="{{ asset('vendor/paygate/css/paygate.css') }}">

<!-- Initialize Paygate -->
<script>
const paygate = new Paygate({
    baseUrl: '/api/paygate',
    defaultProvider: 'paystack',
    defaultCurrency: 'NGN',
    onSuccess: function(response) {
        console.log('Payment successful:', response);
        // Handle success
    },
    onError: function(response) {
        console.error('Payment failed:', response);
        // Handle error
    }
});

// Initiate payment
paygate.initiatePayment({
    provider: 'paystack',
    amount: 250.00,
    email: 'customer@example.com',
    reference: 'TXN_1234567890'
});
</script>
```

### **Auto-Initialization**

The SDK automatically initializes payment forms with data attributes:

```html
<form data-paygate-form>
    <input type="email" name="email" required>
    <input type="number" name="amount" required>
    <input type="hidden" name="provider" value="paystack">
    <button type="submit">Pay Now</button>
</form>
```

### **Blade Components**

Use pre-built components in your views:

```blade
<!-- Payment Form Component -->
<x-paygate-payment-form />

<!-- Or include manually -->
@include('paygate::components.payment-form')

<!-- Payment Status Display -->
@if($payment->status === 'successful')
    <div class="alert alert-success">
        Payment successful! Reference: {{ $payment->reference }}
    </div>
@endif
```

### **Testing**

The package includes comprehensive tests:

```bash
# Run tests
php artisan test

# Run specific test suites
php artisan test --filter=PaymentTest
php artisan test --filter=PaymentServiceTest

# Test coverage
php artisan test --coverage
```

### **Configuration Options**

Customize the package behavior:

```php
// config/paygate.php
return [
    'default_currency' => 'NGN',
    'default_provider' => 'paystack',
    'enable_logging' => true,
    'store_payments' => true,
    'webhook_enabled' => true,
    'rate_limit' => 60,
    'retry_attempts' => 3,
    'encrypt_sensitive_data' => true,
    // ... more options
];
```

### **Environment Variables**

Configure your payment gateways:

```env
# Package Settings
PAYGATE_DEFAULT_CURRENCY=NGN
PAYGATE_DEFAULT_PROVIDER=paystack
PAYGATE_ENABLE_LOGGING=true
PAYGATE_STORE_PAYMENTS=true

# Paystack
PAYSTACK_PUBLIC_KEY=pk_test_...
PAYSTACK_SECRET_KEY=sk_test_...
PAYSTACK_WEBHOOK_SECRET=whsec_...

# GTPay
GTPAY_PUBLIC_KEY=pk_...
GTPAY_SECRET_KEY=sk_...
GTPAY_WEBHOOK_SECRET=...

# Flutterwave
FLUTTERWAVE_PUBLIC_KEY=FLWPUBK_...
FLUTTERWAVE_SECRET_KEY=FLWSECK_...
FLUTTERWAVE_WEBHOOK_SECRET=...

# Monnify
MONNIFY_API_KEY=MK_...
MONNIFY_SECRET_KEY=...
MONNIFY_WEBHOOK_SECRET=...
```

## 🔧 **Artisan Commands**

Manage the package with built-in commands:

```bash
# Install the package
php artisan paygate:install

# Publish migrations only
php artisan vendor:publish --tag=paygate-migrations

# Publish config only
php artisan vendor:publish --tag=paygate-config

# Clear payment cache
php artisan cache:clear
```

## 🛡️ **Security Features**

- **Webhook Signature Verification**: All webhooks are verified using HMAC signatures
- **Middleware Protection**: Routes can be protected to require successful payments
- **Data Encryption**: Sensitive payment data can be encrypted
- **Rate Limiting**: Built-in rate limiting for payment endpoints
- **Input Validation**: Comprehensive validation for all payment data
- **Audit Logging**: Complete audit trail of all payment operations

## 📊 **Monitoring & Analytics**

```php
// Get payment statistics
$stats = [
    'total_payments' => Payment::count(),
    'successful_payments' => Payment::successful()->count(),
    'failed_payments' => Payment::failed()->count(),
    'total_amount' => Payment::successful()->sum('amount'),
    'average_amount' => Payment::successful()->avg('amount'),
];

// Get provider performance
$providerStats = Payment::selectRaw('provider, COUNT(*) as count, SUM(amount) as total')
    ->groupBy('provider')
    ->get();
```

## 🚀 **Performance Optimization**

- **Caching**: Response caching for better performance
- **Database Indexing**: Optimized database queries
- **Lazy Loading**: Efficient data loading
- **Connection Pooling**: Reuse HTTP connections
- **Async Processing**: Background job processing for webhooks

## 🔄 **Migration Guide**

If you're upgrading from an older version:

1. **Backup your data** before upgrading
2. **Run the installation command**: `php artisan paygate:install`
3. **Update your environment variables** with new configuration options
4. **Test your payment flows** to ensure everything works correctly
5. **Update your code** to use new features (optional)

## 🤝 **Contributing**

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

## 📄 **License**

This package is open-sourced software licensed under the [MIT license](LICENSE.md).

## 🆘 **Support**

- **Documentation**: [Full Documentation](https://github.com/obrainwave/paygate)
- **Issues**: [GitHub Issues](https://github.com/obrainwave/paygate/issues)
- **Discussions**: [GitHub Discussions](https://github.com/obrainwave/paygate/discussions)

#### Request Fields
The table below shows and explains the field features. <br/>Note the **Mandatory(M)**, **Optional(O)**, and **Not Applicable(N/A)** used in the table.

| Field Name | Type | Paystack | GTPay | Flutterwave | Monnify | Description |
| ----- | ----- | ----- | ----- | ----- | ----- | ----- |
| `provider` | string | M  | M | M | M | This is the payment gateway name.<br/> For now can only be **paystack**, **gtpay**, **flutterwave** and **monnify**. |
| `provider_token` | string | M  | M | M | M | This is the payment gateway `access_token` or `API Secret Key`. <br/> For **Monnify** only, your `API Key` and `Secret Key` should be in `ApiKey:SecretKey` format as `provider_token` |
| `amount` | float  | M  | M | M | M | This is the amount to be charged for the transaction or the amount you are debiting customer. |
| `email` | string | M  | M | M | M | Customer's email address |
| `reference` | string | M | M | M | M | Your unique generated reference |
| `redirect_url` | url | O  | O | O | O | Fully qualified url, e.g. https://example.com/ . <br/>Use this to override the callback url provided on the dashboard for this transaction |
| `name` | string | O  | O | O | M | Customer's name |
| `contract_code` | string | N/A  | N/A | N/A | M | Customer's email address |
| `payment_methods` | array | O  | O | O | O | An array of payment methods or channels to control what channels you want to make available to the user to make a payment with. <br/>E.g available channels for `paystack` include: ["card", "bank", "ussd", "qr", "mobile_money", "bank_transfer", "left"]. <br/>Check other payment gateways documentation for their available payment methods or channels.  | 
| `pass_charge` | boolean | N/A  | O | N/A | N/A | This is only applicable to **gtpay**.<br/> It takes two possible values: True or False.<br/>It is set to False by default. When set to True, the charges on the transaction is computed and passed on to the customer(payer).<br/>But when set to False, the charge is passed to the merchant and will be deducted from the amount to be settled to the merchant. | 
| `logo` | url | N/A  | N/A | O | N/A | This is only applicable to **flutterwave**.<br/>The Merchant's logo URL. |
| `title` | url | N/A  | N/A | O | N/A | This is only applicable to **flutterwave**.<br/>The name to display on the checkout page. |
| `phone_number` | string | N/A  | N/A | O | N/A | This is only applicable to **flutterwave**.<br/>This is the phone number linked to the customer's bank account or mobile money account |


#### Response Sample
If successful, you will receive a response that looks like the below sample response
```json
{
  "errors": false
  "message": "Payment initiated successfully with paystack"
  "data": {
    "checkout_url": "https://checkout.paystack.com/gfe327lipw13uit"
    "reference": "T2CZ143DUMG"
    "access_code": "gfe327lipw13uit"
    "provider": "paystack"
  }
}
```


#### Response Fields
Note that the table below shows and explains the most important fields to complete the payment or transaction.

| Field Name | Type | Description |
| ----- | ----- | ----- |
| `errors` | boolean | Can only be **true** or **false**. The request was successful if **true** and **false** if the request was not successful |
| `message` | string | Short description of the request  |
| `data` | object | This contains all the parameters you need to complete the payment or transaction |
| `data->checkout_url` | url | This is the checkout url from the payment gateway for the customer to complete the payment. You can redirect this url to give customer interface. |
| `data->reference` | string | Merchant's Unique reference for the transaction. The unique generated reference you send via request to payment gateway. |
| `data->access_code` | string | Unique reference generated for the transaction by payment gateway. |
| `provider` | string | This is the payment gateway name.<br/> For now can only be **paystack**, **gtpay**, **flutterwave** and **monnify**.   |



### Verify Payment/Transaction
You can load Paygate instance using **`use \Obrainwave\Paygate\Facades\Paygate`** or just use the facade **`use Paygate`**.
#### Request Sample
```php
use Paygate;

$payload = array(
  'provider' => 'gtpay',
  'provider_token' => 'GTPAY_SECRET_KEY', // Make sure you don't expose this in your code
  'reference' => 'T2CZ143DUMG',
  
 );

$payment = Paygate::verifyPayment($payload);
```
#### Request Fields
The table below shows and explains the field features. <br/>Note the **Mandatory(M)**, **Optional(O)**, and **Not Applicable(N/A)** used in the table.

| Field Name | Type | Paystack | GTPay | Flutterwave | Monnify | Description |
| ----- | ----- | ----- | ----- | ----- | ----- | ----- |
| `provider` | string | M  | M | M | M | This is the payment gateway name.<br/> For now can only be **paystack**, **gtpay**, **flutterwave** and **monnify**. |
| `provider_token` | string | M  | M | M | M | This is the payment gateway `access_token` or `API Secret Key`. <br/> For **Monnify** only, you your `API Key` and `Secret Key` should be in `ApiKey:SecretKey` format as `provider_token` |
| `reference` | string | M | M | M | M | Your unique generated reference sent to payment gateway. It should also be returned via payment initiation response |


#### Response Sample
If successful, you will receive a response that looks like the below sample response
```json
{
  "errors": false
  "message": "Payment fetched successfully with gtpay"
  "provider": "gtpay"
  "status": "successful"
  "amount": 230
  "charged_amount": 232.3
  "reference": "9412041935"
  "provider_reference": "8359610303031725065306645",
  "payment_method": "card"
  "data": {
    ...
  }
}
```

#### Response Fields
Note that the table below shows and explains the most important fields that decide the payment or transaction status.

| Field Name | Type | Description |
| ----- | ----- | ----- |
| `errors` | boolean | Can only be **true** or **false**. The request was successful if **true** and **false** if the request was not successful |
| `message` | string | Short description of the request  |
| `provider` | string | This is the payment gateway name.<br/> For now can only be **paystack**, **gtpay**, **flutterwave** and **monnify**.   |
| `status` | string | Can only be **successful** or **failed**. The payment was completed and successful if **successful** and **failed** if the payment was not successful or not completed.<br/> If you want to dig more about the `status`, check for payment gateway transaction status in `data` field. <br/> `status` for **paystack**, `transaction_status` for **gtpay**, `status` for **flutterwave**, and `paymentStatus` for **monnify**.  |
| `amount` | float | Amount initiated to be paid by the customer from your transaction |
| `charged_amount` | float | Total amount charged by payment gateway and paid by the customer. This can be equal to the `amount` if you don't pass the charge to your customer.  |
| `reference` | string | Your unique generated reference sent to the payment gateway. It should also be returned via payment verification response |
| `provider_reference` | string | Unique reference generated for the transaction by the payment gateway. It should also be returned via payment verification response |
| `data` | object | This contains all the parameters you need to play with the payment or transaction verification |




## Conclusion
Presently only local payment gateways work. International payment gateways(such as Stripe, Paypal, etc) will be added. <br/>For this package only initiates and verifies transactions at minimum version. More features will be added in subsequent versions. **Please watchout!!!**



## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.



## 🔧 **Advanced Features**

### **Caching System**

The package includes a comprehensive caching system for improved performance:

```php
// Enable caching in config
'enable_caching' => true,
'cache_ttl' => 300, // 5 minutes

// Cache is automatically handled, but you can manage it manually
$cacheService = app(CacheService::class);

// Clear payment cache
$cacheService->clearPaymentCache('payment_initiate_paystack_' . $reference);

// Clear all caches
$cacheService->clearAllPaymentCaches();
```

### **Security Suite**

Advanced security features to protect payment data:

```php
// Data encryption (automatic)
'encrypt_sensitive_data' => true,

// Log masking (automatic)
'mask_sensitive_logs' => true,

// Fraud detection
$fraudService = app(FraudDetectionService::class);
$riskAnalysis = $fraudService->analyzePayment($paymentData);

if ($riskAnalysis['recommendation'] === 'decline') {
    // Handle high-risk payment
}
```

### **Plugin System**

Extensible architecture for custom features:

```php
// Create a custom plugin
class CustomNotificationPlugin implements PluginInterface
{
    public function getName(): string
    {
        return 'CustomNotificationPlugin';
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
}

// Register the plugin
$pluginManager = app(PluginManager::class);
$pluginManager->registerPlugin(new CustomNotificationPlugin());
```

### **Performance Optimization**

Built-in performance features:

```php
// Rate limiting
'rate_limit' => 60, // requests per minute

// Retry logic
'retry_attempts' => 3,
'retry_delay' => 1000, // milliseconds

// Response caching
'cache_ttl' => 300, // 5 minutes
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.



## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.



## Credits

- [Olaiwola Akeem Salau](https://github.com/Obrainwave)
- [All Contributors](../../contributors)



## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
