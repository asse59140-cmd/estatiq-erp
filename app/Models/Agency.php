<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Agency extends Model
{
    protected $fillable = ['name', 'slug'];

    // Une agence possède plusieurs utilisateurs (agents)
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    // Une agence possède ses propres données
    public function properties(): HasMany { return $this->hasMany(Property::class); }
    public function owners(): HasMany { return $this->hasMany(Owner::class); }
    public function tenants(): HasMany { return $this->hasMany(Tenant::class); }
}