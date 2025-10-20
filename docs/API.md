# Paygate API Documentation

This document provides comprehensive API documentation for the Paygate Laravel package.

## Table of Contents

- [Authentication](#authentication)
- [Endpoints](#endpoints)
- [Request/Response Formats](#requestresponse-formats)
- [Error Handling](#error-handling)
- [Webhooks](#webhooks)
- [Examples](#examples)

## Authentication

The Paygate package uses API keys for authentication with payment gateways. Configure your keys in the `.env` file:

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
```

## Endpoints

### Payment Operations

#### Initiate Payment

**POST** `/api/paygate/initiate`

Initiates a new payment transaction.

**Request Body:**
```json
{
    "provider": "paystack",
    "amount": 250.00,
    "email": "customer@example.com",
    "reference": "TXN_1234567890",
    "redirect_url": "https://yoursite.com/callback",
    "name": "John Doe",
    "phone_number": "08012345678",
    "currency": "NGN"
}
```

**Response:**
```json
{
    "errors": false,
    "message": "Payment initiated successfully with paystack",
    "data": {
        "checkout_url": "https://checkout.paystack.com/gfe327lipw13uit",
        "reference": "TXN_1234567890",
        "access_code": "gfe327lipw13uit",
        "provider": "paystack"
    }
}
```

#### Verify Payment

**GET** `/api/paygate/verify/{reference}`

Verifies a payment transaction.

**Parameters:**
- `reference` (string): Payment reference
- `provider` (string, optional): Payment provider (defaults to configured default)

**Response:**
```json
{
    "errors": false,
    "message": "Payment verified successfully with paystack",
    "provider": "paystack",
    "status": "successful",
    "amount": 250.00,
    "charged_amount": 257.50,
    "reference": "TXN_1234567890",
    "provider_reference": "TXN_1234567890",
    "payment_method": "card",
    "data": {
        "status": "success",
        "amount": 25000,
        "currency": "NGN"
    }
}
```

#### Refund Payment

**POST** `/api/paygate/refund`

Processes a refund for a payment.

**Request Body:**
```json
{
    "provider": "paystack",
    "reference": "TXN_1234567890",
    "amount": 100.00,
    "reason": "Customer requested refund"
}
```

**Response:**
```json
{
    "errors": false,
    "message": "Refund processed successfully with paystack",
    "provider": "paystack",
    "refund_reference": "REF_1234567890",
    "amount": 100.00,
    "status": "success",
    "data": {
        "reference": "REF_1234567890",
        "amount": 10000,
        "status": "success
    }
    }
}
```

#### Get Payment Status

**GET** `/api/paygate/status/{reference}`

Gets the current status of a payment.

**Response:**
```json
{
    "errors": false,
    "message": "Payment status retrieved successfully",
    "provider": "paystack",
    "status": "successful",
    "amount": 250.00,
    "reference": "TXN_1234567890",
    "data": {
        "status": "success",
        "amount": 25000
    }
}
```

#### Get Payment History

**GET** `/api/paygate/history`

Retrieves payment history with optional filters.

**Query Parameters:**
- `status` (string, optional): Filter by payment status
- `provider` (string, optional): Filter by payment provider
- `customer_email` (string, optional): Filter by customer email
- `date_from` (date, optional): Filter from date (YYYY-MM-DD)
- `date_to` (date, optional): Filter to date (YYYY-MM-DD)
- `page` (integer, optional): Page number for pagination
- `per_page` (integer, optional): Items per page (default: 15)

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "reference": "TXN_1234567890",
            "provider": "paystack",
            "amount": 250.00,
            "currency": "NGN",
            "status": "successful",
            "customer_email": "customer@example.com",
            "created_at": "2024-01-15T10:30:00Z",
            "completed_at": "2024-01-15T10:32:00Z"
        }
    ],
    "total": 1,
    "per_page": 15,
    "current_page": 1,
    "last_page": 1
}
```

#### Get Available Gateways

**GET** `/api/paygate/gateways`

Returns available payment gateways and their capabilities.

**Response:**
```json
{
    "errors": false,
    "message": "Available gateways retrieved successfully",
    "data": [
        {
            "name": "paystack",
            "display_name": "Paystack",
            "enabled": true,
            "supported_methods": ["card", "bank", "ussd", "qr", "mobile_money", "bank_transfer", "eft"],
            "supported_currencies": ["NGN", "USD", "GHS", "ZAR", "KES"]
        },
        {
            "name": "gtpay",
            "display_name": "GTPay",
            "enabled": true,
            "supported_methods": ["card", "bank", "ussd", "qr", "bank_transfer"],
            "supported_currencies": ["NGN", "USD"]
        }
    ]
}
```

### Web Routes

#### Payment Callback

**GET** `/paygate/callback`

Handles payment callbacks from payment gateways.

**Query Parameters:**
- `reference` (string): Payment reference
- `provider` (string, optional): Payment provider

**Response:** HTML page showing payment status

#### Payment Success

**GET** `/paygate/success/{reference}`

Protected route that shows success page for completed payments.

**Middleware:** Requires successful payment verification

**Response:** HTML page with payment details

#### Payment History (Web)

**GET** `/paygate/history`

Web interface for payment history with filtering.

**Query Parameters:** Same as API history endpoint

**Response:** HTML page with payment history table

## Request/Response Formats

### Standard Response Format

All API responses follow this format:

```json
{
    "errors": false,
    "message": "Operation completed successfully",
    "data": {
        // Response data
    }
}
```

### Error Response Format

```json
{
    "errors": true,
    "message": "Error description",
    "description": "Detailed error information",
    "provider": "paystack",
    "data": {
        // Additional error data
    }
}
```

### Payment Status Values

- `pending`: Payment initiated but not completed
- `processing`: Payment is being processed
- `successful`: Payment completed successfully
- `failed`: Payment failed
- `cancelled`: Payment was cancelled
- `refunded`: Payment was refunded

## Error Handling

### HTTP Status Codes

- `200`: Success
- `400`: Bad Request (validation errors)
- `401`: Unauthorized (invalid webhook signature)
- `402`: Payment Required (payment not completed)
- `404`: Not Found (payment not found)
- `422`: Unprocessable Entity (validation errors)
- `500`: Internal Server Error

### Validation Errors

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "provider": ["The provider field is required."],
        "amount": ["The amount must be at least 0.01."],
        "email": ["The email field is required."]
    }
}
```

## Webhooks

### Webhook Endpoint

**POST** `/api/paygate/webhook`

Handles webhooks from payment gateways.

### Webhook Security

All webhooks are verified using HMAC signatures. Configure webhook secrets:

```env
PAYSTACK_WEBHOOK_SECRET=whsec_...
GTPAY_WEBHOOK_SECRET=...
FLUTTERWAVE_WEBHOOK_SECRET=...
MONNIFY_WEBHOOK_SECRET=...
```

### Webhook Events

The package automatically dispatches Laravel events for webhook processing:

- `PaymentInitiated`: When payment is initiated
- `PaymentCompleted`: When payment is completed successfully
- `PaymentFailed`: When payment fails

### Webhook Payload Examples

#### Paystack Webhook

```json
{
    "event": "charge.success",
    "data": {
        "reference": "TXN_1234567890",
        "amount": 25000,
        "status": "success",
        "currency": "NGN"
    }
}
```

#### GTPay Webhook

```json
{
    "transaction_ref": "TXN_1234567890",
    "transaction_status": "success",
    "transaction_amount": 25000,
    "currency": "NGN"
}
```

## JavaScript SDK

### Initialization

```javascript
// Initialize Paygate SDK
const paygate = new Paygate({
    baseUrl: '/api/paygate',
    defaultProvider: 'paystack',
    defaultCurrency: 'NGN',
    onSuccess: function(response) {
        console.log('Payment successful:', response);
    },
    onError: function(response) {
        console.error('Payment failed:', response);
    },
    onPending: function(response) {
        console.log('Payment pending:', response);
    }
});
```

### Payment Methods

```javascript
// Initiate payment
const response = await paygate.initiatePayment({
    provider: 'paystack',
    amount: 250.00,
    email: 'customer@example.com',
    reference: 'TXN_1234567890'
});

// Verify payment
const verification = await paygate.verifyPayment('TXN_1234567890');

// Get payment status
const status = await paygate.getPaymentStatus('TXN_1234567890');

// Refund payment
const refund = await paygate.refundPayment({
    reference: 'TXN_1234567890',
    amount: 100.00,
    reason: 'Customer requested refund'
});

// Get available gateways
const gateways = await paygate.getAvailableGateways();
```

### Auto-Initialization

```html
<!-- Auto-initialize forms with data attributes -->
<form data-paygate-form>
    <input type="email" name="email" required>
    <input type="number" name="amount" required>
    <input type="hidden" name="provider" value="paystack">
    <button type="submit">Pay Now</button>
</form>

<!-- Initialize with data attributes -->
<div data-paygate 
     data-base-url="/api/paygate"
     data-default-provider="paystack"
     data-default-currency="NGN">
    <!-- Payment form content -->
</div>
```

### Event Handling

```javascript
// Custom event handlers
paygate.onSuccess = function(response) {
    // Handle successful payment
    showSuccessMessage('Payment completed successfully!');
    redirectToSuccessPage(response.reference);
};

paygate.onError = function(response) {
    // Handle payment error
    showErrorMessage(response.message);
    logError(response);
};

paygate.onPending = function(response) {
    // Handle pending payment
    showPendingMessage('Payment is being processed...');
};
```

## Examples

### Complete Payment Flow

```php
// 1. Initiate Payment
$response = Http::post('/api/paygate/initiate', [
    'provider' => 'paystack',
    'amount' => 250.00,
    'email' => 'customer@example.com',
    'reference' => 'TXN_1234567890',
    'redirect_url' => 'https://yoursite.com/callback'
]);

$checkoutUrl = $response->json('data.checkout_url');

// 2. Redirect user to checkout
return redirect($checkoutUrl);

// 3. Handle callback
Route::get('/callback', function(Request $request) {
    $reference = $request->get('reference');
    
    $verification = Http::get("/api/paygate/verify/{$reference}");
    
    if ($verification->json('status') === 'successful') {
        // Payment successful
        return view('payment.success', $verification->json());
    } else {
        // Payment failed
        return view('payment.failed', $verification->json());
    }
});
```

### Using with Laravel Events

```php
// In your EventServiceProvider
protected $listen = [
    PaymentCompleted::class => [
        SendConfirmationEmail::class,
        UpdateOrderStatus::class,
    ],
];

// Event listener
class SendConfirmationEmail
{
    public function handle(PaymentCompleted $event)
    {
        $payment = $event->payment;
        
        Mail::to($payment->customer_email)->send(
            new PaymentConfirmation($payment)
        );
    }
}
```

### Database Queries

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

## Rate Limiting

The API includes rate limiting to prevent abuse:

- **Default**: 60 requests per minute
- **Configurable**: Set `PAYGATE_RATE_LIMIT` in your `.env`
- **Headers**: Rate limit information in response headers

## Caching

Response caching is available for better performance:

- **Enable**: Set `PAYGATE_ENABLE_CACHING=true`
- **TTL**: Configure `PAYGATE_CACHE_TTL` (default: 300 seconds)
- **Cache Keys**: Automatic cache key generation

## Security Considerations

1. **API Keys**: Store securely in environment variables
2. **Webhook Secrets**: Use strong, unique secrets
3. **HTTPS**: Always use HTTPS in production
4. **Validation**: All inputs are validated
5. **Rate Limiting**: Built-in protection against abuse
6. **Logging**: Comprehensive audit logging available
