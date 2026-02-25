<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = ['tenant_id', 'amount', 'payment_date', 'method', 'status', 'agency_id'];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
}