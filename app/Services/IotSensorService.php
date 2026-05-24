<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Device;
use App\Models\ThresholdSetting;
use App\Repositories\DeviceRepository;
use App\Repositories\SensorReadingRepository;
use App\Repositories\SprayLogRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class IotSensorService
{
    public function __construct(
        private readonly DeviceRepository $deviceRepository,
        private readonly SensorReadingRepository $sensorReadingRepository,
        private readonly SprayLogRepository $sprayLogRepository,
        private readonly WhatsAppNotificationService $whatsAppNotificationService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function storeReading(Device $device, array $data): array
    {
        $threshold = $device->thresholdSetting;

        if (! $threshold instanceof ThresholdSetting) {
            throw ValidationException::withMessages([
                'device' => ['Threshold perangkat belum dikonfigurasi.'],
            ]);
        }

        return DB::transaction(function () use ($device, $threshold, $data): array {
            $evaluation = $this->evaluate($device, $threshold, $data);

            $this->sensorReadingRepository->create([
                'device_id' => $device->id,
                'temperature' => $data['temperature'],
                'air_humidity' => $data['air_humidity'],
                'soil_moisture' => $data['soil_moisture'],
                'rain_status' => $data['rain_status'],
                'sprayer_status' => $data['sprayer_status'],
                'condition_status' => $evaluation['condition_status'],
                'recorded_at' => $data['recorded_at'],
            ]);

            if ($device->sprayer_status !== $evaluation['sprayer_command']) {
                $this->deviceRepository->update($device, [
                    'sprayer_status' => $evaluation['sprayer_command'],
                ]);

                $this->sprayLogRepository->create([
                    'device_id' => $device->id,
                    'trigger_type' => 'automatic',
                    'status' => $evaluation['sprayer_command'],
                    'reason' => $evaluation['reason'],
                    'created_by' => null,
                ]);
            }

            $this->dispatchNotifications($device, $evaluation, $data);

            return [
                'success' => true,
                'condition_status' => $evaluation['condition_status'],
                'mode' => $device->mode,
                'sprayer_command' => $evaluation['sprayer_command'],
            ];
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    private function evaluate(Device $device, ThresholdSetting $threshold, array $data): array
    {
        if ($data['rain_status'] === 'rain') {
            return [
                'condition_status' => 'normal',
                'sprayer_command' => 'off',
                'reason' => 'Hujan terdeteksi',
                'notification_type' => 'rain_detected',
            ];
        }

        if ((float) $data['soil_moisture'] < $threshold->min_soil_moisture) {
            return [
                'condition_status' => 'kritis',
                'sprayer_command' => $device->mode === 'automatic' ? 'on' : $device->sprayer_status,
                'reason' => 'Tanah kering, melewati threshold minimum',
                'notification_type' => 'critical_condition',
            ];
        }

        if ((float) $data['temperature'] > $threshold->max_temperature || (float) $data['air_humidity'] < $threshold->min_air_humidity) {
            return [
                'condition_status' => 'waspada',
                'sprayer_command' => $device->mode === 'automatic' ? 'off' : $device->sprayer_status,
                'reason' => 'Kondisi lingkungan waspada',
                'notification_type' => '',
            ];
        }

        return [
            'condition_status' => 'normal',
            'sprayer_command' => $device->mode === 'automatic' ? 'off' : $device->sprayer_status,
            'reason' => 'Kondisi lingkungan aman',
            'notification_type' => '',
        ];
    }

    /**
     * @param  array<string, string|int|float>  $evaluation
     * @param  array<string, mixed>  $data
     */
    private function dispatchNotifications(Device $device, array $evaluation, array $data): void
    {
        $message = sprintf(
            '%s | suhu %sC | udara %s%% | tanah %s%% | hujan %s | sprayer %s',
            $device->name,
            $data['temperature'],
            $data['air_humidity'],
            $data['soil_moisture'],
            $data['rain_status'],
            $evaluation['sprayer_command'],
        );

        if ($evaluation['notification_type'] !== '') {
            $this->whatsAppNotificationService->send($device, $evaluation['notification_type'], $message);
        }

        if ($device->sprayer_status !== $evaluation['sprayer_command']) {
            $this->whatsAppNotificationService->send(
                $device,
                $evaluation['sprayer_command'] === 'on' ? 'spray_start' : 'spray_stop',
                $message,
            );
        }
    }
}
