<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Traits\KoreErpBelongsToAgency;
use App\Traits\HasDocuments;

class Building extends Model
{
    use KoreErpBelongsToAgency, HasDocuments;

    protected $table = 'kore_erp_buildings';

    protected $fillable = [
        'name',
        'address',
        'city',
        'postal_code',
        'country',
        'building_type',
        'construction_year',
        'total_floors',
        'description',
        'amenities',
        'parking_spaces',
        'elevator_count',
        'energy_rating',
        'owner_id',
        'agency_id',
        'lat',
        'lng',
        'images'
    ];

    protected $casts = [
        'amenities' => 'array',
        'images' => 'array',
        'construction_year' => 'integer',
        'total_floors' => 'integer',
        'parking_spaces' => 'integer',
        'elevator_count' => 'integer',
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function meters(): HasMany
    {
        return $this->hasMany(Meter::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'documentable_id')
            ->where('documentable_type', Building::class);
    }

    public function getOccupancyRateAttribute(): float
    {
        $totalUnits = $this->units()->count();
        if ($totalUnits === 0) {
            return 0.0;
        }

        $occupiedUnits = $this->units()
            ->whereHas('leases', function ($query) {
                $query->where('status', 'active')
                      ->where('start_date', '<=', now())
                      ->where('end_date', '>=', now());
            })
            ->count();

        return round(($occupiedUnits / $totalUnits) * 100, 2);
    }

    public function getAverageRentAttribute(): float
    {
        return $this->units()
            ->whereHas('leases', function ($query) {
                $query->where('status', 'active');
            })
            ->avg('current_rent') ?: 0;
    }

    public function getTotalRevenueAttribute(): float
    {
        return $this->units()
            ->whereHas('leases', function ($query) {
                $query->where('status', 'active');
            })
            ->sum('current_rent') ?: 0;
    }

    public function getMaintenanceCostAttribute(): float
    {
        return $this->maintenanceRequests()
            ->where('status', 'completed')
            ->sum('cost') ?: 0;
    }

    public function getAgeAttribute(): int
    {
        return $this->construction_year ? (now()->year - $this->construction_year) : 0;
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->postal_code,
            $this->country
        ]);
        
        return implode(', ', $parts);
    }

    public function getAmenitiesListAttribute(): array
    {
        return $this->amenities ?? [];
    }

    public function getEnergyEfficiencyClassAttribute(): string
    {
        return match(true) {
            $this->energy_rating >= 90 => 'A',
            $this->energy_rating >= 80 => 'B',
            $this->energy_rating >= 70 => 'C',
            $this->energy_rating >= 60 => 'D',
            $this->energy_rating >= 50 => 'E',
            default => 'F'
        };
    }

    public function getStatisticsAttribute(): array
    {
        return [
            'total_units' => $this->units()->count(),
            'occupied_units' => $this->units()->whereHas('leases', fn($q) => $q->where('status', 'active'))->count(),
            'vacant_units' => $this->units()->whereDoesntHave('leases', fn($q) => $q->where('status', 'active'))->count(),
            'occupancy_rate' => $this->occupancy_rate,
            'average_rent' => $this->average_rent,
            'total_revenue' => $this->total_revenue,
            'maintenance_cost' => $this->maintenance_cost,
            'age' => $this->age,
            'energy_class' => $this->energy_efficiency_class,
        ];
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('building_type', $type);
    }

    public function scopeByCity($query, string $city)
    {
        return $query->where('city', 'like', "%{$city}%");
    }

    public function scopeByOwner($query, int $ownerId)
    {
        return $query->where('owner_id', $ownerId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeWithHighOccupancy($query, float $threshold = 80)
    {
        return $query->whereHas('units', function ($query) use ($threshold) {
            $query->selectRaw('buildings.*, COUNT(CASE WHEN leases.status = "active" THEN 1 END) * 100.0 / COUNT(*) as occupancy_rate')
                  ->leftJoin('leases', 'units.id', '=', 'leases.unit_id')
                  ->groupBy('buildings.id')
                  ->having('occupancy_rate', '>=', $threshold);
        });
    }

    public function scopeByEnergyRating($query, string $rating)
    {
        return $query->where('energy_rating', $rating);
    }

    public function scopeByAgeRange($query, int $minAge, int $maxAge)
    {
        $currentYear = now()->year;
        return $query->whereBetween('construction_year', [$currentYear - $maxAge, $currentYear - $minAge]);
    }

    public function scopeWithAmenities($query, array $amenities)
    {
        return $query->whereJsonContains('amenities', $amenities);
    }

    public function scopeByMaintenanceCost($query, float $minCost, float $maxCost = null)
    {
        return $query->whereHas('maintenanceRequests', function ($query) use ($minCost, $maxCost) {
            $query->selectRaw('building_id, SUM(cost) as total_cost')
                  ->where('status', 'completed')
                  ->groupBy('building_id')
                  ->when($maxCost, fn($q) => $q->havingBetween('total_cost', [$minCost, $maxCost]))
                  ->when(!$maxCost, fn($q) => $q->having('total_cost', '>=', $minCost));
        });
    }

    public function scopeByRevenueRange($query, float $minRevenue, float $maxRevenue = null)
    {
        return $query->whereHas('units.leases', function ($query) use ($minRevenue, $maxRevenue) {
            $query->selectRaw('buildings.id, SUM(monthly_rent) as total_revenue')
                  ->where('leases.status', 'active')
                  ->groupBy('buildings.id')
                  ->when($maxRevenue, fn($q) => $q->havingBetween('total_revenue', [$minRevenue, $maxRevenue]))
                  ->when(!$maxRevenue, fn($q) => $q->having('total_revenue', '>=', $minRevenue));
        });
    }
}