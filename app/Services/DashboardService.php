<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Device;
use App\Models\NotificationLog;
use App\Models\SensorReading;
use App\Models\SprayLog;
use App\Repositories\DeviceRepository;
use App\Repositories\NotificationLogRepository;
use App\Repositories\SensorReadingRepository;
use App\Repositories\SprayLogRepository;
use Illuminate\Support\Collection;

final class DashboardService
{
    public function __construct(
        private readonly DeviceRepository $deviceRepository,
        private readonly SensorReadingRepository $sensorReadingRepository,
        private readonly SprayLogRepository $sprayLogRepository,
        private readonly NotificationLogRepository $notificationLogRepository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getDashboardData(): array
    {
        $device = $this->deviceRepository->findDashboardDevice();

        if (! $device instanceof Device) {
            return $this->emptyDashboardData();
        }

        $latestReading = $this->sensorReadingRepository->findLatestForDevice($device);
        $sensorHistory = $this->sensorReadingRepository->getRecentForDevice($device);

        return [
            'device' => [
                'name' => $device->name,
                'mode' => $device->mode,
            ],
            'sensor' => $this->buildSensorSummary($device, $latestReading),
            'thresholds' => [
                'min_soil_moisture' => $device->thresholdSetting?->min_soil_moisture,
                'max_temperature' => $device->thresholdSetting?->max_temperature,
                'min_air_humidity' => $device->thresholdSetting?->min_air_humidity,
            ],
            'chart' => $this->buildChartData($sensorHistory),
            'activities' => $this->buildActivities(
                $latestReading,
                $this->sprayLogRepository->getRecentForDevice($device),
                $this->notificationLogRepository->getRecentForDevice($device),
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyDashboardData(): array
    {
        return [
            'device' => [
                'name' => 'Belum ada perangkat',
                'mode' => 'automatic',
            ],
            'sensor' => [
                'temperature' => null,
                'air_humidity' => null,
                'soil_moisture' => null,
                'soil_raw' => null,
                'rain_status' => 'no_rain',
                'rain_raw' => null,
                'sprayer_status' => 'off',
                'simulation_mode' => false,
                'condition_status' => 'normal',
                'recorded_at' => null,
            ],
            'thresholds' => [
                'min_soil_moisture' => null,
                'max_temperature' => null,
                'min_air_humidity' => null,
            ],
            'chart' => [
                'labels' => [],
                'temperature' => [],
                'air_humidity' => [],
                'soil_moisture' => [],
            ],
            'activities' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSensorSummary(Device $device, ?SensorReading $latestReading): array
    {
        if (! $latestReading instanceof SensorReading) {
            return [
                'temperature' => null,
                'air_humidity' => null,
                'soil_moisture' => null,
                'soil_raw' => null,
                'rain_status' => 'no_rain',
                'rain_raw' => null,
                'sprayer_status' => $device->sprayer_status,
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
            'sprayer_status' => $device->sprayer_status,
            'simulation_mode' => $latestReading->simulation_mode,
            'condition_status' => $latestReading->condition_status,
            'recorded_at' => $latestReading->recorded_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @param  Collection<int, SensorReading>  $sensorHistory
     * @return array<string, array<int, float|int|string|null>>
     */
    private function buildChartData(Collection $sensorHistory): array
    {
        return [
            'labels' => $sensorHistory
                ->map(static fn (SensorReading $reading): string => $reading->recorded_at?->format('H:i') ?? '-')
                ->all(),
            'temperature' => $sensorHistory
                ->map(static fn (SensorReading $reading): ?float => $reading->temperature)
                ->all(),
            'air_humidity' => $sensorHistory
                ->map(static fn (SensorReading $reading): ?float => $reading->air_humidity)
                ->all(),
            'soil_moisture' => $sensorHistory
                ->map(static fn (SensorReading $reading): ?float => $reading->soil_moisture)
                ->all(),
        ];
    }

    /**
     * @param  Collection<int, SprayLog>  $sprayLogs
     * @param  Collection<int, NotificationLog>  $notificationLogs
     * @return array<int, array<string, string>>
     */
    private function buildActivities(
        ?SensorReading $latestReading,
        Collection $sprayLogs,
        Collection $notificationLogs,
    ): array {
        $activities = collect();

        if ($latestReading instanceof SensorReading) {
            $activities->push([
                'time' => $latestReading->recorded_at?->format('Y-m-d H:i:s') ?? '-',
                'sort_at' => $latestReading->recorded_at?->timestamp ?? 0,
                'type' => 'Sensor',
                'status' => ucfirst($latestReading->condition_status),
                'status_key' => $latestReading->condition_status,
                'description' => sprintf(
                    'Suhu %s°C, udara %s%%, tanah %s%% (raw %s), hujan %s (raw %s), mode data %s',
                    $latestReading->temperature,
                    $latestReading->air_humidity,
                    $latestReading->soil_moisture,
                    $latestReading->soil_raw ?? '-',
                    $latestReading->rain_status,
                    $latestReading->rain_raw ?? '-',
                    $latestReading->simulation_mode ? 'simulasi' : 'real',
                ),
            ]);
        }

        $sprayActivities = $sprayLogs->map(
            static fn (SprayLog $sprayLog): array => [
                'time' => $sprayLog->created_at?->format('Y-m-d H:i:s') ?? '-',
                'sort_at' => $sprayLog->created_at?->timestamp ?? 0,
                'type' => 'Sprayer',
                'status' => strtoupper($sprayLog->status),
                'status_key' => $sprayLog->status,
                'description' => $sprayLog->reason,
            ],
        );

        $notificationActivities = $notificationLogs->map(
            static fn (NotificationLog $notificationLog): array => [
                'time' => $notificationLog->sent_at?->format('Y-m-d H:i:s') ?? '-',
                'sort_at' => $notificationLog->sent_at?->timestamp ?? 0,
                'type' => 'WhatsApp',
                'status' => ucfirst($notificationLog->status),
                'status_key' => $notificationLog->status,
                'description' => $notificationLog->message,
            ],
        );

        /** @var Collection<int, array<string, string|int>> $merged */
        $merged = $activities
            ->merge($sprayActivities)
            ->merge($notificationActivities)
            ->sortByDesc('sort_at')
            ->take(5)
            ->values();

        return $merged
            ->map(static function (array $activity): array {
                unset($activity['sort_at']);

                /** @var array<string, string> $activity */
                return $activity;
            })
            ->all();
    }
}
