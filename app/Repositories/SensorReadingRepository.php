<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Device;
use App\Models\SensorReading;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

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

    public function findLatestForDevice(Device $device): ?SensorReading
    {
        return SensorReading::query()
            ->where('device_id', $device->id)
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * @return Collection<int, SensorReading>
     */
    public function getRecentForDevice(Device $device, int $limit = 12): Collection
    {
        return SensorReading::query()
            ->where('device_id', $device->id)
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->sortBy('recorded_at')
            ->values();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateHistoryForDevice(Device $device, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = SensorReading::query()
            ->where('device_id', $device->id)
            ->orderByDesc('recorded_at')
            ->orderByDesc('id');

        if (($filters['from_date'] ?? null) !== null) {
            $query->whereDate('recorded_at', '>=', $filters['from_date']);
        }

        if (($filters['to_date'] ?? null) !== null) {
            $query->whereDate('recorded_at', '<=', $filters['to_date']);
        }

        return $query->paginate($perPage)->withQueryString();
    }
}
