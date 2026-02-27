<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\KoreErpBelongsToAgency;

class Tenant extends Model
{
    use KoreErpBelongsToAgency;
    protected $fillable = [
        'full_name', 
        'email', 
        'phone', 
        'lease_start', 
        'lease_end', 
        'property_id', // Pour compatibilité descendante
        'unit_id', // Nouveau lien vers Unit
        'agency_id',
        'emergency_contact',
        'emergency_phone',
        'nationality',
        'profession',
        'monthly_income',
        'guarantor_id'
    ];

    protected $casts = [
        'lease_start' => 'date',
        'lease_end' => 'date',
        'monthly_income' => 'decimal:2',
    ];

    public function property(): BelongsTo { return $this->belongsTo(Property::class); }
    public function unit(): BelongsTo { return $this->belongsTo(Unit::class); }
    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function guarantor(): BelongsTo { return $this->belongsTo(Guarantor::class); }
}