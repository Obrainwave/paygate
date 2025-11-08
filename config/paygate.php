<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Package Information
    |--------------------------------------------------------------------------
    |
    | These settings define the basic information about the Paygate package.
    | The name is used for display purposes and the prefix is used for
    | route naming and configuration keys.
    |
    */

    'name' => 'Paygate',
    'prefix' => 'paygate',
    
    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure the default settings for the Paygate package.
    | These settings will be used when no specific configuration is provided
    | for individual payment operations.
    |
    */

    'default_currency' => env('PAYGATE_DEFAULT_CURRENCY', 'NGN'),
    'default_provider' => env('PAYGATE_DEFAULT_PROVIDER', 'paystack'),
    'enable_logging' => env('PAYGATE_ENABLE_LOGGING', true),
    'enable_caching' => env('PAYGATE_ENABLE_CACHING', false),
    'cache_ttl' => env('PAYGATE_CACHE_TTL', 300), // 5 minutes
    
    /*
    |--------------------------------------------------------------------------
    | Database Settings
    |--------------------------------------------------------------------------
    |
    | These options control how payment data is stored in the database.
    | When store_payments is enabled, all payment transactions will be
    | automatically stored in the database for tracking and analytics.
    |
    */

    'store_payments' => env('PAYGATE_STORE_PAYMENTS', false),
    'payment_model' => \Obrainwave\Paygate\Models\Payment::class,
    
    /*
    |--------------------------------------------------------------------------
    | Webhook Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure the webhook settings for receiving payment
    | notifications from payment gateways. Webhooks provide real-time
    | updates about payment status changes.
    |
    */

    'webhook_enabled' => env('PAYGATE_WEBHOOK_ENABLED', true),
    'webhook_route' => env('PAYGATE_WEBHOOK_ROUTE', 'paygate/webhook'),
    'webhook_middleware' => ['api'],
    
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | These options control the rate limiting for payment endpoints to prevent
    | abuse and ensure fair usage. Rate limiting helps protect your application
    | from excessive API calls and potential attacks.
    |
    */

    'rate_limit' => env('PAYGATE_RATE_LIMIT', 60), // requests per minute
    'rate_limit_per_minute' => env('PAYGATE_RATE_LIMIT_PER_MINUTE', 60),
    
    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the settings for each supported payment gateway.
    | Each gateway has its own API credentials and configuration options.
    | Make sure to set the correct credentials for your environment.
    |
    */

    'paystack' => [
        'base_url' => env('PAYSTACK_BASE_URL', 'https://api.paystack.co'),
        'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        'secret_key' => env('PAYSTACK_SECRET_KEY'),
        'webhook_secret' => env('PAYSTACK_WEBHOOK_SECRET'),
        'enabled' => env('PAYSTACK_ENABLED', true),
    ],
    
    'gtpay' => [
        'base_url' => env('GTPAY_BASE_URL', 'https://sandbox-api-d.squadco.com'),
        'public_key' => env('GTPAY_PUBLIC_KEY'),
        'secret_key' => env('GTPAY_SECRET_KEY'),
        'webhook_secret' => env('GTPAY_WEBHOOK_SECRET'),
        'enabled' => env('GTPAY_ENABLED', true),
    ],
    
    'flutterwave' => [
        'base_url' => env('FLUTTERWAVE_BASE_URL', 'https://api.flutterwave.com/v3'),
        'public_key' => env('FLUTTERWAVE_PUBLIC_KEY'),
        'secret_key' => env('FLUTTERWAVE_SECRET_KEY'),
        'webhook_secret' => env('FLUTTERWAVE_WEBHOOK_SECRET'),
        'enabled' => env('FLUTTERWAVE_ENABLED', true),
    ],
    
    'monnify' => [
        'base_url' => env('MONNIFY_BASE_URL', 'https://sandbox.monnify.com'),
        'api_key' => env('MONNIFY_API_KEY'),
        'secret_key' => env('MONNIFY_SECRET_KEY'),
        'webhook_secret' => env('MONNIFY_WEBHOOK_SECRET'),
        'enabled' => env('MONNIFY_ENABLED', true),
    ],
    
    'interswitch' => [
        'base_url' => env('INTERSWITCH_BASE_URL', 'https://sandbox.interswitch.com'),
        'client_id' => env('INTERSWITCH_CLIENT_ID'),
        'client_secret' => env('INTERSWITCH_CLIENT_SECRET'),
        'webhook_secret' => env('INTERSWITCH_WEBHOOK_SECRET'),
        'enabled' => env('INTERSWITCH_ENABLED', true),
    ],
    
    'remita' => [
        'base_url' => env('REMITA_BASE_URL', 'https://remitademo.net'),
        'merchant_id' => env('REMITA_MERCHANT_ID'),
        'api_key' => env('REMITA_API_KEY'),
        'service_type_id' => env('REMITA_SERVICE_TYPE_ID'),
        'webhook_secret' => env('REMITA_WEBHOOK_SECRET'),
        'enabled' => env('REMITA_ENABLED', true),
    ],
    
    'vtpass' => [
        'base_url' => env('VTPASS_BASE_URL', 'https://vtpass.com'),
        'api_key' => env('VTPASS_API_KEY'),
        'secret_key' => env('VTPASS_SECRET_KEY'),
        'webhook_secret' => env('VTPASS_WEBHOOK_SECRET'),
        'enabled' => env('VTPASS_ENABLED', true),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Payment Methods Configuration
    |--------------------------------------------------------------------------
    |
    | This array defines which payment methods are supported by each gateway.
    | You can customize this to enable or disable specific payment methods
    | for different gateways based on your business requirements.
    |
    */

    'payment_methods' => [
        'card' => ['paystack', 'gtpay', 'flutterwave', 'monnify', 'interswitch'],
        'bank' => ['paystack', 'gtpay', 'flutterwave', 'interswitch', 'remita'],
        'ussd' => ['paystack', 'gtpay', 'interswitch', 'remita'],
        'qr' => ['paystack', 'gtpay', 'interswitch'],
        'mobile_money' => ['paystack', 'flutterwave'],
        'bank_transfer' => ['paystack', 'gtpay', 'flutterwave', 'interswitch', 'remita'],
        'eft' => ['paystack'],
        'airtime' => ['vtpass'],
        'data' => ['vtpass'],
        'electricity' => ['vtpass'],
        'cable_tv' => ['vtpass'],
        'internet' => ['vtpass'],
        'education' => ['vtpass'],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Supported Currencies
    |--------------------------------------------------------------------------
    |
    | This array defines the currencies supported by the Paygate package.
    | Each currency is mapped to its full name for display purposes.
    | You can add or remove currencies based on your business needs.
    |
    */

    'supported_currencies' => [
        'NGN' => 'Nigerian Naira',
        'USD' => 'US Dollar',
        'EUR' => 'Euro',
        'GBP' => 'British Pound',
        'GHS' => 'Ghanaian Cedi',
        'KES' => 'Kenyan Shilling',
        'ZAR' => 'South African Rand',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | These options control the retry behavior for failed API calls to
    | payment gateways. Retry attempts help ensure reliable payment processing
    | by automatically retrying failed requests with exponential backoff.
    |
    */

    'retry_attempts' => env('PAYGATE_RETRY_ATTEMPTS', 3),
    'retry_delay' => env('PAYGATE_RETRY_DELAY', 1000), // milliseconds
    
    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | These options control the security features of the Paygate package.
    | Encryption helps protect sensitive payment data, while log masking
    | ensures that sensitive information is not exposed in application logs.
    |
    */

    'encrypt_sensitive_data' => env('PAYGATE_ENCRYPT_SENSITIVE_DATA', true),
    'mask_sensitive_logs' => env('PAYGATE_MASK_SENSITIVE_LOGS', true),
    
    /*
    |--------------------------------------------------------------------------
    | Plugin System
    |--------------------------------------------------------------------------
    |
    | Here you may configure the plugin system for extending Paygate
    | functionality. Plugins allow you to add custom features and
    | integrations without modifying the core package.
    |
    */

    'plugins' => [
        \Obrainwave\Paygate\Plugins\EmailNotificationPlugin::class => [
            'enabled' => env('PAYGATE_EMAIL_NOTIFICATIONS', true),
            'admin_email' => env('PAYGATE_ADMIN_EMAIL', 'admin@example.com'),
            'notify_success' => true,
            'notify_failure' => true,
            'notify_refund' => true,
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Fraud Detection
    |--------------------------------------------------------------------------
    |
    | These options control the fraud detection system to help identify
    | and prevent fraudulent payment transactions.
    |
    */

    'fraud_detection' => [
        'enabled' => env('PAYGATE_FRAUD_DETECTION', true),
        'risk_threshold' => env('PAYGATE_RISK_THRESHOLD', 6),
        'auto_decline_threshold' => env('PAYGATE_AUTO_DECLINE_THRESHOLD', 10),
        'monitor_mode' => env('PAYGATE_MONITOR_MODE', false),
    ],

];
