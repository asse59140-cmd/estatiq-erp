<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'address',
        'price',
        'type',
        'status',
        'description',
        'owner_id',
        'images', // Ajouté proprement ici
    ];

    protected $casts = [
        'images' => 'array', // Indispensable pour stocker plusieurs photos
    ];

    /**
     * Une propriété appartient à un propriétaire.
     */
    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }
}