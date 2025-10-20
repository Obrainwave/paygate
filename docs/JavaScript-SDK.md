# Paygate JavaScript SDK

The Paygate JavaScript SDK provides a comprehensive client-side solution for handling payments in your web applications.

## Table of Contents

- [Installation](#installation)
- [Initialization](#initialization)
- [Payment Methods](#payment-methods)
- [Event Handling](#event-handling)
- [Form Integration](#form-integration)
- [Styling](#styling)
- [Configuration](#configuration)
- [Examples](#examples)

## Installation

### CDN (Recommended)

```html
<!-- Include CSS -->
<link rel="stylesheet" href="{{ asset('vendor/paygate/css/paygate.css') }}">

<!-- Include JavaScript -->
<script src="{{ asset('vendor/paygate/js/paygate.js') }}"></script>
```

### NPM (Alternative)

```bash
npm install @obrainwave/paygate-js
```

```javascript
import Paygate from '@obrainwave/paygate-js';
```

## Initialization

### Basic Initialization

```javascript
const paygate = new Paygate({
    baseUrl: '/api/paygate',
    defaultProvider: 'paystack',
    defaultCurrency: 'NGN'
});
```

### Advanced Initialization

```javascript
const paygate = new Paygate({
    baseUrl: '/api/paygate',
    defaultProvider: 'paystack',
    defaultCurrency: 'NGN',
    csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    
    // Event handlers
    onSuccess: function(response) {
        console.log('Payment successful:', response);
        showSuccessMessage('Payment completed successfully!');
        redirectToSuccessPage(response.reference);
    },
    
    onError: function(response) {
        console.error('Payment failed:', response);
        showErrorMessage(response.message);
    },
    
    onPending: function(response) {
        console.log('Payment pending:', response);
        showPendingMessage('Payment is being processed...');
    }
});
```

## Payment Methods

### Initiate Payment

```javascript
// Basic payment initiation
const response = await paygate.initiatePayment({
    provider: 'paystack',
    amount: 250.00,
    email: 'customer@example.com',
    reference: 'TXN_1234567890'
});

// Advanced payment initiation
const response = await paygate.initiatePayment({
    provider: 'paystack',
    amount: 250.00,
    email: 'customer@example.com',
    reference: 'TXN_1234567890',
    name: 'John Doe',
    phone_number: '08012345678',
    currency: 'NGN',
    redirect_url: 'https://yoursite.com/callback',
    description: 'Payment for order #12345',
    metadata: {
        order_id: '12345',
        customer_id: '67890'
    }
});
```

### Verify Payment

```javascript
// Verify payment by reference
const verification = await paygate.verifyPayment('TXN_1234567890');

// Verify with specific provider
const verification = await paygate.verifyPayment('TXN_1234567890', 'paystack');
```

### Get Payment Status

```javascript
const status = await paygate.getPaymentStatus('TXN_1234567890');
console.log('Payment status:', status.status);
```

### Refund Payment

```javascript
// Full refund
const refund = await paygate.refundPayment({
    reference: 'TXN_1234567890',
    reason: 'Customer requested refund'
});

// Partial refund
const refund = await paygate.refundPayment({
    reference: 'TXN_1234567890',
    amount: 100.00,
    reason: 'Partial refund for damaged item'
});
```

### Get Available Gateways

```javascript
const gateways = await paygate.getAvailableGateways();
console.log('Available gateways:', gateways.data);
```

## Event Handling

### Success Handler

```javascript
paygate.onSuccess = function(response) {
    // Payment completed successfully
    console.log('Payment successful:', response);
    
    // Show success message
    showSuccessMessage('Payment completed successfully!');
    
    // Redirect to success page
    if (response.reference) {
        window.location.href = `/success/${response.reference}`;
    }
    
    // Clear form
    clearPaymentForm();
    
    // Send analytics event
    gtag('event', 'purchase', {
        transaction_id: response.reference,
        value: response.amount,
        currency: response.currency
    });
};
```

### Error Handler

```javascript
paygate.onError = function(response) {
    // Payment failed
    console.error('Payment failed:', response);
    
    // Show error message
    showErrorMessage(response.message || 'Payment failed. Please try again.');
    
    // Log error for debugging
    logError('Payment Error', {
        reference: response.reference,
        error: response.message,
        provider: response.provider
    });
    
    // Re-enable form
    enablePaymentForm();
};
```

### Pending Handler

```javascript
paygate.onPending = function(response) {
    // Payment is being processed
    console.log('Payment pending:', response);
    
    // Show pending message
    showPendingMessage('Payment is being processed...');
    
    // Disable form to prevent double submission
    disablePaymentForm();
    
    // Start polling for status updates
    startStatusPolling(response.reference);
};
```

## Form Integration

### Auto-Initialization

The SDK automatically initializes forms with the `data-paygate-form` attribute:

```html
<form data-paygate-form>
    <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" name="email" id="email" required>
    </div>
    
    <div class="form-group">
        <label for="amount">Amount</label>
        <input type="number" name="amount" id="amount" step="0.01" required>
    </div>
    
    <div class="form-group">
        <label for="provider">Payment Provider</label>
        <select name="provider" id="provider">
            <option value="paystack">Paystack</option>
            <option value="flutterwave">Flutterwave</option>
            <option value="monnify">Monnify</option>
        </select>
    </div>
    
    <button type="submit">Pay Now</button>
</form>
```

### Manual Form Handling

```javascript
// Handle form submission manually
document.getElementById('payment-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    // Add default values
    data.currency = data.currency || 'NGN';
    data.reference = data.reference || paygate.generateReference();
    
    // Initiate payment
    const response = await paygate.initiatePayment(data);
});
```

### Form Validation

```javascript
// Validate form before submission
function validatePaymentForm(formData) {
    const errors = [];
    
    // Validate email
    if (!paygate.validateEmail(formData.email)) {
        errors.push('Please enter a valid email address');
    }
    
    // Validate amount
    if (!paygate.validateAmount(formData.amount)) {
        errors.push('Please enter a valid amount');
    }
    
    // Validate required fields
    const requiredFields = ['email', 'amount'];
    requiredFields.forEach(field => {
        if (!formData[field]) {
            errors.push(`${field} is required`);
        }
    });
    
    return errors;
}
```

## Styling

### CSS Classes

The SDK provides CSS classes for styling:

```css
/* Loading indicator */
#paygate-loading {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
}

/* Payment form */
.paygate-payment-form {
    max-width: 500px;
    margin: 0 auto;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
}

/* Notifications */
.paygate-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 20px;
    border-radius: 5px;
    color: white;
    z-index: 9999;
}

.paygate-notification-success {
    background: #28a745;
}

.paygate-notification-error {
    background: #dc3545;
}
```

### Custom Styling

```css
/* Override default styles */
.paygate-payment-form {
    background: #f8f9fa;
    border: 2px solid #007bff;
}

.paygate-payment-form .btn {
    background: linear-gradient(45deg, #007bff, #0056b3);
    border: none;
    border-radius: 25px;
    padding: 12px 30px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.paygate-payment-form .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
}
```

## Configuration

### Environment Variables

```javascript
const paygate = new Paygate({
    baseUrl: process.env.PAYGATE_API_URL || '/api/paygate',
    defaultProvider: process.env.PAYGATE_DEFAULT_PROVIDER || 'paystack',
    defaultCurrency: process.env.PAYGATE_DEFAULT_CURRENCY || 'NGN'
});
```

### Runtime Configuration

```javascript
// Update configuration at runtime
paygate.baseUrl = '/api/v2/paygate';
paygate.defaultProvider = 'flutterwave';
paygate.defaultCurrency = 'USD';

// Update event handlers
paygate.onSuccess = newSuccessHandler;
paygate.onError = newErrorHandler;
```

## Examples

### Complete Payment Flow

```html
<!DOCTYPE html>
<html>
<head>
    <title>Payment Example</title>
    <link rel="stylesheet" href="{{ asset('vendor/paygate/css/paygate.css') }}">
</head>
<body>
    <div class="container">
        <h1>Make a Payment</h1>
        
        <form id="payment-form" data-paygate-form>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" required>
            </div>
            
            <div class="form-group">
                <label for="amount">Amount (NGN)</label>
                <input type="number" name="amount" id="amount" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="provider">Payment Provider</label>
                <select name="provider" id="provider">
                    <option value="paystack">Paystack</option>
                    <option value="flutterwave">Flutterwave</option>
                    <option value="monnify">Monnify</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Pay Now</button>
        </form>
        
        <div id="paygate-loading" style="display: none;">
            <div class="paygate-loading-spinner"></div>
        </div>
    </div>
    
    <script src="{{ asset('vendor/paygate/js/paygate.js') }}"></script>
    <script>
        // Initialize Paygate
        const paygate = new Paygate({
            baseUrl: '/api/paygate',
            defaultProvider: 'paystack',
            defaultCurrency: 'NGN',
            
            onSuccess: function(response) {
                alert('Payment successful! Reference: ' + response.reference);
                window.location.href = '/success/' + response.reference;
            },
            
            onError: function(response) {
                alert('Payment failed: ' + response.message);
            },
            
            onPending: function(response) {
                console.log('Payment pending...');
            }
        });
    </script>
</body>
</html>
```

### React Integration

```jsx
import React, { useState, useEffect } from 'react';

const PaymentForm = () => {
    const [paygate, setPaygate] = useState(null);
    const [loading, setLoading] = useState(false);
    
    useEffect(() => {
        // Initialize Paygate when component mounts
        const pg = new Paygate({
            baseUrl: '/api/paygate',
            onSuccess: (response) => {
                setLoading(false);
                alert('Payment successful!');
            },
            onError: (response) => {
                setLoading(false);
                alert('Payment failed: ' + response.message);
            }
        });
        
        setPaygate(pg);
    }, []);
    
    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        
        await paygate.initiatePayment(data);
    };
    
    return (
        <form onSubmit={handleSubmit}>
            <input type="email" name="email" required />
            <input type="number" name="amount" required />
            <button type="submit" disabled={loading}>
                {loading ? 'Processing...' : 'Pay Now'}
            </button>
        </form>
    );
};
```

### Vue.js Integration

```vue
<template>
    <form @submit.prevent="handleSubmit">
        <input v-model="form.email" type="email" required />
        <input v-model="form.amount" type="number" required />
        <button type="submit" :disabled="loading">
            {{ loading ? 'Processing...' : 'Pay Now' }}
        </button>
    </form>
</template>

<script>
export default {
    data() {
        return {
            paygate: null,
            loading: false,
            form: {
                email: '',
                amount: ''
            }
        };
    },
    
    mounted() {
        this.paygate = new Paygate({
            baseUrl: '/api/paygate',
            onSuccess: (response) => {
                this.loading = false;
                this.$emit('success', response);
            },
            onError: (response) => {
                this.loading = false;
                this.$emit('error', response);
            }
        });
    },
    
    methods: {
        async handleSubmit() {
            this.loading = true;
            await this.paygate.initiatePayment(this.form);
        }
    }
};
</script>
```

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+
- Internet Explorer 11+ (with polyfills)

## Troubleshooting

### Common Issues

1. **CSRF Token Missing**
   ```javascript
   // Make sure CSRF token is available
   const token = document.querySelector('meta[name="csrf-token"]');
   if (!token) {
       console.error('CSRF token not found');
   }
   ```

2. **CORS Issues**
   ```javascript
   // Check if baseUrl is correct
   const paygate = new Paygate({
       baseUrl: window.location.origin + '/api/paygate'
   });
   ```

3. **Form Not Submitting**
   ```html
   <!-- Make sure form has data-paygate-form attribute -->
   <form data-paygate-form>
       <!-- form fields -->
   </form>
   ```

### Debug Mode

```javascript
// Enable debug mode
const paygate = new Paygate({
    baseUrl: '/api/paygate',
    debug: true // Enable console logging
});
```

## License

This JavaScript SDK is part of the Paygate Laravel package and is licensed under the MIT License.
