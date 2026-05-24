<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class Device extends Model
{
    use HasFactory;

    // mode: 'manual' | 'automatic'
    // sprayer_status: 'on' | 'off'
    protected $fillable = [
        'name',
        'location',
        'api_key',
        'mode',
        'sprayer_status',
    ];

    protected $hidden = [
        'api_key',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function thresholdSetting(): HasOne
    {
        return $this->hasOne(ThresholdSetting::class);
    }

    public function sensorReadings(): HasMany
    {
        return $this->hasMany(SensorReading::class);
    }

    public function sprayLogs(): HasMany
    {
        return $this->hasMany(SprayLog::class);
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }
}
