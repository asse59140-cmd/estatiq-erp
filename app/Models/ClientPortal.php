<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientPortal extends Model
{
    protected $fillable = [
        'user_id',
        'client_type',
        'client_id',
        'portal_type',
        'access_code',
        'is_active',
        'last_login_at',
        'login_count',
        'preferences',
        'api_token',
        'token_expires_at',
        'agency_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'login_count' => 'integer',
        'preferences' => 'array',
        'token_expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->morphTo();
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function portalActivities(): HasMany
    {
        return $this->hasMany(PortalActivity::class);
    }

    public function portalPayments(): HasMany
    {
        return $this->hasMany(PortalPayment::class);
    }

    public function portalTickets(): HasMany
    {
        return $this->hasMany(PortalTicket::class);
    }

    public function getClientNameAttribute(): string
    {
        if (!$this->client) {
            return 'Client inconnu';
        }

        return match($this->client_type) {
            'App\\Models\\Tenant' => $this->client->full_name ?? 'Locataire',
            'App\\Models\\Owner' => $this->client->full_name ?? 'Propriétaire',
            'App\\Models\\Guarantor' => $this->client->full_name ?? 'Garant',
            default => 'Client'
        };
    }

    public function getPortalTypeLabelAttribute(): string
    {
        return match($this->portal_type) {
            'tenant' => 'Portail Locataire',
            'owner' => 'Portail Propriétaire',
            'guarantor' => 'Portail Garant',
            'both' => 'Portail Complet',
            default => 'Portail'
        };
    }

    public function generateAccessCode(): string
    {
        return strtoupper(bin2hex(random_bytes(4)));
    }

    public function generateApiToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function recordLogin(): void
    {
        $this->update([
            'last_login_at' => now(),
            'login_count' => $this->login_count + 1,
        ]);
    }

    public function recordActivity(string $action, array $data = []): void
    {
        $this->portalActivities()->create([
            'action' => $action,
            'data' => $data,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'agency_id' => $this->agency_id,
        ]);
    }

    public function isTokenValid(): bool
    {
        if (!$this->api_token || !$this->token_expires_at) {
            return false;
        }

        return $this->token_expires_at->isFuture();
    }

    public function refreshToken(): void
    {
        $this->update([
            'api_token' => $this->generateApiToken(),
            'token_expires_at' => now()->addDays(30),
        ]);
    }

    public function revokeToken(): void
    {
        $this->update([
            'api_token' => null,
            'token_expires_at' => null,
        ]);
    }

    public function getPreferences(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->preferences ?? [];
        }

        return data_get($this->preferences, $key, $default);
    }

    public function setPreferences(string $key, $value): void
    {
        $preferences = $this->preferences ?? [];
        data_set($preferences, $key, $value);
        $this->update(['preferences' => $preferences]);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByPortalType($query, string $type)
    {
        return $query->where('portal_type', $type);
    }

    public function scopeByClientType($query, string $type)
    {
        return $query->where('client_type', $type);
    }

    public function scopeRecentlyActive($query, int $days = 30)
    {
        return $query->where('last_login_at', '>=', now()->subDays($days));
    }
}