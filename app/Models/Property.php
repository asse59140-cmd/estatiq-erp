<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\KoreErpBelongsToAgency;
use App\Traits\HasDocuments;

class Property extends Model
{
    use HasFactory, SoftDeletes, KoreErpBelongsToAgency, HasDocuments;

    /**
     * La table associée au modèle
     *
     * @var string
     */
    protected $table = 'kore_erp_properties';

    /**
     * Les attributs qui peuvent être assignés en masse
     *
     * @var array
     */
    protected $fillable = [
        'agency_id',
        'title',
        'description',
        'type',
        'status',
        'price',
        'currency',
        'area',
        'area_unit',
        'bedrooms',
        'bathrooms',
        'parking_spaces',
        'floor_number',
        'total_floors',
        'year_built',
        'furnished',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'latitude',
        'longitude',
        'owner_id',
        'agent_id',
        'featured',
        'featured_until',
        'view_count',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'custom_fields',
        'is_active',
        'available_from',
        'available_until',
        'created_by',
        'updated_by',
    ];

    /**
     * Les attributs qui doivent être castés
     *
     * @var array
     */
    protected $casts = [
        'price' => 'decimal:2',
        'area' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'featured' => 'boolean',
        'featured_until' => 'datetime',
        'view_count' => 'integer',
        'furnished' => 'boolean',
        'year_built' => 'integer',
        'is_active' => 'boolean',
        'available_from' => 'date',
        'available_until' => 'date',
        'custom_fields' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Les attributs qui doivent être cachés pour la sérialisation
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at',
    ];

    /**
     * Relation avec l'agence
     */
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * Relation avec le propriétaire
     */
    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    /**
     * Relation avec l'agent
     */
    public function agent()
    {
        return $this->belongsTo(Employee::class, 'agent_id');
    }

    /**
     * Relation avec les images
     */
    public function images()
    {
        return $this->hasMany(PropertyImage::class);
    }

    /**
     * Relation avec les visites
     */
    public function viewings()
    {
        return $this->hasMany(Viewing::class);
    }

    /**
     * Relation avec les favoris
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Relation avec les enquêtes
     */
    public function inquiries()
    {
        return $this->hasMany(Inquiry::class);
    }

    /**
     * Relation avec l'utilisateur qui a créé
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation avec l'utilisateur qui a mis à jour
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope pour les propriétés actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les propriétés en vedette
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true)
                    ->where('featured_until', '>', now());
    }

    /**
     * Scope pour les propriétés disponibles
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')
                    ->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('available_from')
                          ->orWhere('available_from', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('available_until')
                          ->orWhere('available_until', '>', now());
                    });
    }

    /**
     * Scope pour les propriétés par type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour les propriétés par prix
     */
    public function scopePriceRange($query, $min, $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    /**
     * Scope pour les propriétés par ville
     */
    public function scopeByCity($query, $city)
    {
        return $query->where('city', 'like', "%{$city}%");
    }

    /**
     * Incrémenter le compteur de vues
     */
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    /**
     * Vérifier si la propriété est en vedette
     */
    public function isFeatured()
    {
        return $this->featured && $this->featured_until && $this->featured_until->isFuture();
    }

    /**
     * Vérifier si la propriété est disponible
     */
    public function isAvailable()
    {
        if ($this->status !== 'available' || !$this->is_active) {
            return false;
        }

        $now = now();
        
        if ($this->available_from && $this->available_from > $now) {
            return false;
        }
        
        if ($this->available_until && $this->available_until <= $now) {
            return false;
        }
        
        return true;
    }

    /**
     * Obtenir le prix formaté
     */
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2) . ' ' . $this->currency;
    }

    /**
     * Obtenir l'adresse complète
     */
    public function getFullAddressAttribute()
    {
        $address = [];
        
        if ($this->address) {
            $address[] = $this->address;
        }
        
        if ($this->city) {
            $address[] = $this->city;
        }
        
        if ($this->state) {
            $address[] = $this->state;
        }
        
        if ($this->zip_code) {
            $address[] = $this->zip_code;
        }
        
        if ($this->country) {
            $address[] = $this->country;
        }
        
        return implode(', ', $address);
    }

    /**
     * Obtenir les attributs SEO
     */
    public function getSeoAttributesAttribute()
    {
        return [
            'title' => $this->meta_title ?: $this->title,
            'description' => $this->meta_description ?: Str::limit($this->description, 160),
            'keywords' => $this->meta_keywords,
            'image' => $this->images->first()?->url,
        ];
    }

    /**
     * Obtenir les statistiques
     */
    public function getStatisticsAttribute()
    {
        return [
            'view_count' => $this->view_count,
            'inquiry_count' => $this->inquiries()->count(),
            'viewing_count' => $this->viewings()->count(),
            'favorite_count' => $this->favorites()->count(),
        ];
    }

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($property) {
            if (Auth::check()) {
                $property->created_by = Auth::id();
            }
        });

        static::updating(function ($property) {
            if (Auth::check()) {
                $property->updated_by = Auth::id();
            }
        });
    }
}