<?php

namespace Obrainwave\Paygate\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference',
        'provider',
        'provider_reference',
        'amount',
        'charged_amount',
        'currency',
        'status',
        'payment_method',
        'customer_email',
        'customer_name',
        'customer_phone',
        'redirect_url',
        'checkout_url',
        'access_code',
        'metadata',
        'provider_response',
        'initiated_at',
        'completed_at',
        'failed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'charged_amount' => 'decimal:2',
        'metadata' => 'array',
        'provider_response' => 'array',
        'initiated_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    protected $dates = [
        'initiated_at',
        'completed_at',
        'failed_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SUCCESSFUL = 'successful';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';

    // Provider constants
    const PROVIDER_PAYSTACK = 'paystack';
    const PROVIDER_GTPAY = 'gtpay';
    const PROVIDER_FLUTTERWAVE = 'flutterwave';
    const PROVIDER_MONNIFY = 'monnify';

    /**
     * Scope for successful payments
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_SUCCESSFUL);
    }

    /**
     * Scope for failed payments
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope for pending payments
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope by provider
     */
    public function scopeByProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope by reference
     */
    public function scopeByReference($query, $reference)
    {
        return $query->where('reference', $reference);
    }

    /**
     * Check if payment is successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESSFUL;
    }

    /**
     * Check if payment is failed
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ' . strtoupper($this->currency);
    }

    /**
     * Get payment duration
     */
    public function getDurationAttribute(): ?int
    {
        if ($this->completed_at && $this->initiated_at) {
            return $this->initiated_at->diffInSeconds($this->completed_at);
        }
        
        return null;
    }
}
