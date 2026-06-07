<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Device;
use App\Models\SensorReading;
use App\Repositories\DeviceRepository;
use App\Repositories\SensorReadingRepository;
use App\Repositories\SprayLogRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SprayerControlService
{
    public function __construct(
        private readonly DeviceRepository $deviceRepository,
        private readonly SensorReadingRepository $sensorReadingRepository,
        private readonly SprayLogRepository $sprayLogRepository,
        private readonly WhatsAppNotificationService $whatsAppNotificationService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getControlPageData(): array
    {
        $device = $this->deviceRepository->findDashboardDevice();

        if (! $device instanceof Device) {
            return $this->emptyControlPageData();
        }

        $latestReading = $this->sensorReadingRepository->findLatestForDevice($device);

        return [
            'device' => [
                'name' => $device->name,
                'mode' => $device->mode,
                'sprayer_status' => $device->sprayer_status,
            ],
            'sensor' => $this->buildSensorSummary($device, $latestReading),
            'thresholds' => [
                'min_soil_moisture' => $device->thresholdSetting?->min_soil_moisture,
            ],
            'logs' => $this->buildRecentLogs($device),
        ];
    }

    public function updateMode(string $mode, int $userId): void
    {
        $device = $this->requireDevice();

        if ($device->mode === $mode) {
            return;
        }

        DB::transaction(function () use ($device, $mode, $userId): void {
            $this->deviceRepository->update($device, [
                'mode' => $mode,
            ]);

            $this->sprayLogRepository->create([
                'device_id' => $device->id,
                'trigger_type' => 'manual',
                'status' => $device->sprayer_status,
                'reason' => sprintf('Mode diubah ke %s', $mode),
                'created_by' => $userId,
            ]);
        });
    }

    public function updateStatus(string $status, int $userId): void
    {
        $device = $this->requireDevice();

        if ($device->mode !== 'manual') {
            throw ValidationException::withMessages([
                'status' => 'Sprayer hanya dapat dikontrol langsung saat mode manual.',
            ]);
        }

        if ($device->sprayer_status === $status) {
            return;
        }

        DB::transaction(function () use ($device, $status, $userId): void {
            $this->deviceRepository->update($device, [
                'sprayer_status' => $status,
            ]);

            $this->sprayLogRepository->create([
                'device_id' => $device->id,
                'trigger_type' => 'manual',
                'status' => $status,
                'reason' => $status === 'on'
                    ? 'Sprayer dinyalakan manual dari website'
                    : 'Sprayer dimatikan manual dari website',
                'created_by' => $userId,
            ]);

            $this->whatsAppNotificationService->send(
                $device,
                $status === 'on' ? 'spray_start' : 'spray_stop',
                $this->buildNotificationContext($device, $status),
            );
        });
    }

    private function requireDevice(): Device
    {
        $device = $this->deviceRepository->findDashboardDevice();

        if (! $device instanceof Device) {
            throw ValidationException::withMessages([
                'device' => 'Perangkat sprayer belum terdaftar.',
            ]);
        }

        return $device;
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyControlPageData(): array
    {
        return [
            'device' => [
                'name' => 'Belum ada perangkat',
                'mode' => 'automatic',
                'sprayer_status' => 'off',
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
            ],
            'logs' => [],
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
     * @return array<int, array<string, string>>
     */
    private function buildRecentLogs(Device $device): array
    {
        return $this->sprayLogRepository
            ->getRecentForDevice($device, 10)
            ->map(static function ($log): array {
                return [
                    'time' => $log->created_at?->format('Y-m-d H:i:s') ?? '-',
                    'trigger' => $log->trigger_type,
                    'status' => strtoupper($log->status),
                    'reason' => $log->reason,
                    'by' => $log->creator?->name ?? 'Sistem',
                ];
            })
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function buildNotificationContext(Device $device, string $status): array
    {
        $latestReading = $this->sensorReadingRepository->findLatestForDevice($device);

        return [
            'device_name' => $device->name,
            'mode' => $device->mode,
            'temperature' => (string) ($latestReading?->temperature ?? '-'),
            'air_humidity' => (string) ($latestReading?->air_humidity ?? '-'),
            'soil_moisture' => (string) ($latestReading?->soil_moisture ?? '-'),
            'rain_status' => $latestReading?->rain_status ?? 'no_rain',
            'sprayer_status' => $status,
            'condition_status' => $latestReading?->condition_status ?? 'normal',
            'recorded_at' => $latestReading?->recorded_at?->format('Y-m-d H:i:s') ?? '-',
            'reason' => $status === 'on'
                ? 'Sprayer dinyalakan manual dari website'
                : 'Sprayer dimatikan manual dari website',
        ];
    }
}
