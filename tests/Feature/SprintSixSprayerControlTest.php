<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Device;
use App\Models\SensorReading;
use App\Models\ThresholdSetting;
use Tests\Concerns\UsesMysqlTestDatabase;
use Tests\TestCase;

final class SprintSixSprayerControlTest extends TestCase
{
    use UsesMysqlTestDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useMysqlTestDatabase();
    }

    protected function tearDown(): void
    {
        $this->rollbackMysqlTestDatabase();
        parent::tearDown();
    }

    public function test_sprayer_control_page_renders_real_device_sensor_and_logs(): void
    {
        $device = $this->makeDevice(mode: 'manual', sprayerStatus: 'off');

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

        $this->post('/sprayer/status', [
            'status' => 'on',
        ]);

        $this->get('/sprayer')
            ->assertOk()
            ->assertSeeText('Sprayer Lahan Kontrol')
            ->assertSeeText('kritis')
            ->assertSeeText('Sprayer dinyalakan manual dari website');
    }

    public function test_device_mode_can_be_switched(): void
    {
        $device = $this->makeDevice(mode: 'automatic', sprayerStatus: 'off');

        $this->post('/sprayer/mode', [
            'mode' => 'manual',
        ])
            ->assertRedirect('/sprayer')
            ->assertSessionHas('status', 'sprayer-mode-updated');

        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'mode' => 'manual',
        ]);

        $this->assertDatabaseHas('spray_logs', [
            'device_id' => $device->id,
            'trigger_type' => 'manual',
            'status' => 'off',
            'reason' => 'Mode diubah ke manual',
            'created_by' => null,
        ]);
    }

    public function test_sprayer_can_be_turned_on_in_manual_mode_and_log_it(): void
    {
        $device = $this->makeDevice(mode: 'manual', sprayerStatus: 'off');

        $this->post('/sprayer/status', [
            'status' => 'on',
        ])
            ->assertRedirect('/sprayer')
            ->assertSessionHas('status', 'sprayer-status-updated');

        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'sprayer_status' => 'on',
        ]);

        $this->assertDatabaseHas('spray_logs', [
            'device_id' => $device->id,
            'trigger_type' => 'manual',
            'status' => 'on',
            'reason' => 'Sprayer dinyalakan manual dari website',
            'created_by' => null,
        ]);
    }

    public function test_sprayer_can_be_turned_off_in_manual_mode_and_log_it(): void
    {
        $device = $this->makeDevice(mode: 'manual', sprayerStatus: 'on');

        $this->post('/sprayer/status', [
            'status' => 'off',
        ])
            ->assertRedirect('/sprayer')
            ->assertSessionHas('status', 'sprayer-status-updated');

        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'sprayer_status' => 'off',
        ]);

        $this->assertDatabaseHas('spray_logs', [
            'device_id' => $device->id,
            'trigger_type' => 'manual',
            'status' => 'off',
            'reason' => 'Sprayer dimatikan manual dari website',
            'created_by' => null,
        ]);
    }

    public function test_manual_status_change_is_rejected_when_device_still_automatic(): void
    {
        $device = $this->makeDevice(mode: 'automatic', sprayerStatus: 'off');

        $this->from('/sprayer')
            ->post('/sprayer/status', [
                'status' => 'on',
            ])
            ->assertRedirect('/sprayer')
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'sprayer_status' => 'off',
        ]);

        $this->assertDatabaseMissing('spray_logs', [
            'device_id' => $device->id,
            'trigger_type' => 'manual',
            'status' => 'on',
        ]);
    }

    private function makeDevice(string $mode, string $sprayerStatus): Device
    {
        $device = Device::query()->create([
            'name' => 'Sprayer Lahan Kontrol',
            'location' => 'Brebes',
            'api_key' => uniqid('sprayer-', true),
            'mode' => $mode,
            'sprayer_status' => $sprayerStatus,
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
