<?php

namespace Obrainwave\Paygate\Plugins;

use Obrainwave\Paygate\Contracts\PluginInterface;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailNotificationPlugin implements PluginInterface
{
    protected array $config = [];
    protected string $name = 'EmailNotificationPlugin';
    protected string $version = '1.0.0';
    protected string $description = 'Sends email notifications for payment events';

    public function __construct()
    {
        $this->config = [
            'enabled' => true,
            'admin_email' => config('mail.admin_email', 'admin@example.com'),
            'notify_success' => true,
            'notify_failure' => true,
            'notify_refund' => true,
            'template' => 'paygate::emails.payment-notification'
        ];
    }

    /**
     * Get plugin name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get plugin version
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Get plugin description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Initialize plugin
     */
    public function initialize(): void
    {
        Log::info('Email notification plugin initialized');
    }

    /**
     * Register plugin hooks
     */
    public function registerHooks(): array
    {
        return [
            'payment.initiated',
            'payment.completed',
            'payment.failed',
            'payment.refunded'
        ];
    }

    /**
     * Handle plugin hook
     */
    public function handleHook(string $hook, array $data = []): mixed
    {
        switch ($hook) {
            case 'payment.initiated':
                return $this->handlePaymentInitiated($data);
            
            case 'payment.completed':
                return $this->handlePaymentCompleted($data);
            
            case 'payment.failed':
                return $this->handlePaymentFailed($data);
            
            case 'payment.refunded':
                return $this->handlePaymentRefunded($data);
            
            default:
                return null;
        }
    }

    /**
     * Check if plugin is enabled
     */
    public function isEnabled(): bool
    {
        return $this->config['enabled'] ?? false;
    }

    /**
     * Get plugin configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set plugin configuration
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Handle payment initiated hook
     */
    protected function handlePaymentInitiated(array $data): void
    {
        if (!$this->config['notify_success']) {
            return;
        }

        try {
            $this->sendEmail([
                'subject' => 'Payment Initiated',
                'template' => 'payment-initiated',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send payment initiated email', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
        }
    }

    /**
     * Handle payment completed hook
     */
    protected function handlePaymentCompleted(array $data): void
    {
        if (!$this->config['notify_success']) {
            return;
        }

        try {
            $this->sendEmail([
                'subject' => 'Payment Completed Successfully',
                'template' => 'payment-completed',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send payment completed email', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
        }
    }

    /**
     * Handle payment failed hook
     */
    protected function handlePaymentFailed(array $data): void
    {
        if (!$this->config['notify_failure']) {
            return;
        }

        try {
            $this->sendEmail([
                'subject' => 'Payment Failed',
                'template' => 'payment-failed',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send payment failed email', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
        }
    }

    /**
     * Handle payment refunded hook
     */
    protected function handlePaymentRefunded(array $data): void
    {
        if (!$this->config['notify_refund']) {
            return;
        }

        try {
            $this->sendEmail([
                'subject' => 'Payment Refunded',
                'template' => 'payment-refunded',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send payment refunded email', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
        }
    }

    /**
     * Send email notification
     */
    protected function sendEmail(array $emailData): void
    {
        $adminEmail = $this->config['admin_email'];
        $template = $this->config['template'] . '.' . $emailData['template'];
        
        Mail::send($template, $emailData['data'], function ($message) use ($adminEmail, $emailData) {
            $message->to($adminEmail)
                   ->subject($emailData['subject']);
        });
    }
}
