<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meter extends Model
{
    protected $fillable = [
        'meter_number',
        'unit_id',
        'building_id',
        'agency_id',
        'meter_type',
        'utility_type',
        'installation_date',
        'initial_reading',
        'current_reading',
        'unit_of_measure',
        'multiplier',
        'status',
        'location_description',
        'supplier_name',
        'contract_number',
        'billing_frequency',
        'notes'
    ];

    protected $casts = [
        'installation_date' => 'date',
        'initial_reading' => 'decimal:3',
        'current_reading' => 'decimal:3',
        'multiplier' => 'decimal:2',
        'status' => 'string',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function readings(): HasMany
    {
        return $this->hasMany(MeterReading::class);
    }

    public function getConsumptionSinceLastReadingAttribute(): float
    {
        $lastReading = $this->readings()->latest('reading_date')->first();
        $previousReading = $this->readings()->latest('reading_date')->skip(1)->first();
        
        if ($lastReading && $previousReading) {
            return ($lastReading->reading_value - $previousReading->reading_value) * $this->multiplier;
        }
        
        return 0;
    }

    public function getMonthlyAverageConsumptionAttribute(): float
    {
        $readings = $this->readings()
            ->where('reading_date', '>=', now()->subMonths(12))
            ->orderBy('reading_date')
            ->get();
        
        if ($readings->count() < 2) {
            return 0;
        }
        
        $totalConsumption = 0;
        $readingCount = $readings->count();
        
        for ($i = 1; $i < $readingCount; $i++) {
            $consumption = ($readings[$i]->reading_value - $readings[$i-1]->reading_value) * $this->multiplier;
            $totalConsumption += $consumption;
        }
        
        return $readingCount > 1 ? $totalConsumption / ($readingCount - 1) : 0;
    }

    public function getLastReadingDateAttribute()
    {
        return $this->readings()->latest('reading_date')->first()?->reading_date;
    }

    public function getNextReadingDueDateAttribute()
    {
        if (!$this->last_reading_date) {
            return now();
        }
        
        $frequency = $this->billing_frequency;
        $lastDate = $this->last_reading_date;
        
        return match($frequency) {
            'monthly' => $lastDate->addMonth(),
            'bimonthly' => $lastDate->addMonths(2),
            'quarterly' => $lastDate->addQuarter(),
            'semiannual' => $lastDate->addMonths(6),
            'annual' => $lastDate->addYear(),
            default => $lastDate->addMonth()
        };
    }
}