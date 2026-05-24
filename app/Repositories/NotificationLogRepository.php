<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Device;
use App\Models\NotificationLog;
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
}
