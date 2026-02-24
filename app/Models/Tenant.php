<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'lease_start',
        'lease_end',
        'property_id',
        'lease_document', // Ajouté avec la virgule correcte
    ];

    /**
     * Un locataire est lié à une propriété.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}