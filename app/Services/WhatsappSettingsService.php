<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\WhatsappSettingRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

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
        $health = $this->getGatewayHealthData();

        return [
            'settings' => [
                'gateway_url'                   => (string) config('services.whatsapp.gateway_url', ''),
                'sender_number'                 => $health['sender_number'] ?? (string) config('services.whatsapp.sender_number', ''),
                'gateway_token_masked'          => $this->maskToken(config('services.whatsapp.gateway_token')),
                'connection_status'             => $health['connection_status'],
                'qr_code_string'                => $health['qr'],
                'recipient_phone'               => $setting?->recipient_phone ?? '',
                'critical_condition_template'   => $setting?->critical_condition_template ?? $templates['critical_condition_template'],
                'spray_start_template'          => $setting?->spray_start_template ?? $templates['spray_start_template'],
                'spray_stop_template'           => $setting?->spray_stop_template ?? $templates['spray_stop_template'],
                'rain_detected_template'        => $setting?->rain_detected_template ?? $templates['rain_detected_template'],
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
                'recipient_phone'               => $data['recipient_phone'],
                'critical_condition_template'   => $data['critical_condition_template'],
                'spray_start_template'          => $data['spray_start_template'],
                'spray_stop_template'           => $data['spray_stop_template'],
                'rain_detected_template'        => $data['rain_detected_template'],
            ]);
        });
    }

    /**
     * @return array{success: bool, message: string}
     */
    public function sendTestMessage(): array
    {
        $health = $this->getGatewayHealthData();

        if ($health['connection_status'] !== 'connected') {
            return [
                'success' => false,
                'message' => 'WhatsApp belum terhubung. Selesaikan koneksi terlebih dahulu.',
            ];
        }

        $recipient = $this->whatsappSettingRepository->getSingleton()?->recipient_phone;

        if (! is_string($recipient) || trim($recipient) === '') {
            return [
                'success' => false,
                'message' => 'Nomor penerima belum dikonfigurasi. Isi nomor WhatsApp target terlebih dahulu.',
            ];
        }

        $gatewayUrl = config('services.whatsapp.gateway_url');
        $token = config('services.whatsapp.gateway_token');

        if (! is_string($gatewayUrl) || $gatewayUrl === '' || ! is_string($token) || $token === '') {
            return [
                'success' => false,
                'message' => 'Gateway WhatsApp belum dikonfigurasi di berkas .env.',
            ];
        }

        try {
            Http::withToken($token)
                ->post($gatewayUrl, [
                    'to' => $recipient,
                    'message' => 'Tes koneksi Smart Sprayer IoT — pesan ini dikirim dari halaman pengaturan WhatsApp.',
                ])
                ->throw();
        } catch (Throwable) {
            return [
                'success' => false,
                'message' => 'Gagal mengirim pesan uji. Periksa gateway dan nomor penerima.',
            ];
        }

        return [
            'success' => true,
            'message' => "Pesan uji berhasil dikirim ke {$recipient}.",
        ];
    }

    /**
     * @return array<string, string>
     */
    public function defaultTemplates(): array
    {
        return [
            'critical_condition_template' => 'Peringatan {{device_name}}: kondisi {{condition_status}}. Suhu {{temperature}}C, kelembapan udara {{air_humidity}}%, kelembapan tanah {{soil_moisture}}%, hujan {{rain_status}}, mode {{mode}}.',
            'spray_start_template'        => 'Sprayer {{device_name}} mulai aktif. Status {{sprayer_status}}, mode {{mode}}, alasan: {{reason}}.',
            'spray_stop_template'         => 'Sprayer {{device_name}} berhenti. Status {{sprayer_status}}, mode {{mode}}, alasan: {{reason}}.',
            'rain_detected_template'      => 'Hujan terdeteksi di {{device_name}}. Sprayer tetap {{sprayer_status}}. Data terakhir: suhu {{temperature}}C, tanah {{soil_moisture}}%, mode {{mode}}.',
        ];
    }

    /**
     * Single HTTP call ke /health gateway; kembalikan status, sender_number, dan qr string.
     *
     * @return array{connection_status: string, sender_number: string|null, qr: string|null}
     */
    private function getGatewayHealthData(): array
    {
        $gatewayUrl  = config('services.whatsapp.gateway_url');
        $gatewayToken = config('services.whatsapp.gateway_token');

        if (! is_string($gatewayUrl) || $gatewayUrl === '' || ! is_string($gatewayToken) || $gatewayToken === '') {
            return ['connection_status' => 'unconfigured', 'sender_number' => null, 'qr' => null];
        }

        try {
            $healthUrl = str_replace('/send', '/health', $gatewayUrl);
            $response  = Http::timeout(2)->get($healthUrl);

            if ($response->successful()) {
                $data  = $response->json();
                $ready = isset($data['whatsapp_ready']) && $data['whatsapp_ready'] === true;

                return [
                    'connection_status' => $ready ? 'connected' : 'qr_pending',
                    'sender_number'     => isset($data['sender_number']) && is_string($data['sender_number'])
                        ? $data['sender_number']
                        : null,
                    'qr' => isset($data['qr']) && is_string($data['qr']) ? $data['qr'] : null,
                ];
            }
        } catch (\Throwable) {
            return ['connection_status' => 'offline', 'sender_number' => null, 'qr' => null];
        }

        return ['connection_status' => 'offline', 'sender_number' => null, 'qr' => null];
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
