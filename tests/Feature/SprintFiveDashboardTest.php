<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Device;
use App\Models\NotificationLog;
use App\Models\SensorReading;
use App\Models\SprayLog;
use App\Models\ThresholdSetting;
use Illuminate\Support\Carbon;
use Tests\Concerns\UsesMysqlTestDatabase;
use Tests\TestCase;

final class SprintFiveDashboardTest extends TestCase
{
    use UsesMysqlTestDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useMysqlTestDatabase();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        $this->rollbackMysqlTestDatabase();
        parent::tearDown();
    }

    public function test_dashboard_shows_empty_state_when_device_has_not_been_registered(): void
    {
        $this->get('/dashboard')
            ->assertOk()
            ->assertSeeText('Belum ada perangkat')
            ->assertSeeText('Belum ada data aktivitas perangkat.');
    }

    public function test_dashboard_renders_latest_sensor_summary_chart_and_recent_activity(): void
    {
        $frozenNow = Carbon::create(2026, 5, 24, 10, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($frozenNow);

        $device = Device::query()->create([
            'name' => 'Sprayer Lahan Brebes',
            'location' => 'Brebes',
            'api_key' => 'dashboard-device-key',
            'mode' => 'automatic',
            'sprayer_status' => 'on',
        ]);

        ThresholdSetting::query()->create([
            'device_id' => $device->id,
            'min_soil_moisture' => 40,
            'max_temperature' => 32,
            'min_air_humidity' => 60,
        ]);

        SensorReading::query()->create([
            'device_id' => $device->id,
            'temperature' => 30.2,
            'air_humidity' => 74,
            'soil_moisture' => 46,
            'rain_status' => 'no_rain',
            'sprayer_status' => 'off',
            'condition_status' => 'normal',
            'recorded_at' => $frozenNow->copy()->subMinutes(30),
        ]);

        SensorReading::query()->create([
            'device_id' => $device->id,
            'temperature' => 31.5,
            'air_humidity' => 70,
            'soil_moisture' => 35,
            'rain_status' => 'no_rain',
            'sprayer_status' => 'off',
            'condition_status' => 'kritis',
            'recorded_at' => $frozenNow->copy()->subMinutes(5),
        ]);

        SprayLog::query()->create([
            'device_id' => $device->id,
            'trigger_type' => 'automatic',
            'status' => 'on',
            'reason' => 'Aktif otomatis karena tanah kering',
            'created_by' => null,
        ]);

        NotificationLog::query()->create([
            'device_id' => $device->id,
            'type' => 'critical_condition',
            'recipient_phone' => '+628111111111',
            'message' => 'Peringatan kondisi kritis dikirim',
            'status' => 'sent',
            'sent_at' => $frozenNow->copy()->subMinutes(4),
        ]);

        $this->get('/dashboard')
            ->assertOk()
            ->assertSeeText('Sprayer Lahan Brebes')
            ->assertSeeText('31.5°C')
            ->assertSeeText('70%')
            ->assertSeeText('35%')
            ->assertSeeText('ON')
            ->assertSeeText('Aktif otomatis karena tanah kering')
            ->assertSeeText('Peringatan kondisi kritis dikirim')
            ->assertSee('["09:30","09:55"]', false)
            ->assertSee('[30.2,31.5]', false)
            ->assertSee('[74,70]', false)
            ->assertSee('[46,35]', false);
    }
}
