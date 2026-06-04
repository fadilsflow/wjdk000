<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Device;
use App\Models\SensorReading;
use App\Repositories\DeviceRepository;
use App\Repositories\SensorReadingRepository;

final class PublicSummaryService
{
    public function __construct(
        private readonly DeviceRepository $deviceRepository,
        private readonly SensorReadingRepository $sensorReadingRepository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getPublicSummaryData(): array
    {
        $device = $this->deviceRepository->findDashboardDevice();

        if (! $device instanceof Device) {
            return $this->emptySummaryData();
        }

        $latestReading = $this->sensorReadingRepository->findLatestForDevice($device);

        return [
            'sensor' => $this->buildSensorSummary($latestReading),
            'public_summary' => [
                'device_name' => $device->name,
                'location' => $device->location,
                'updated_at' => $latestReading?->recorded_at?->format('Y-m-d H:i:s'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptySummaryData(): array
    {
        return [
            'sensor' => [
                'temperature' => null,
                'air_humidity' => null,
                'soil_moisture' => null,
                'soil_raw' => null,
                'rain_status' => 'no_rain',
                'rain_raw' => null,
                'simulation_mode' => false,
                'condition_status' => 'normal',
                'recorded_at' => null,
            ],
            'public_summary' => [
                'device_name' => 'Belum ada perangkat',
                'location' => 'Brebes',
                'updated_at' => null,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSensorSummary(?SensorReading $latestReading): array
    {
        if (! $latestReading instanceof SensorReading) {
            return [
                'temperature' => null,
                'air_humidity' => null,
                'soil_moisture' => null,
                'soil_raw' => null,
                'rain_status' => 'no_rain',
                'rain_raw' => null,
                'simulation_mode' => false,
                'condition_status' => 'normal',
                'recorded_at' => null,
            ];
        }

        return [
            'temperature' => $latestReading->temperature,
            'air_humidity' => $latestReading->air_humidity,
            'soil_moisture' => $latestReading->soil_moisture,
            'soil_raw' => $latestReading->soil_raw,
            'rain_status' => $latestReading->rain_status,
            'rain_raw' => $latestReading->rain_raw,
            'simulation_mode' => $latestReading->simulation_mode,
            'condition_status' => $latestReading->condition_status,
            'recorded_at' => $latestReading->recorded_at?->format('Y-m-d H:i:s'),
        ];
    }
}
