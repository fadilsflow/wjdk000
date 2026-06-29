<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Device;
use App\Models\ThresholdSetting;
use Tests\Concerns\UsesMysqlTestDatabase;
use Tests\TestCase;

final class SprintTwoBackendCoreValidationTest extends TestCase
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

    public function test_sensor_endpoint_returns_standard_validation_wrapper_for_invalid_payload(): void
    {
        $device = $this->makeDevice(name: 'Sprayer Validation');

        $response = $this->postJson('/api/sensor-readings', [
            'api_key' => $device->getAttributes()['api_key'],
            'temperature' => 120,
            'air_humidity' => 150,
            'soil_moisture' => -1,
            'rain_status' => 'storm',
            'sprayer_status' => 'idle',
            'recorded_at' => 'not-a-date',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validasi data sensor gagal.')
            ->assertJsonPath('data', null)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'errors' => [
                    'temperature',
                    'air_humidity',
                    'soil_moisture',
                    'rain_status',
                    'sprayer_status',
                    'recorded_at',
                ],
            ]);
    }

    public function test_device_command_endpoint_rejects_mismatched_api_key(): void
    {
        $device = $this->makeDevice(name: 'Sprayer A');
        $otherDevice = $this->makeDevice(name: 'Sprayer B');

        $this->getJson("/api/devices/{$device->id}/command", [
            'X-Api-Key' => $otherDevice->getAttributes()['api_key'],
        ])->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'API key tidak cocok dengan perangkat tujuan.');
    }

    public function test_invalid_sprayer_mode_is_rejected_by_validation(): void
    {
        $this->makeDevice(name: 'Sprayer Mode');

        $this->from('/sprayer')
            ->post('/sprayer/mode', [
                'mode' => 'auto',
            ])
            ->assertRedirect('/sprayer')
            ->assertSessionHasErrors('mode');
    }

    public function test_admin_can_update_threshold_configuration(): void
    {
        $device = $this->makeDevice(name: 'Sprayer Threshold');

        $this->put('/admin/threshold', [
                'device_id' => $device->id,
                'min_soil_moisture' => 33,
                'max_temperature' => 30,
            ])
            ->assertRedirect('/admin/devices')
            ->assertSessionHas('status', 'threshold-updated');

        $this->assertDatabaseHas('threshold_settings', [
            'device_id' => $device->id,
            'min_soil_moisture' => 33.00,
            'max_temperature' => 30.00,
        ]);
    }

    private function makeDevice(string $name): Device
    {
        $device = Device::query()->create([
            'name' => $name,
            'location' => 'Brebes',
            'api_key' => uniqid('device-', true),
            'mode' => 'automatic',
            'sprayer_status' => 'off',
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
