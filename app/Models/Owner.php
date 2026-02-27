<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\KoreErpBelongsToAgency;

class Owner extends Model
{
    use KoreErpBelongsToAgency;
    protected $fillable = ['full_name', 'email', 'phone', 'agency_id'];

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
}