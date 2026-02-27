<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortalTicketReply extends Model
{
    protected $fillable = [
        'portal_ticket_id',
        'content',
        'is_internal',
        'attachments',
        'sender_type',
        'sender_id',
        'agency_id'
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'attachments' => 'array',
    ];

    public function portalTicket(): BelongsTo
    {
        return $this->belongsTo(PortalTicket::class);
    }

    public function sender()
    {
        return $this->morphTo();
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function getSenderNameAttribute(): string
    {
        if (!$this->sender) {
            return 'Système';
        }

        return match($this->sender_type) {
            'App\\Models\\User' => $this->sender->name ?? 'Utilisateur',
            'App\\Models\\Employee' => $this->sender->full_name ?? 'Employé',
            'App\\Models\\Tenant' => $this->sender->full_name ?? 'Locataire',
            'App\\Models\\Owner' => $this->sender->full_name ?? 'Propriétaire',
            default => 'Utilisateur'
        };
    }

    public function getIsFromClientAttribute(): bool
    {
        return in_array($this->sender_type, [
            'App\\Models\\Tenant',
            'App\\Models\\Owner',
            'App\\Models\\Guarantor'
        ]);
    }

    public function getIsFromEmployeeAttribute(): bool
    {
        return $this->sender_type === 'App\\Models\\Employee';
    }

    public function scopeByTicket($query, int $ticketId)
    {
        return $query->where('portal_ticket_id', $ticketId);
    }

    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }

    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    public function scopeBySender($query, string $senderType, int $senderId)
    {
        return $query->where('sender_type', $senderType)
                    ->where('sender_id', $senderId);
    }
}