<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortalPayment extends Model
{
    protected $fillable = [
        'client_portal_id',
        'invoice_id',
        'amount',
        'currency',
        'payment_method',
        'payment_provider',
        'transaction_id',
        'status',
        'paid_at',
        'payment_data',
        'refund_amount',
        'refunded_at',
        'refund_reason',
        'ip_address',
        'user_agent',
        'agency_id'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
        'payment_data' => 'array',
    ];

    public function clientPortal(): BelongsTo
    {
        return $this->belongsTo(ClientPortal::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function getIsSuccessfulAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function getIsRefundedAttribute(): bool
    {
        return $this->refund_amount > 0 && $this->refunded_at !== null;
    }

    public function getIsPartiallyRefundedAttribute(): bool
    {
        return $this->refund_amount > 0 && $this->refund_amount < $this->amount;
    }

    public function getIsFullyRefundedAttribute(): bool
    {
        return $this->refund_amount === $this->amount && $this->refunded_at !== null;
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'credit_card' => 'Carte de crédit',
            'debit_card' => 'Carte de débit',
            'bank_transfer' => 'Virement bancaire',
            'paypal' => 'PayPal',
            'stripe' => 'Stripe',
            'apple_pay' => 'Apple Pay',
            'google_pay' => 'Google Pay',
            'check' => 'Chèque',
            'cash' => 'Espèces',
            default => ucfirst(str_replace('_', ' ', $this->payment_method))
        };
    }

    public function getPaymentProviderLabelAttribute(): string
    {
        return match($this->payment_provider) {
            'stripe' => 'Stripe',
            'paypal' => 'PayPal',
            'razorpay' => 'Razorpay',
            'paytm' => 'PayTM',
            'ccavenue' => 'CCAvenue',
            'payu' => 'PayU',
            'mollie' => 'Mollie',
            'square' => 'Square',
            default => ucfirst($this->payment_provider)
        };
    }

    public function canBeRefunded(): bool
    {
        return $this->status === 'completed' && 
               $this->refund_amount < $this->amount && 
               $this->paid_at && 
               $this->paid_at->diffInDays(now()) <= 30; // 30 jours pour remboursement
    }

    public function refund(float $amount, string $reason = null): bool
    {
        if (!$this->canBeRefunded()) {
            return false;
        }

        $newRefundAmount = min($amount, $this->amount - $this->refund_amount);
        
        $this->update([
            'refund_amount' => $this->refund_amount + $newRefundAmount,
            'refunded_at' => now(),
            'refund_reason' => $reason ?? 'Remboursement demandé',
        ]);

        return true;
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByClientPortal($query, int $clientPortalId)
    {
        return $query->where('client_portal_id', $clientPortalId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('paid_at', [$startDate, $endDate]);
    }

    public function scopeByPaymentProvider($query, string $provider)
    {
        return $query->where('payment_provider', $provider);
    }

    public function scopeRefunded($query)
    {
        return $query->where('refund_amount', '>', 0);
    }
}