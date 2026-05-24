<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\SprayLog;

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
}
