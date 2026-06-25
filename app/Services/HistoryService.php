<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Device;
use App\Models\SensorReading;
use App\Models\SprayLog;
use App\Repositories\DeviceRepository;
use App\Repositories\SensorReadingRepository;
use App\Repositories\SprayLogRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class HistoryService
{
    public function __construct(
        private readonly DeviceRepository $deviceRepository,
        private readonly SensorReadingRepository $sensorReadingRepository,
        private readonly SprayLogRepository $sprayLogRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getSensorHistoryData(array $filters): array
    {
        $device = $this->deviceRepository->findDashboardDevice();
        $paginator = $device instanceof Device
            ? $this->sensorReadingRepository->paginateHistoryForDevice($device, $filters)
            : $this->emptyPaginator();

        return [
            'readings' => $paginator->getCollection()
                ->map(static fn (SensorReading $reading): array => [
                    'time' => $reading->recorded_at?->format('Y-m-d H:i:s') ?? '-',
                    'temp' => $reading->temperature,
                    'hum' => $reading->air_humidity,
                    'soil' => $reading->soil_moisture,
                    'soil_raw' => $reading->soil_raw,
                    'rain' => $reading->rain_status,
                    'rain_raw' => $reading->rain_raw,
                    'sprayer' => $reading->sprayer_status,
                    'simulation_mode' => $reading->simulation_mode,
                    'condition' => $reading->condition_status,
                ])
                ->all(),
            'filters' => $this->normalizeFilters($filters),
            'pagination' => $this->buildPaginationData($paginator),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getSprayHistoryData(array $filters): array
    {
        $device = $this->deviceRepository->findDashboardDevice();
        $paginator = $device instanceof Device
            ? $this->sprayLogRepository->paginateHistoryForDevice($device, $filters)
            : $this->emptyPaginator();

        return [
            'logs' => $paginator->getCollection()
                ->map(static fn (SprayLog $log): array => [
                    'time' => $log->created_at?->format('Y-m-d H:i:s') ?? '-',
                    'trigger' => $log->trigger_type,
                    'status' => $log->status,
                    'reason' => $log->reason,
                    'by' => $log->creator?->name ?? 'Sistem',
                ])
                ->all(),
            'filters' => $this->normalizeFilters($filters),
            'pagination' => $this->buildPaginationData($paginator),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, string|null>
     */
    private function normalizeFilters(array $filters): array
    {
        return [
            'from_date' => isset($filters['from_date']) ? (string) $filters['from_date'] : null,
            'to_date' => isset($filters['to_date']) ? (string) $filters['to_date'] : null,
        ];
    }

    /**
     * @return array<string, int|string|null>
     */
    private function buildPaginationData(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'previous_page_url' => $paginator->previousPageUrl(),
            'next_page_url' => $paginator->nextPageUrl(),
        ];
    }

    private function emptyPaginator(): LengthAwarePaginator
    {
        return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1, [
            'path' => request()->url(),
            'pageName' => 'page',
        ]);
    }
}
