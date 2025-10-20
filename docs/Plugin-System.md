# Paygate Plugin System

The Paygate plugin system allows you to extend the package's functionality without modifying the core code. This document explains how to create, register, and manage plugins.

## Table of Contents

- [Plugin Architecture](#plugin-architecture)
- [Creating a Plugin](#creating-a-plugin)
- [Plugin Interface](#plugin-interface)
- [Available Hooks](#available-hooks)
- [Plugin Management](#plugin-management)
- [Built-in Plugins](#built-in-plugins)
- [Examples](#examples)
- [Best Practices](#best-practices)

## Plugin Architecture

The plugin system is built on a hook-based architecture where plugins can:

- **Listen to Events**: Respond to payment lifecycle events
- **Modify Behavior**: Intercept and modify payment processing
- **Add Features**: Extend functionality with custom features
- **Integrate Services**: Connect with external services

## Creating a Plugin

### Basic Plugin Structure

```php
<?php

namespace App\Plugins;

use Obrainwave\Paygate\Contracts\PluginInterface;

class MyCustomPlugin implements PluginInterface
{
    protected array $config = [];
    protected string $name = 'MyCustomPlugin';
    protected string $version = '1.0.0';
    protected string $description = 'My custom plugin description';

    public function __construct()
    {
        $this->config = [
            'enabled' => true,
            'setting1' => 'default_value',
            'setting2' => true
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function initialize(): void
    {
        // Plugin initialization logic
    }

    public function registerHooks(): array
    {
        return [
            'payment.initiated',
            'payment.completed',
            'payment.failed'
        ];
    }

    public function handleHook(string $hook, array $data = []): mixed
    {
        switch ($hook) {
            case 'payment.initiated':
                return $this->handlePaymentInitiated($data);
            
            case 'payment.completed':
                return $this->handlePaymentCompleted($data);
            
            case 'payment.failed':
                return $this->handlePaymentFailed($data);
            
            default:
                return null;
        }
    }

    public function isEnabled(): bool
    {
        return $this->config['enabled'] ?? false;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    protected function handlePaymentInitiated(array $data): void
    {
        // Handle payment initiated event
    }

    protected function handlePaymentCompleted(array $data): void
    {
        // Handle payment completed event
    }

    protected function handlePaymentFailed(array $data): void
    {
        // Handle payment failed event
    }
}
```

## Plugin Interface

All plugins must implement the `PluginInterface`:

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

### Required Methods

- **`getName()`**: Returns the plugin name
- **`getVersion()`**: Returns the plugin version
- **`getDescription()`**: Returns the plugin description
- **`initialize()`**: Called when plugin is loaded
- **`registerHooks()`**: Returns array of hooks to listen to
- **`handleHook()`**: Handles hook execution
- **`isEnabled()`**: Checks if plugin is enabled
- **`getConfig()`**: Returns plugin configuration
- **`setConfig()`**: Sets plugin configuration

## Available Hooks

### Payment Lifecycle Hooks

```php
// Payment initiated
'payment.initiated' => [
    'provider' => 'paystack',
    'amount' => 250.00,
    'email' => 'customer@example.com',
    'reference' => 'TXN_1234567890'
];

// Payment completed
'payment.completed' => [
    'provider' => 'paystack',
    'amount' => 250.00,
    'email' => 'customer@example.com',
    'reference' => 'TXN_1234567890',
    'status' => 'successful'
];

// Payment failed
'payment.failed' => [
    'provider' => 'paystack',
    'amount' => 250.00,
    'email' => 'customer@example.com',
    'reference' => 'TXN_1234567890',
    'status' => 'failed',
    'error' => 'Insufficient funds'
];

// Payment refunded
'payment.refunded' => [
    'provider' => 'paystack',
    'amount' => 100.00,
    'email' => 'customer@example.com',
    'reference' => 'TXN_1234567890',
    'refund_reference' => 'REF_1234567890'
];
```

### System Hooks

```php
// Before payment processing
'payment.before_process' => $paymentData;

// After payment processing
'payment.after_process' => $paymentResult;

// Before webhook processing
'webhook.before_process' => $webhookData;

// After webhook processing
'webhook.after_process' => $webhookResult;

// Before refund processing
'refund.before_process' => $refundData;

// After refund processing
'refund.after_process' => $refundResult;
```

## Plugin Management

### Registering Plugins

#### Via Configuration

```php
// config/paygate.php
'plugins' => [
    \App\Plugins\MyCustomPlugin::class => [
        'enabled' => true,
        'setting1' => 'value1',
        'setting2' => true
    ],
    \App\Plugins\AnotherPlugin::class => [
        'enabled' => false,
        'custom_setting' => 'value'
    ]
];
```

#### Programmatically

```php
use Obrainwave\Paygate\Services\PluginManager;

$pluginManager = app(PluginManager::class);

// Register a plugin
$plugin = new MyCustomPlugin();
$pluginManager->registerPlugin($plugin);

// Unregister a plugin
$pluginManager->unregisterPlugin('MyCustomPlugin');
```

### Managing Plugins

```php
// Enable/disable plugins
$pluginManager->enablePlugin('MyCustomPlugin');
$pluginManager->disablePlugin('MyCustomPlugin');

// Get plugin information
$plugin = $pluginManager->getPlugin('MyCustomPlugin');
$config = $pluginManager->getPluginConfig('MyCustomPlugin');

// Update plugin configuration
$pluginManager->setPluginConfig('MyCustomPlugin', [
    'enabled' => true,
    'new_setting' => 'new_value'
]);

// Get plugin statistics
$stats = $pluginManager->getPluginStatistics();
```

## Built-in Plugins

### Email Notification Plugin

Sends email notifications for payment events:

```php
// config/paygate.php
'plugins' => [
    \Obrainwave\Paygate\Plugins\EmailNotificationPlugin::class => [
        'enabled' => true,
        'admin_email' => 'admin@example.com',
        'notify_success' => true,
        'notify_failure' => true,
        'notify_refund' => true
    ]
];
```

### Custom Notification Plugin

```php
<?php

namespace App\Plugins;

use Obrainwave\Paygate\Contracts\PluginInterface;
use Illuminate\Support\Facades\Notification;

class NotificationPlugin implements PluginInterface
{
    protected array $config = [];

    public function __construct()
    {
        $this->config = [
            'enabled' => true,
            'channels' => ['mail', 'slack', 'sms'],
            'recipients' => ['admin@example.com']
        ];
    }

    public function getName(): string
    {
        return 'NotificationPlugin';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getDescription(): string
    {
        return 'Sends notifications via multiple channels';
    }

    public function initialize(): void
    {
        // Initialize notification channels
    }

    public function registerHooks(): array
    {
        return [
            'payment.completed',
            'payment.failed',
            'payment.refunded'
        ];
    }

    public function handleHook(string $hook, array $data = []): mixed
    {
        switch ($hook) {
            case 'payment.completed':
                $this->sendSuccessNotification($data);
                break;
            
            case 'payment.failed':
                $this->sendFailureNotification($data);
                break;
            
            case 'payment.refunded':
                $this->sendRefundNotification($data);
                break;
        }
        
        return null;
    }

    public function isEnabled(): bool
    {
        return $this->config['enabled'] ?? false;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    protected function sendSuccessNotification(array $data): void
    {
        $message = "Payment completed successfully!\n";
        $message .= "Reference: {$data['reference']}\n";
        $message .= "Amount: {$data['amount']}\n";
        $message .= "Provider: {$data['provider']}";

        $this->sendNotification('Payment Success', $message);
    }

    protected function sendFailureNotification(array $data): void
    {
        $message = "Payment failed!\n";
        $message .= "Reference: {$data['reference']}\n";
        $message .= "Amount: {$data['amount']}\n";
        $message .= "Error: {$data['error']}";

        $this->sendNotification('Payment Failed', $message);
    }

    protected function sendRefundNotification(array $data): void
    {
        $message = "Payment refunded!\n";
        $message .= "Reference: {$data['reference']}\n";
        $message .= "Refund Amount: {$data['amount']}\n";
        $message .= "Refund Reference: {$data['refund_reference']}";

        $this->sendNotification('Payment Refunded', $message);
    }

    protected function sendNotification(string $title, string $message): void
    {
        $channels = $this->config['channels'] ?? ['mail'];
        
        foreach ($channels as $channel) {
            switch ($channel) {
                case 'mail':
                    $this->sendEmailNotification($title, $message);
                    break;
                
                case 'slack':
                    $this->sendSlackNotification($title, $message);
                    break;
                
                case 'sms':
                    $this->sendSmsNotification($title, $message);
                    break;
            }
        }
    }

    protected function sendEmailNotification(string $title, string $message): void
    {
        // Send email notification
        foreach ($this->config['recipients'] as $recipient) {
            Mail::raw($message, function ($mail) use ($recipient, $title) {
                $mail->to($recipient)
                     ->subject($title);
            });
        }
    }

    protected function sendSlackNotification(string $title, string $message): void
    {
        // Send Slack notification
        // Implementation depends on your Slack integration
    }

    protected function sendSmsNotification(string $title, string $message): void
    {
        // Send SMS notification
        // Implementation depends on your SMS provider
    }
}
```

## Examples

### Analytics Plugin

```php
<?php

namespace App\Plugins;

use Obrainwave\Paygate\Contracts\PluginInterface;
use Illuminate\Support\Facades\DB;

class AnalyticsPlugin implements PluginInterface
{
    protected array $config = [];

    public function __construct()
    {
        $this->config = [
            'enabled' => true,
            'track_conversions' => true,
            'track_errors' => true
        ];
    }

    public function getName(): string
    {
        return 'AnalyticsPlugin';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getDescription(): string
    {
        return 'Tracks payment analytics and metrics';
    }

    public function initialize(): void
    {
        // Initialize analytics tracking
    }

    public function registerHooks(): array
    {
        return [
            'payment.initiated',
            'payment.completed',
            'payment.failed'
        ];
    }

    public function handleHook(string $hook, array $data = []): mixed
    {
        switch ($hook) {
            case 'payment.initiated':
                $this->trackPaymentInitiated($data);
                break;
            
            case 'payment.completed':
                $this->trackPaymentCompleted($data);
                break;
            
            case 'payment.failed':
                $this->trackPaymentFailed($data);
                break;
        }
        
        return null;
    }

    public function isEnabled(): bool
    {
        return $this->config['enabled'] ?? false;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    protected function trackPaymentInitiated(array $data): void
    {
        DB::table('payment_analytics')->insert([
            'event' => 'initiated',
            'provider' => $data['provider'],
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'NGN',
            'created_at' => now()
        ]);
    }

    protected function trackPaymentCompleted(array $data): void
    {
        DB::table('payment_analytics')->insert([
            'event' => 'completed',
            'provider' => $data['provider'],
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'NGN',
            'created_at' => now()
        ]);
    }

    protected function trackPaymentFailed(array $data): void
    {
        DB::table('payment_analytics')->insert([
            'event' => 'failed',
            'provider' => $data['provider'],
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'NGN',
            'error' => $data['error'] ?? null,
            'created_at' => now()
        ]);
    }
}
```

### Fraud Detection Plugin

```php
<?php

namespace App\Plugins;

use Obrainwave\Paygate\Contracts\PluginInterface;
use Obrainwave\Paygate\Services\FraudDetectionService;

class FraudDetectionPlugin implements PluginInterface
{
    protected array $config = [];
    protected FraudDetectionService $fraudService;

    public function __construct(FraudDetectionService $fraudService)
    {
        $this->fraudService = $fraudService;
        $this->config = [
            'enabled' => true,
            'auto_decline_threshold' => 10,
            'monitor_threshold' => 6
        ];
    }

    public function getName(): string
    {
        return 'FraudDetectionPlugin';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getDescription(): string
    {
        return 'Advanced fraud detection and prevention';
    }

    public function initialize(): void
    {
        // Initialize fraud detection
    }

    public function registerHooks(): array
    {
        return [
            'payment.before_process'
        ];
    }

    public function handleHook(string $hook, array $data = []): mixed
    {
        if ($hook === 'payment.before_process') {
            return $this->analyzePayment($data);
        }
        
        return null;
    }

    public function isEnabled(): bool
    {
        return $this->config['enabled'] ?? false;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    protected function analyzePayment(array $data): array
    {
        $riskAnalysis = $this->fraudService->analyzePayment($data);
        
        if ($riskAnalysis['recommendation'] === 'decline') {
            return [
                'approved' => false,
                'reason' => 'High fraud risk detected',
                'risk_score' => $riskAnalysis['risk_score'],
                'risk_factors' => $riskAnalysis['risk_factors']
            ];
        }
        
        if ($riskAnalysis['recommendation'] === 'review') {
            return [
                'approved' => true,
                'requires_review' => true,
                'risk_score' => $riskAnalysis['risk_score'],
                'risk_factors' => $riskAnalysis['risk_factors']
            ];
        }
        
        return [
            'approved' => true,
            'risk_score' => $riskAnalysis['risk_score']
        ];
    }
}
```

## Best Practices

### 1. Error Handling

```php
public function handleHook(string $hook, array $data = []): mixed
{
    try {
        switch ($hook) {
            case 'payment.completed':
                return $this->handlePaymentCompleted($data);
            // ... other cases
        }
    } catch (\Exception $e) {
        Log::error('Plugin error', [
            'plugin' => $this->getName(),
            'hook' => $hook,
            'error' => $e->getMessage()
        ]);
        
        return null;
    }
}
```

### 2. Configuration Validation

```php
public function setConfig(array $config): void
{
    $this->config = array_merge($this->config, $config);
    
    // Validate configuration
    $this->validateConfig();
}

protected function validateConfig(): void
{
    if (isset($this->config['required_setting']) && empty($this->config['required_setting'])) {
        throw new \InvalidArgumentException('required_setting is required');
    }
}
```

### 3. Performance Optimization

```php
public function handleHook(string $hook, array $data = []): mixed
{
    // Only process if plugin is enabled
    if (!$this->isEnabled()) {
        return null;
    }
    
    // Cache expensive operations
    $cacheKey = "plugin_{$this->getName()}_{$hook}_" . md5(serialize($data));
    
    return Cache::remember($cacheKey, 300, function() use ($hook, $data) {
        return $this->processHook($hook, $data);
    });
}
```

### 4. Logging

```php
protected function handlePaymentCompleted(array $data): void
{
    Log::info('Payment completed via plugin', [
        'plugin' => $this->getName(),
        'reference' => $data['reference'],
        'amount' => $data['amount']
    ]);
    
    // Process payment completion
}
```

## Troubleshooting

### Common Issues

1. **Plugin Not Loading**
   - Check if plugin class exists
   - Verify plugin implements PluginInterface
   - Check configuration syntax

2. **Hooks Not Firing**
   - Verify hook names are correct
   - Check if plugin is enabled
   - Ensure plugin is registered

3. **Performance Issues**
   - Use caching for expensive operations
   - Implement proper error handling
   - Monitor plugin execution time

### Debug Mode

```php
// Enable debug logging
Log::debug('Plugin hook executed', [
    'plugin' => $this->getName(),
    'hook' => $hook,
    'data' => $data
]);
```

## License

The plugin system is part of the Paygate Laravel package and is licensed under the MIT License.
