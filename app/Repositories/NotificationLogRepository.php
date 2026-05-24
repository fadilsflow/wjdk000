<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\NotificationLog;

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
}
