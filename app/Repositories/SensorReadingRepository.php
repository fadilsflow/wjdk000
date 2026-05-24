<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\SensorReading;

final class SensorReadingRepository
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): SensorReading
    {
        /** @var SensorReading $sensorReading */
        $sensorReading = SensorReading::query()->create($data);

        return $sensorReading;
    }
}
