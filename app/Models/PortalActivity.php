<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortalActivity extends Model
{
    protected $fillable = [
        'client_portal_id',
        'action',
        'data',
        'ip_address',
        'user_agent',
        'agency_id'
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function clientPortal(): BelongsTo
    {
        return $this->belongsTo(ClientPortal::class);
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'login' => 'Connexion',
            'logout' => 'Déconnexion',
            'document_view' => 'Consultation de document',
            'document_download' => 'Téléchargement de document',
            'invoice_view' => 'Consultation de facture',
            'invoice_payment' => 'Paiement de facture',
            'ticket_create' => 'Création de ticket',
            'ticket_update' => 'Mise à jour de ticket',
            'profile_update' => 'Mise à jour du profil',
            'password_change' => 'Changement de mot de passe',
            'access_code_used' => 'Code d\'accès utilisé',
            'api_token_generated' => 'Token API généré',
            'signature_completed' => 'Signature complétée',
            'contract_accepted' => 'Contrat accepté',
            'payment_method_added' => 'Méthode de paiement ajoutée',
            'notification_read' => 'Notification lue',
            'preference_updated' => 'Préférence mise à jour',
            default => ucfirst(str_replace('_', ' ', $this->action))
        };
    }

    public function getActionIconAttribute(): string
    {
        return match($this->action) {
            'login' => 'heroicon-o-arrow-right-on-rectangle',
            'logout' => 'heroicon-o-arrow-left-on-rectangle',
            'document_view' => 'heroicon-o-eye',
            'document_download' => 'heroicon-o-arrow-down-tray',
            'invoice_view' => 'heroicon-o-document-text',
            'invoice_payment' => 'heroicon-o-currency-euro',
            'ticket_create' => 'heroicon-o-plus-circle',
            'ticket_update' => 'heroicon-o-pencil',
            'profile_update' => 'heroicon-o-user',
            'password_change' => 'heroicon-o-key',
            'access_code_used' => 'heroicon-o-lock-open',
            'api_token_generated' => 'heroicon-o-key',
            'signature_completed' => 'heroicon-o-pencil-square',
            'contract_accepted' => 'heroicon-o-hand-thumb-up',
            'payment_method_added' => 'heroicon-o-credit-card',
            'notification_read' => 'heroicon-o-bell',
            'preference_updated' => 'heroicon-o-cog-6-tooth',
            default => 'heroicon-o-information-circle'
        };
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByClientPortal($query, int $clientPortalId)
    {
        return $query->where('client_portal_id', $clientPortalId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}