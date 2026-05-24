<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Device;
use App\Repositories\NotificationLogRepository;
use App\Repositories\WhatsappSettingRepository;
use Illuminate\Support\Facades\Http;
use Throwable;

final class WhatsAppNotificationService
{
    public function __construct(
        private readonly NotificationLogRepository $notificationLogRepository,
        private readonly WhatsappSettingRepository $whatsappSettingRepository,
        private readonly WhatsappSettingsService $whatsappSettingsService,
    ) {}

    /**
     * @param  array<string, mixed>  $context
     */
    public function send(Device $device, string $type, array $context = []): void
    {
        $recipient = $this->resolveRecipientPhone();
        $message = $this->buildMessage($type, $context);

        if ($recipient === null) {
            $this->notificationLogRepository->create([
                'device_id' => $device->id,
                'type' => $type,
                'recipient_phone' => 'unconfigured',
                'message' => $message,
                'status' => 'failed',
                'sent_at' => now(),
            ]);

            return;
        }

        $status = 'sent';

        try {
            $gatewayUrl = config('services.whatsapp.gateway_url');
            $token = config('services.whatsapp.gateway_token');
            $sender = config('services.whatsapp.sender_number');

            if (! is_string($gatewayUrl) || $gatewayUrl === '' || ! is_string($token) || $token === '') {
                throw new \RuntimeException('WhatsApp gateway belum dikonfigurasi.');
            }

            Http::withToken($token)
                ->post($gatewayUrl, array_filter([
                    'sender' => $sender,
                    'to' => $recipient,
                    'message' => $message,
                    'context' => $context,
                ], static fn (mixed $value): bool => $value !== null && $value !== ''))
                ->throw();
        } catch (Throwable) {
            $status = 'failed';
        }

        $this->notificationLogRepository->create([
            'device_id' => $device->id,
            'type' => $type,
            'recipient_phone' => $recipient,
            'message' => $message,
            'status' => $status,
            'sent_at' => now(),
        ]);
    }

    private function resolveRecipientPhone(): ?string
    {
        return $this->whatsappSettingRepository->getSingleton()?->recipient_phone;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function buildMessage(string $type, array $context): string
    {
        $setting = $this->whatsappSettingRepository->getSingleton();
        $templates = $this->whatsappSettingsService->defaultTemplates();

        $template = match ($type) {
            'critical_condition' => $setting?->critical_condition_template ?? $templates['critical_condition_template'],
            'spray_start' => $setting?->spray_start_template ?? $templates['spray_start_template'],
            'spray_stop' => $setting?->spray_stop_template ?? $templates['spray_stop_template'],
            'rain_detected' => $setting?->rain_detected_template ?? $templates['rain_detected_template'],
            default => '{{device_name}} notification',
        };

        $replacements = [];

        foreach ($context as $key => $value) {
            $replacements['{{'.$key.'}}'] = (string) $value;
        }

        return strtr($template, $replacements);
    }
}
