<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Device;
use App\Models\SprayLog;
use Illuminate\Support\Collection;

final class SprayLogRepository
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): SprayLog
    {
        /** @var SprayLog $sprayLog */
        $sprayLog = SprayLog::query()->create($data);

        return $sprayLog;
    }

    /**
     * @return Collection<int, SprayLog>
     */
    public function getRecentForDevice(Device $device, int $limit = 5): Collection
    {
        return SprayLog::query()
            ->with('creator')
            ->where('device_id', $device->id)
            ->latest()
            ->limit($limit)
            ->get();
    }
}
