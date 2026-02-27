<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoicePayment extends Model
{
    protected $fillable = [
        'invoice_id',
        'amount',
        'payment_method',
        'payment_date',
        'reference',
        'notes',
        'transaction_id',
        'processed_by',
        'agency_id'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'processed_by');
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'cash' => 'Espèces',
            'check' => 'Chèque',
            'bank_transfer' => 'Virement bancaire',
            'credit_card' => 'Carte de crédit',
            'debit_card' => 'Carte de débit',
            'online' => 'Paiement en ligne',
            'other' => 'Autre',
            default => $this->payment_method,
        };
    }
}