<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Guarantor extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'nationality',
        'profession',
        'monthly_income',
        'address',
        'city',
        'postal_code',
        'country',
        'id_number',
        'id_type',
        'relationship_to_tenant',
        'guarantee_amount',
        'guarantee_type',
        'status',
        'notes',
        'agency_id',
        'documents_verified',
        'verified_at',
        'verified_by'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'monthly_income' => 'decimal:2',
        'guarantee_amount' => 'decimal:2',
        'documents_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'verified_by');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAgeAttribute(): int
    {
        return $this->date_of_birth ? $this->date_of_birth->age : 0;
    }

    public function getIsVerifiedAttribute(): bool
    {
        return $this->documents_verified && $this->verified_at !== null;
    }

    public function getGuaranteeRatioAttribute(): float
    {
        return $this->monthly_income > 0 ? round(($this->guarantee_amount / $this->monthly_income) * 100, 2) : 0;
    }

    public function getActiveTenantsCountAttribute(): int
    {
        return $this->tenants()->whereHas('unit', function ($query) {
            $query->where('status', 'occupied');
        })->count();
    }

    public function scopeVerified($query)
    {
        return $query->where('documents_verified', true)
                    ->whereNotNull('verified_at');
    }

    public function scopePendingVerification($query)
    {
        return $query->where('documents_verified', false)
                    ->orWhereNull('verified_at');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}