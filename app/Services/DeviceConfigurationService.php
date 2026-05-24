<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Device;
use App\Repositories\DeviceRepository;

final class DeviceConfigurationService
{
    public function __construct(
        private readonly DeviceRepository $deviceRepository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getIndexData(): array
    {
        $devices = $this->deviceRepository->getAllWithThresholds();
        $selectedDevice = $devices->first();

        return [
            'devices' => $devices
                ->map(static fn (Device $device): array => [
                    'id' => $device->id,
                    'name' => $device->name,
                    'location' => $device->location,
                    'api_key' => $device->getAttributes()['api_key'],
                    'mode' => $device->mode,
                    'sprayer_status' => $device->sprayer_status,
                ])
                ->all(),
            'thresholds' => [
                'device_id' => $selectedDevice?->id,
                'min_soil_moisture' => $selectedDevice?->thresholdSetting?->min_soil_moisture,
                'max_temperature' => $selectedDevice?->thresholdSetting?->max_temperature,
                'min_air_humidity' => $selectedDevice?->thresholdSetting?->min_air_humidity,
            ],
        ];
    }
}
