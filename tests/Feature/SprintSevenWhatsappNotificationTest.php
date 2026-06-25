<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Device;
use App\Models\ThresholdSetting;
use App\Models\WhatsappSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\UsesMysqlTestDatabase;
use Tests\TestCase;

final class SprintSevenWhatsappNotificationTest extends TestCase
{
    use UsesMysqlTestDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useMysqlTestDatabase();
        Config::set('services.whatsapp.gateway_url', 'https://gateway.test/send');
        Config::set('services.whatsapp.gateway_token', 'secret-token');
        Config::set('services.whatsapp.sender_number', '+628000000000');
        Http::fake([
            'https://gateway.test/*' => Http::response(['success' => true], 200),
        ]);
    }

    protected function tearDown(): void
    {
        $this->rollbackMysqlTestDatabase();
        parent::tearDown();
    }

    public function test_admin_can_update_whatsapp_recipient_and_templates(): void
    {
        $this->put('/admin/whatsapp', [
                'recipient_phone' => '+628123456700',
                'critical_condition_template' => 'Kritis {{device_name}} {{condition_status}}',
                'spray_start_template' => 'Mulai {{device_name}} {{sprayer_status}}',
                'spray_stop_template' => 'Stop {{device_name}} {{sprayer_status}}',
                'rain_detected_template' => 'Hujan {{device_name}} {{rain_status}}',
            ])
            ->assertRedirect('/admin/whatsapp')
            ->assertSessionHas('status', 'whatsapp-settings-updated');

        $this->assertDatabaseHas('whatsapp_settings', [
            'recipient_phone' => '+628123456700',
            'critical_condition_template' => 'Kritis {{device_name}} {{condition_status}}',
        ]);
    }

    public function test_sensor_endpoint_sends_critical_and_spray_start_notifications_with_template(): void
    {
        $device = $this->makeDeviceWithThreshold(mode: 'automatic', sprayerStatus: 'off');
        $this->makeWhatsappSetting();

        $this->postJson('/api/sensor-readings', [
            'api_key' => $device->getAttributes()['api_key'],
            'temperature' => 31.5,
            'air_humidity' => 70,
            'soil_moisture' => 35,
            'rain_status' => 'no_rain',
            'sprayer_status' => 'off',
            'recorded_at' => now()->toDateTimeString(),
        ])->assertOk()
            ->assertJsonPath('condition_status', 'kritis')
            ->assertJsonPath('sprayer_command', 'on');

        Http::assertSentCount(2);

        $this->assertDatabaseHas('notification_logs', [
            'device_id' => $device->id,
            'type' => 'critical_condition',
            'recipient_phone' => '+628123456700',
            'status' => 'sent',
        ]);

        $this->assertDatabaseHas('notification_logs', [
            'device_id' => $device->id,
            'type' => 'spray_start',
            'recipient_phone' => '+628123456700',
            'status' => 'sent',
        ]);
    }

    public function test_sensor_endpoint_sends_rain_detected_notification(): void
    {
        $device = $this->makeDeviceWithThreshold(mode: 'automatic', sprayerStatus: 'on');
        $this->makeWhatsappSetting();

        $this->postJson('/api/sensor-readings', [
            'api_key' => $device->getAttributes()['api_key'],
            'temperature' => 28.0,
            'air_humidity' => 80,
            'soil_moisture' => 20,
            'rain_status' => 'rain',
            'sprayer_status' => 'on',
            'recorded_at' => now()->toDateTimeString(),
        ])->assertOk()
            ->assertJsonPath('sprayer_command', 'off');

        $this->assertDatabaseHas('notification_logs', [
            'device_id' => $device->id,
            'type' => 'rain_detected',
            'recipient_phone' => '+628123456700',
            'status' => 'sent',
        ]);
    }

    public function test_manual_sprayer_control_sends_start_and_stop_notifications(): void
    {
        $device = $this->makeDeviceWithThreshold(mode: 'manual', sprayerStatus: 'off');
        $this->makeWhatsappSetting();

        $this->post('/sprayer/status', [
                'status' => 'on',
            ])
            ->assertRedirect('/sprayer');

        $this->post('/sprayer/status', [
                'status' => 'off',
            ])
            ->assertRedirect('/sprayer');

        $this->assertDatabaseHas('notification_logs', [
            'device_id' => $device->id,
            'type' => 'spray_start',
            'recipient_phone' => '+628123456700',
            'status' => 'sent',
        ]);

        $this->assertDatabaseHas('notification_logs', [
            'device_id' => $device->id,
            'type' => 'spray_stop',
            'recipient_phone' => '+628123456700',
            'status' => 'sent',
        ]);
    }

    private function makeWhatsappSetting(): void
    {
        WhatsappSetting::query()->create([
            'recipient_phone' => '+628123456700',
            'critical_condition_template' => 'Kritis {{device_name}} {{condition_status}} {{soil_moisture}}',
            'spray_start_template' => 'Mulai {{device_name}} {{sprayer_status}} {{reason}}',
            'spray_stop_template' => 'Stop {{device_name}} {{sprayer_status}} {{reason}}',
            'rain_detected_template' => 'Hujan {{device_name}} {{rain_status}} {{sprayer_status}}',
        ]);
    }

    private function makeDeviceWithThreshold(string $mode, string $sprayerStatus): Device
    {
        $device = Device::query()->create([
            'name' => 'Sprayer WhatsApp',
            'location' => 'Brebes',
            'api_key' => uniqid('device-', true),
            'mode' => $mode,
            'sprayer_status' => $sprayerStatus,
        ]);

        ThresholdSetting::query()->create([
            'device_id' => $device->id,
            'min_soil_moisture' => 40,
            'max_temperature' => 32,
            'min_air_humidity' => 60,
        ]);

        return $device->refresh();
    }
}
