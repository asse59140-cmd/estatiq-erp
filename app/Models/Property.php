<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Property extends Model
{
    // On ajoute agency_id dans les champs autorisés
    protected $fillable = ['title', 'address', 'description', 'price', 'type', 'status', 'owner_id', 'agency_id'];

    public function owner(): BelongsTo { return $this->belongsTo(Owner::class); }
    
    // Le lien vers l'agence
    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
}