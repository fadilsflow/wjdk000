<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Device;
use App\Repositories\DeviceRepository;
use Illuminate\Support\Facades\DB;

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
        $device = $this->deviceRepository->findDashboardDevice();

        return [
            'device' => $device instanceof Device ? [
                'id' => $device->id,
                'name' => $device->name,
                'location' => $device->location,
                'api_key' => $device->getAttributes()['api_key'],
                'mode' => $device->mode,
                'sprayer_status' => $device->sprayer_status,
            ] : null,
            'thresholds' => [
                'device_id' => $device?->id,
                'min_soil_moisture' => $device?->thresholdSetting?->min_soil_moisture,
                'max_temperature' => $device?->thresholdSetting?->max_temperature,
                'min_air_humidity' => $device?->thresholdSetting?->min_air_humidity,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateThreshold(array $data): void
    {
        $device = $this->deviceRepository->findById((int) $data['device_id']);

        DB::transaction(function () use ($device, $data): void {
            $payload = [
                'min_soil_moisture' => (float) $data['min_soil_moisture'],
                'max_temperature'   => (float) $data['max_temperature'],
                'min_air_humidity'  => (float) $data['min_air_humidity'],
            ];

            if ($device->thresholdSetting !== null) {
                $device->thresholdSetting->update($payload);
            } else {
                $device->thresholdSetting()->create($payload);
            }
        });
    }
}
