<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Device;
use Illuminate\Support\Collection;

final class DeviceRepository
{
    /**
     * @return Collection<int, Device>
     */
    public function getAllWithThresholds(): Collection
    {
        return Device::query()
            ->with('thresholdSetting')
            ->orderBy('name')
            ->get();
    }

    public function findDashboardDevice(): ?Device
    {
        return Device::query()
            ->with('thresholdSetting')
            ->latest('id')
            ->first();
    }

    public function findByApiKey(string $apiKey): ?Device
    {
        return Device::query()
            ->with('thresholdSetting')
            ->where('api_key', $apiKey)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Device $device, array $data): Device
    {
        $device->fill($data);
        $device->save();

        return $device->refresh();
    }
}
