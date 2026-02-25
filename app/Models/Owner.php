<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Owner extends Model
{
    protected $fillable = ['full_name', 'email', 'phone', 'agency_id'];

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
}