<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Device;
use App\Models\NotificationLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class NotificationLogRepository
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): NotificationLog
    {
        /** @var NotificationLog $notificationLog */
        $notificationLog = NotificationLog::query()->create($data);

        return $notificationLog;
    }

    /**
     * @return Collection<int, NotificationLog>
     */
    public function getRecentForDevice(Device $device, int $limit = 5): Collection
    {
        return NotificationLog::query()
            ->where('device_id', $device->id)
            ->latest('sent_at')
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateHistoryForDevice(Device $device, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = NotificationLog::query()
            ->where('device_id', $device->id)
            ->orderByDesc('sent_at')
            ->orderByDesc('id');

        if (($filters['from_date'] ?? null) !== null) {
            $query->whereDate('sent_at', '>=', $filters['from_date']);
        }

        if (($filters['to_date'] ?? null) !== null) {
            $query->whereDate('sent_at', '<=', $filters['to_date']);
        }

        return $query->paginate($perPage)->withQueryString();
    }
}
