<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\WhatsappSettingRepository;
use Illuminate\Support\Facades\DB;

final class WhatsappSettingsService
{
    public function __construct(
        private readonly WhatsappSettingRepository $whatsappSettingRepository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getSettingsPageData(): array
    {
        $setting = $this->whatsappSettingRepository->getSingleton();
        $templates = $this->defaultTemplates();

        return [
            'settings' => [
                'gateway_url' => (string) config('services.whatsapp.gateway_url', ''),
                'sender_number' => (string) config('services.whatsapp.sender_number', ''),
                'gateway_token_masked' => $this->maskToken(config('services.whatsapp.gateway_token')),
                'connection_status' => $this->resolveConnectionStatus(),
                'recipient_phone' => $setting?->recipient_phone ?? '',
                'critical_condition_template' => $setting?->critical_condition_template ?? $templates['critical_condition_template'],
                'spray_start_template' => $setting?->spray_start_template ?? $templates['spray_start_template'],
                'spray_stop_template' => $setting?->spray_stop_template ?? $templates['spray_stop_template'],
                'rain_detected_template' => $setting?->rain_detected_template ?? $templates['rain_detected_template'],
            ],
            'available_variables' => [
                '{{device_name}}',
                '{{mode}}',
                '{{temperature}}',
                '{{air_humidity}}',
                '{{soil_moisture}}',
                '{{rain_status}}',
                '{{sprayer_status}}',
                '{{condition_status}}',
                '{{recorded_at}}',
                '{{reason}}',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateSettings(array $data): void
    {
        DB::transaction(function () use ($data): void {
            $this->whatsappSettingRepository->updateOrCreateSingleton([
                'recipient_phone' => $data['recipient_phone'],
                'critical_condition_template' => $data['critical_condition_template'],
                'spray_start_template' => $data['spray_start_template'],
                'spray_stop_template' => $data['spray_stop_template'],
                'rain_detected_template' => $data['rain_detected_template'],
            ]);
        });
    }

    /**
     * @return array<string, string>
     */
    public function defaultTemplates(): array
    {
        return [
            'critical_condition_template' => 'Peringatan {{device_name}}: kondisi {{condition_status}}. Suhu {{temperature}}C, kelembapan udara {{air_humidity}}%, kelembapan tanah {{soil_moisture}}%, hujan {{rain_status}}, mode {{mode}}.',
            'spray_start_template' => 'Sprayer {{device_name}} mulai aktif. Status {{sprayer_status}}, mode {{mode}}, alasan: {{reason}}.',
            'spray_stop_template' => 'Sprayer {{device_name}} berhenti. Status {{sprayer_status}}, mode {{mode}}, alasan: {{reason}}.',
            'rain_detected_template' => 'Hujan terdeteksi di {{device_name}}. Sprayer tetap {{sprayer_status}}. Data terakhir: suhu {{temperature}}C, tanah {{soil_moisture}}%, mode {{mode}}.',
        ];
    }

    private function resolveConnectionStatus(): string
    {
        $gatewayUrl = config('services.whatsapp.gateway_url');
        $gatewayToken = config('services.whatsapp.gateway_token');

        return is_string($gatewayUrl) && $gatewayUrl !== '' && is_string($gatewayToken) && $gatewayToken !== ''
            ? 'connected'
            : 'unconfigured';
    }

    private function maskToken(mixed $token): string
    {
        if (! is_string($token) || $token === '') {
            return '';
        }

        if (strlen($token) <= 8) {
            return str_repeat('*', strlen($token));
        }

        return substr($token, 0, 4).str_repeat('*', strlen($token) - 8).substr($token, -4);
    }
}
