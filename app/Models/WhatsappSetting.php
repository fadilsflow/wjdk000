<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class WhatsappSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipient_phone',
        'critical_condition_template',
        'spray_start_template',
        'spray_stop_template',
        'rain_detected_template',
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
}
