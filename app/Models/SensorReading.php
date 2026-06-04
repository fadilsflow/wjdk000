<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SensorReading extends Model
{
    use HasFactory;

    // rain_status: 'rain' | 'no_rain'
    // sprayer_status: 'on' | 'off'
    // condition_status: 'normal' | 'waspada' | 'kritis'
    protected $fillable = [
        'device_id',
        'temperature',
        'air_humidity',
        'soil_moisture',
        'soil_raw',
        'rain_status',
        'rain_raw',
        'sprayer_status',
        'simulation_mode',
        'condition_status',
        'recorded_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'temperature' => 'float',
            'air_humidity' => 'float',
            'soil_moisture' => 'float',
            'soil_raw' => 'integer',
            'rain_raw' => 'integer',
            'simulation_mode' => 'boolean',
            'recorded_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
