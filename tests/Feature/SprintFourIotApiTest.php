<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Device;
use App\Models\ThresholdSetting;
use Illuminate\Support\Facades\Config;
use Tests\Concerns\UsesMysqlTestDatabase;
use Tests\TestCase;

final class SprintFourIotApiTest extends TestCase
{
    use UsesMysqlTestDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useMysqlTestDatabase();
        Config::set('services.whatsapp.gateway_url', null);
        Config::set('services.whatsapp.gateway_token', null);
        Config::set('services.whatsapp.sender_number', null);
    }

    protected function tearDown(): void
    {
        $this->rollbackMysqlTestDatabase();
        parent::tearDown();
    }

    public function test_sensor_endpoint_rejects_unregistered_device(): void
    {
        $this->postJson('/api/sensor-readings', [
            'api_key' => 'invalid-key',
            'temperature' => 31.5,
            'air_humidity' => 70,
            'soil_moisture' => 35,
            'rain_status' => 'no_rain',
            'sprayer_status' => 'off',
            'recorded_at' => now()->toDateTimeString(),
        ])->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_sensor_endpoint_saves_reading_and_returns_on_command_for_critical_automatic_condition(): void
    {
        $device = $this->makeDeviceWithThreshold(mode: 'automatic', sprayerStatus: 'off');

        $response = $this->postJson('/api/sensor-readings', [
            'api_key' => $device->getAttributes()['api_key'],
            'temperature' => 31.5,
            'air_humidity' => 70,
            'soil_moisture' => 35,
            'rain_status' => 'no_rain',
            'sprayer_status' => 'off',
            'recorded_at' => now()->toDateTimeString(),
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('condition_status', 'kritis')
            ->assertJsonPath('mode', 'automatic')
            ->assertJsonPath('sprayer_command', 'on');

        $this->assertDatabaseHas('sensor_readings', [
            'device_id' => $device->id,
            'condition_status' => 'kritis',
        ]);

        $this->assertDatabaseHas('spray_logs', [
            'device_id' => $device->id,
            'trigger_type' => 'automatic',
            'status' => 'on',
        ]);

        $this->assertDatabaseHas('notification_logs', [
            'device_id' => $device->id,
            'type' => 'critical_condition',
        ]);
    }

    public function test_sensor_endpoint_accepts_actual_esp32_payload_aliases(): void
    {
        $device = $this->makeDeviceWithThreshold(mode: 'automatic', sprayerStatus: 'off');

        $response = $this->postJson('/api/sensor-readings', [
            'temperature' => 29.4,
            'humidity' => 72.5,
            'soilPercent' => 33,
            'raining' => false,
            'pumpOn' => false,
            'soilRaw' => 2450,
            'rainRaw' => 3500,
            'simulationMode' => false,
        ], [
            'X-Api-Key' => $device->getAttributes()['api_key'],
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('condition_status', 'kritis')
            ->assertJsonPath('sprayer_command', 'on');

        $this->assertDatabaseHas('sensor_readings', [
            'device_id' => $device->id,
            'air_humidity' => 72.50,
            'soil_moisture' => 33.00,
            'soil_raw' => 2450,
            'rain_status' => 'no_rain',
            'rain_raw' => 3500,
            'sprayer_status' => 'off',
            'simulation_mode' => false,
            'condition_status' => 'kritis',
        ]);
    }

    public function test_sensor_endpoint_forces_off_when_rain_detected(): void
    {
        $device = $this->makeDeviceWithThreshold(mode: 'automatic', sprayerStatus: 'on');

        $response = $this->postJson('/api/sensor-readings', [
            'api_key' => $device->getAttributes()['api_key'],
            'temperature' => 28.0,
            'air_humidity' => 80,
            'soil_moisture' => 20,
            'rain_status' => 'rain',
            'sprayer_status' => 'on',
            'recorded_at' => now()->toDateTimeString(),
        ]);

        $response->assertOk()
            ->assertJsonPath('condition_status', 'normal')
            ->assertJsonPath('sprayer_command', 'off');

        $this->assertDatabaseHas('notification_logs', [
            'device_id' => $device->id,
            'type' => 'rain_detected',
        ]);
    }

    public function test_device_command_endpoint_returns_current_mode_and_command(): void
    {
        $device = $this->makeDeviceWithThreshold(mode: 'manual', sprayerStatus: 'off');

        $this->getJson("/api/devices/{$device->id}/command", [
            'X-Api-Key' => $device->getAttributes()['api_key'],
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.mode', 'manual')
            ->assertJsonPath('data.sprayer_command', 'off');
    }

    private function makeDeviceWithThreshold(string $mode, string $sprayerStatus): Device
    {
        $device = Device::query()->create([
            'name' => 'Sprayer IoT A',
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
