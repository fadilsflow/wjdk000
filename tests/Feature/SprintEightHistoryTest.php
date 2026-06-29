<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Device;
use App\Models\SensorReading;
use App\Models\SprayLog;
use App\Models\ThresholdSetting;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\Concerns\UsesMysqlTestDatabase;
use Tests\TestCase;

final class SprintEightHistoryTest extends TestCase
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

    public function test_sensor_history_page_renders_database_rows_and_date_filter(): void
    {
        $device = $this->makeDevice();

        SensorReading::query()->create([
            'device_id' => $device->id,
            'temperature' => 29.5,
            'air_humidity' => 76,
            'soil_moisture' => 55,
            'rain_status' => 'rain',
            'sprayer_status' => 'off',
            'condition_status' => 'normal',
            'recorded_at' => Carbon::parse('2026-05-23 08:30:00'),
        ]);

        SensorReading::query()->create([
            'device_id' => $device->id,
            'temperature' => 31.5,
            'air_humidity' => 70,
            'soil_moisture' => 35,
            'rain_status' => 'no_rain',
            'sprayer_status' => 'on',
            'condition_status' => 'kritis',
            'recorded_at' => Carbon::parse('2026-05-24 10:00:00'),
        ]);

        $this->get('/history/sensor?from_date=2026-05-24&to_date=2026-05-24')
            ->assertOk()
            ->assertSeeText('31.5°C')
            ->assertSeeText('35%')
            ->assertSeeText('Kritis')
            ->assertDontSeeText('29.5°C');
    }

    public function test_spray_history_page_renders_database_logs(): void
    {
        $actor = User::factory()->create([
            'name' => 'Petani Log',
        ]);
        $device = $this->makeDevice();

        SprayLog::query()->create([
            'device_id' => $device->id,
            'trigger_type' => 'manual',
            'status' => 'on',
            'reason' => 'Sprayer dinyalakan manual dari website',
            'created_by' => $actor->id,
        ]);

        $this->get('/history/spray')
            ->assertOk()
            ->assertSeeText('Manual')
            ->assertSeeText('ON')
            ->assertSeeText('Sprayer dinyalakan manual dari website')
            ->assertSeeText('Petani Log');
    }

    private function makeDevice(): Device
    {
        $device = Device::query()->create([
            'name' => 'Sprayer Riwayat',
            'location' => 'Brebes',
            'api_key' => uniqid('history-', true),
            'mode' => 'automatic',
            'sprayer_status' => 'off',
        ]);

        ThresholdSetting::query()->create([
            'device_id' => $device->id,
            'min_soil_moisture' => 40,
            'max_temperature' => 32,
            'min_air_humidity' => 60,
        ]);

        return $device;
    }
}
