<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Traits\KoreErpBelongsToAgency;

class Unit extends Model
{
    use KoreErpBelongsToAgency;
    protected $fillable = [
        'unit_number',
        'building_id',
        'floor',
        'unit_type',
        'bedrooms',
        'bathrooms',
        'area_sqm',
        'monthly_rent',
        'deposit_amount',
        'furnished',
        'balcony',
        'parking_space',
        'status',
        'description',
        'amenities',
        'images',
        'agency_id'
    ];

    protected $casts = [
        'area_sqm' => 'decimal:2',
        'monthly_rent' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'furnished' => 'boolean',
        'balcony' => 'boolean',
        'parking_space' => 'boolean',
        'amenities' => 'array',
        'images' => 'array',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'floor' => 'integer',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function tenant(): HasOne
    {
        return $this->hasOne(Tenant::class);
    }

    public function meters(): HasMany
    {
        return $this->hasMany(Meter::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getCurrentTenantAttribute()
    {
        return $this->tenant;
    }

    public function getIsOccupiedAttribute(): bool
    {
        return $this->tenant !== null && $this->tenant->lease_end >= now();
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->status === 'available' && !$this->is_occupied;
    }

    public function getNextAvailableDateAttribute()
    {
        if ($this->tenant && $this->tenant->lease_end) {
            return $this->tenant->lease_end->addDay();
        }
        return now();
    }

    public function getTotalExpensesAttribute(): float
    {
        return $this->expenses()->sum('amount') ?? 0;
    }

    public function getTotalRevenueAttribute(): float
    {
        return $this->payments()->where('status', 'completed')->sum('amount') ?? 0;
    }
}