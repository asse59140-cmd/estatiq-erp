<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait KoreErpBelongsToAgency
{
    /**
     * Boot the trait
     */
    protected static function bootKoreErpBelongsToAgency(): void
    {
        static::addGlobalScope('agency', function (Builder $builder) {
            if (Auth::check() && Auth::user()->agency_id) {
                $builder->where($builder->getModel()->getTable() . '.agency_id', Auth::user()->agency_id);
            }
        });

        static::creating(function (Model $model) {
            if (Auth::check() && Auth::user()->agency_id && !$model->agency_id) {
                $model->agency_id = Auth::user()->agency_id;
            }
        });
    }

    /**
     * Get the agency relationship
     */
    public function agency()
    {
        return $this->belongsTo(\App\Models\Agency::class);
    }

    /**
     * Scope to get records for a specific agency
     */
    public function scopeForAgency(Builder $query, int $agencyId): Builder
    {
        return $query->where('agency_id', $agencyId);
    }

    /**
     * Get records without agency scope
     */
    public function scopeWithoutAgencyScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('agency');
    }

    /**
     * Get all records for all agencies (admin only)
     */
    public function scopeAllAgencies(Builder $query): Builder
    {
        return $query->withoutGlobalScope('agency');
    }
}