<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Traits\KoreErpBelongsToAgency;

class Invoice extends Model
{
    use KoreErpBelongsToAgency;
    protected $fillable = [
        'invoice_number',
        'reference',
        'client_type',
        'client_id',
        'agency_id',
        'issue_date',
        'due_date',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'balance_due',
        'currency',
        'payment_terms',
        'notes',
        'terms_and_conditions',
        'late_fee_amount',
        'late_fee_percentage',
        'reminder_sent_at',
        'overdue_notified_at'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'reminder_sent_at' => 'datetime',
        'overdue_notified_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'late_fee_amount' => 'decimal:2',
        'late_fee_percentage' => 'decimal:2',
    ];

    public function client()
    {
        return $this->morphTo();
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(CreditNote::class);
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->status === 'paid' || $this->balance_due <= 0;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date < now() && !$this->is_paid;
    }

    public function getIsPartiallyPaidAttribute(): bool
    {
        return $this->paid_amount > 0 && $this->paid_amount < $this->total_amount;
    }

    public function getDaysOverdueAttribute(): int
    {
        return $this->is_overdue ? now()->diffInDays($this->due_date) : 0;
    }

    public function getFormattedInvoiceNumberAttribute(): string
    {
        return 'FA-' . str_pad($this->invoice_number, 6, '0', STR_PAD_LEFT);
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->items()->sum('total_amount');
        $this->tax_amount = $this->items()->sum('tax_amount');
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;
        $this->balance_due = $this->total_amount - $this->paid_amount;
        $this->save();
    }

    public function applyLateFees(): void
    {
        if ($this->is_overdue && $this->late_fee_percentage > 0) {
            $lateFee = ($this->balance_due * $this->late_fee_percentage) / 100;
            $this->late_fee_amount = $lateFee;
            $this->total_amount += $lateFee;
            $this->balance_due += $lateFee;
            $this->save();
        }
    }

    public function recordPayment(float $amount, string $method = 'cash', array $details = []): InvoicePayment
    {
        $payment = $this->payments()->create([
            'amount' => $amount,
            'payment_method' => $method,
            'payment_date' => now(),
            'reference' => $details['reference'] ?? null,
            'notes' => $details['notes'] ?? null,
            'agency_id' => $this->agency_id,
        ]);

        $this->paid_amount += $amount;
        $this->balance_due -= $amount;
        
        if ($this->balance_due <= 0) {
            $this->status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partially_paid';
        }
        
        $this->save();

        return $payment;
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereNotIn('status', ['paid', 'cancelled']);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('issue_date', [$startDate, $endDate]);
    }

    public function scopeByClient($query, $clientType, $clientId)
    {
        return $query->where('client_type', $clientType)
                    ->where('client_id', $clientId);
    }
}