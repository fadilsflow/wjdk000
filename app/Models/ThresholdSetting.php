<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ThresholdSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'min_soil_moisture',
        'max_temperature',
        'min_air_humidity',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_soil_moisture' => 'float',
            'max_temperature' => 'float',
            'min_air_humidity' => 'float',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
