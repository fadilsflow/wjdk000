<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\WhatsappSetting;

final class WhatsappSettingRepository
{
    public function getSingleton(): ?WhatsappSetting
    {
        return WhatsappSetting::query()->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateOrCreateSingleton(array $data): WhatsappSetting
    {
        /** @var WhatsappSetting $setting */
        $setting = WhatsappSetting::query()->updateOrCreate(
            ['id' => optional($this->getSingleton())->id ?? 1],
            $data,
        );

        return $setting->refresh();
    }
}
