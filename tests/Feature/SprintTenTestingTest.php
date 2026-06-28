<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Device;
use App\Models\NotificationLog;
use App\Models\SensorReading;
use App\Models\SprayLog;
use App\Models\ThresholdSetting;
use App\Models\User;
use App\Models\WhatsappSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\UsesMysqlTestDatabase;
use Tests\TestCase;

final class SprintTenTestingTest extends TestCase
{
    use UsesMysqlTestDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useMysqlTestDatabase();

        // Fake WhatsApp Gateway details
        Config::set('services.whatsapp.gateway_url', 'https://gateway.test/send');
        Config::set('services.whatsapp.gateway_token', 'secret-token');
        Config::set('services.whatsapp.sender_number', '+628000000000');

        Http::fake([
            'https://gateway.test/*' => Http::response(['success' => true], 200),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        $this->rollbackMysqlTestDatabase();
        parent::tearDown();
    }

    /**
     * Test 1: Semua halaman dapat diakses.
     */
    public function test_all_pages_are_accessible(): void
    {
        $this->get('/dashboard')->assertOk();
        $this->get('/admin/users')->assertOk();
        $this->get('/sprayer')->assertOk();
        $this->get('/history/sensor')->assertOk();
        $this->get('/history/spray')->assertOk();
    }

    /**
     * Test 2: Test input API sensor.
     */
    public function test_sensor_api_input_validation_and_rejection(): void
    {
        // Rejects unregistered device
        $this->postJson('/api/sensor-readings', [
            'api_key' => 'invalid-key-token',
            'temperature' => 30.0,
            'air_humidity' => 60,
            'soil_moisture' => 50,
            'rain_status' => 'no_rain',
            'sprayer_status' => 'off',
            'recorded_at' => now()->toDateTimeString(),
        ])->assertStatus(401)
          ->assertJsonPath('success', false);

        // Saves valid reading
        $device = $this->makeDevice(mode: 'manual', sprayerStatus: 'off');
        $this->postJson('/api/sensor-readings', [
            'api_key' => $device->getAttributes()['api_key'],
            'temperature' => 30.0,
            'air_humidity' => 60,
            'soil_moisture' => 50,
            'rain_status' => 'no_rain',
            'sprayer_status' => 'off',
            'recorded_at' => now()->toDateTimeString(),
        ])->assertOk()
          ->assertJsonPath('success', true);

        $this->assertDatabaseHas('sensor_readings', [
            'device_id' => $device->id,
            'temperature' => 30.0,
        ]);
    }

    /**
     * Test 3: Test tampilan dashboard.
     */
    public function test_dashboard_renders_data_and_charts(): void
    {
        $frozenNow = Carbon::create(2026, 5, 24, 10, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($frozenNow);

        $device = $this->makeDevice(mode: 'manual', sprayerStatus: 'off');

        SensorReading::query()->create([
            'device_id' => $device->id,
            'temperature' => 31.5,
            'air_humidity' => 70,
            'soil_moisture' => 35,
            'rain_status' => 'no_rain',
            'sprayer_status' => 'off',
            'condition_status' => 'kritis',
            'recorded_at' => $frozenNow,
        ]);

        $this->get('/dashboard')
            ->assertOk()
            ->assertSeeText($device->name)
            ->assertSeeText('31.5°C')
            ->assertSeeText('70%')
            ->assertSeeText('35%')
            ->assertSeeText('off');
    }

    /**
     * Test 4: Test aturan penyemprotan otomatis.
     */
    public function test_automatic_spraying_business_rules(): void
    {
        $this->makeWhatsappSetting();
        $device = $this->makeDevice(mode: 'automatic', sprayerStatus: 'off');

        // Rule: Sprayer active HANYA jika soil_moisture < min_soil_moisture DAN rain_status = 'no_rain'
        $this->postJson('/api/sensor-readings', [
            'api_key' => $device->getAttributes()['api_key'],
            'temperature' => 31.5,
            'air_humidity' => 70,
            'soil_moisture' => 35, // lower than threshold (40)
            'rain_status' => 'no_rain',
            'sprayer_status' => 'off',
            'recorded_at' => now()->toDateTimeString(),
        ])->assertOk()
          ->assertJsonPath('condition_status', 'kritis')
          ->assertJsonPath('sprayer_command', 'on');

        $this->assertDatabaseHas('spray_logs', [
            'device_id' => $device->id,
            'trigger_type' => 'automatic',
            'status' => 'on',
        ]);

        // Rule: Sprayer tidak aktif saat hujan
        $device->update(['sprayer_status' => 'on']);
        $this->postJson('/api/sensor-readings', [
            'api_key' => $device->getAttributes()['api_key'],
            'temperature' => 28.0,
            'air_humidity' => 80,
            'soil_moisture' => 30, // lower than threshold (40) but it is raining!
            'rain_status' => 'rain',
            'sprayer_status' => 'on',
            'recorded_at' => now()->toDateTimeString(),
        ])->assertOk()
          ->assertJsonPath('sprayer_command', 'off');

        $this->assertDatabaseHas('spray_logs', [
            'device_id' => $device->id,
            'trigger_type' => 'automatic',
            'status' => 'off',
        ]);
    }

    /**
     * Test 5: Test kontrol manual.
     */
    public function test_manual_control_actions_and_restrictions(): void
    {
        $device = $this->makeDevice(mode: 'manual', sprayerStatus: 'off');

        // Turn ON manual
        $this->post('/sprayer/status', [
                'status' => 'on',
            ])
            ->assertRedirect('/sprayer')
            ->assertSessionHas('status', 'sprayer-status-updated');

        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'sprayer_status' => 'on',
        ]);

        // Rejects status change when mode is automatic
        $device->update(['mode' => 'automatic']);
        $this->from('/sprayer')
            ->post('/sprayer/status', [
                'status' => 'off',
            ])
            ->assertRedirect('/sprayer')
            ->assertSessionHasErrors('status');
    }

    /**
     * Test 6: Test notifikasi WhatsApp.
     */
    public function test_whatsapp_notification_sending_and_logging(): void
    {
        $device = $this->makeDevice(mode: 'automatic', sprayerStatus: 'off');
        $this->makeWhatsappSetting();

        $this->postJson('/api/sensor-readings', [
            'api_key' => $device->getAttributes()['api_key'],
            'temperature' => 31.5,
            'air_humidity' => 70,
            'soil_moisture' => 35,
            'rain_status' => 'no_rain',
            'sprayer_status' => 'off',
            'recorded_at' => now()->toDateTimeString(),
        ])->assertOk();

        // 2 calls: one for critical condition warning, one for sprayer start
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

    /**
     * Test 7: Test riwayat data.
     */
    public function test_history_pages_loading(): void
    {
        $user = User::factory()->create(['name' => 'Petani Log']);
        $device = $this->makeDevice(mode: 'manual', sprayerStatus: 'off');

        // Seed data for histories
        SensorReading::query()->create([
            'device_id' => $device->id,
            'temperature' => 31.5,
            'air_humidity' => 70,
            'soil_moisture' => 35,
            'rain_status' => 'no_rain',
            'sprayer_status' => 'off',
            'condition_status' => 'kritis',
            'recorded_at' => now(),
        ]);

        SprayLog::query()->create([
            'device_id' => $device->id,
            'trigger_type' => 'manual',
            'status' => 'on',
            'reason' => 'Tes manual',
            'created_by' => $user->id,
        ]);

        NotificationLog::query()->create([
            'device_id' => $device->id,
            'type' => 'critical_condition',
            'recipient_phone' => '+628123456700',
            'message' => 'Notifikasi Kritis',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->get('/history/sensor')
            ->assertOk()
            ->assertSeeText('31.5°C')
            ->assertSeeText('70%')
            ->assertSeeText('35%');

        $this->get('/history/spray')
            ->assertOk()
            ->assertSeeText('Manual')
            ->assertSeeText('ON')
            ->assertSeeText('Tes manual')
            ->assertSeeText('Petani Log');
    }

    /**
     * Test 8: Test halaman publik.
     */
    public function test_public_pages_do_not_leak_private_data(): void
    {
        $device = $this->makeDevice(mode: 'automatic', sprayerStatus: 'off');
        $user = User::factory()->create(['name' => 'Petani Rahasia']);

        SensorReading::query()->create([
            'device_id' => $device->id,
            'temperature' => 31.5,
            'air_humidity' => 70,
            'soil_moisture' => 35,
            'rain_status' => 'no_rain',
            'sprayer_status' => 'off',
            'condition_status' => 'kritis',
            'recorded_at' => now(),
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSeeText('Smart Sprayer')
            ->assertSeeText('31.5')
            ->assertSeeText('70')
            ->assertSeeText('35')
            ->assertSeeText('kritis')
            ->assertSeeText('Data Publik')
            ->assertDontSeeText('Kontrol Sprayer')
            ->assertDontSeeText('Nyalakan')
            ->assertDontSeeText('Matikan')
            ->assertDontSeeText($user->name)
            ->assertDontSeeText('Pengaturan WhatsApp');
    }

    private function makeDevice(string $mode, string $sprayerStatus): Device
    {
        $device = Device::query()->create([
            'name' => 'Sprayer Lahan Test',
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
}
