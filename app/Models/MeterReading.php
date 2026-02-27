<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeterReading extends Model
{
    protected $fillable = [
        'meter_id',
        'reading_date',
        'reading_value',
        'previous_reading',
        'consumption',
        'reading_type',
        'read_by',
        'notes',
        'image_path',
        'is_estimated'
    ];

    protected $casts = [
        'reading_date' => 'date',
        'reading_value' => 'decimal:3',
        'previous_reading' => 'decimal:3',
        'consumption' => 'decimal:3',
        'is_estimated' => 'boolean',
    ];

    public function meter(): BelongsTo
    {
        return $this->belongsTo(Meter::class);
    }

    public function getConsumptionCostAttribute(): float
    {
        if (!$this->meter || !$this->consumption) {
            return 0;
        }

        // Calcul basé sur le type d'utilité et les tarifs moyens
        $rate = match($this->meter->utility_type) {
            'electricity' => 0.15, // €/kWh
            'water' => 3.50,        // €/m³
            'gas' => 0.08,          // €/kWh
            'heating' => 0.12,      // €/kWh
            default => 0
        };

        return $this->consumption * $rate;
    }

    public function getFormattedConsumptionAttribute(): string
    {
        $unit = match($this->meter->utility_type) {
            'electricity' => 'kWh',
            'water' => 'm³',
            'gas' => 'm³',
            'heating' => 'kWh',
            default => 'unités'
        };

        return number_format($this->consumption, 2) . ' ' . $unit;
    }
}