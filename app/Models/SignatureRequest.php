<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SignatureRequest extends Model
{
    protected $fillable = [
        'document_id',
        'requestable_id',
        'requestable_type',
        'agency_id',
        'envelope_id',
        'provider',
        'status',
        'request_type',
        'title',
        'description',
        'signers',
        'signed_document_path',
        'request_date',
        'sent_date',
        'completed_date',
        'expired_date',
        'reminder_sent_at',
        'webhook_data',
        'metadata',
        'created_by'
    ];

    protected $casts = [
        'signers' => 'array',
        'webhook_data' => 'array',
        'metadata' => 'array',
        'request_date' => 'datetime',
        'sent_date' => 'datetime',
        'completed_date' => 'datetime',
        'expired_date' => 'datetime',
        'reminder_sent_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function requestable(): MorphTo
    {
        return $this->morphTo();
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending';
    }

    public function getIsSentAttribute(): bool
    {
        return $this->status === 'sent';
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->status === 'expired';
    }

    public function getIsCancelledAttribute(): bool
    {
        return $this->status === 'cancelled';
    }

    public function getProgressAttribute(): int
    {
        if (!$this->signers || empty($this->signers)) {
            return 0;
        }

        $signedCount = collect($this->signers)->where('status', 'completed')->count();
        $totalCount = count($this->signers);

        return $totalCount > 0 ? round(($signedCount / $totalCount) * 100) : 0;
    }

    public function getNextSignerAttribute()
    {
        if (!$this->signers || empty($this->signers)) {
            return null;
        }

        return collect($this->signers)->firstWhere('status', 'pending');
    }

    public function getCompletedSignersAttribute()
    {
        if (!$this->signers || empty($this->signers)) {
            return [];
        }

        return collect($this->signers)->where('status', 'completed')->values()->all();
    }

    public function getPendingSignersAttribute()
    {
        if (!$this->signers || empty($this->signers)) {
            return [];
        }

        return collect($this->signers)->where('status', 'pending')->values()->all();
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'sent']);
    }

    public function canBeReminded(): bool
    {
        return $this->status === 'sent' && 
               $this->reminder_sent_at && 
               $this->reminder_sent_at->diffInDays(now()) >= config('esignature.reminder_days', 3);
    }

    public function isExpired(): bool
    {
        return $this->expired_date && $this->expired_date->isPast();
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    public function scopeByRequestType($query, $type)
    {
        return $query->where('request_type', $type);
    }

    public function scopeExpiringSoon($query)
    {
        $days = config('esignature.expiration_days', 30);
        return $query->where('expired_date', '<=', now()->addDays($days))
                    ->where('expired_date', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expired_date', '<', now());
    }
}