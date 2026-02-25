<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tenant extends Model
{
    protected $fillable = ['full_name', 'email', 'phone', 'lease_start', 'lease_end', 'property_id', 'agency_id'];

    public function property(): BelongsTo { return $this->belongsTo(Property::class); }
    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
}