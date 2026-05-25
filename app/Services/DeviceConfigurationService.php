<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Device;
use App\Repositories\DeviceRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

    /**
     * @param  array<string, mixed>  $data
     */
    public function createDevice(array $data): Device
    {
        return DB::transaction(function () use ($data): Device {
            $device = $this->deviceRepository->create([
                'name'           => $data['name'],
                'location'       => $data['location'],
                'api_key'        => Str::random(32),
                'mode'           => 'automatic',
                'sprayer_status' => 'off',
            ]);

            $device->thresholdSetting()->create([
                'min_soil_moisture' => 40.0,
                'max_temperature'   => 35.0,
                'min_air_humidity'  => 60.0,
            ]);

            return $device;
        });
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

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateDevice(Device $device, array $data): void
    {
        $this->deviceRepository->update($device, [
            'name'     => $data['name'],
            'location' => $data['location'],
        ]);
    }
}
