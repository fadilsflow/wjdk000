<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Device;
use App\Models\SensorReading;
use App\Models\ThresholdSetting;
use App\Models\User;
use Tests\Concerns\UsesMysqlTestDatabase;
use Tests\TestCase;

final class SprintNinePublicSummaryTest extends TestCase
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

    public function test_public_summary_page_is_accessible_without_login_and_renders_latest_sensor_data(): void
    {
        $device = $this->makeDevice();

        SensorReading::query()->create([
            'device_id' => $device->id,
            'temperature' => 31.5,
            'air_humidity' => 70,
            'soil_moisture' => 35,
            'rain_status' => 'no_rain',
            'sprayer_status' => 'on',
            'condition_status' => 'kritis',
            'recorded_at' => now(),
        ]);

        $this->get('/public/summary')
            ->assertOk()
            ->assertSeeText('Smart Sprayer')
            ->assertSeeText('31.5')
            ->assertSeeText('70')
            ->assertSeeText('35')
            ->assertSeeText('kritis')
            ->assertSeeText('Sprayer Publik')
            ->assertSeeText('Data Publik');
    }

    public function test_public_summary_hides_control_and_sensitive_information(): void
    {
        $device = $this->makeDevice();
        $user = User::factory()->create([
            'name' => 'Petani Rahasia',
            'phone_number' => '+628123999999',
        ]);

        SensorReading::query()->create([
            'device_id' => $device->id,
            'temperature' => 30.1,
            'air_humidity' => 74,
            'soil_moisture' => 48,
            'rain_status' => 'rain',
            'sprayer_status' => 'off',
            'condition_status' => 'normal',
            'recorded_at' => now(),
        ]);

        $response = $this->get('/');

        $response->assertOk()
            ->assertDontSeeText('Kontrol Sprayer')
            ->assertDontSeeText('Nyalakan')
            ->assertDontSeeText('Matikan')
            ->assertDontSeeText($user->phone_number)
            ->assertDontSeeText($user->name)
            ->assertDontSeeText('Pengaturan WhatsApp');
    }

    private function makeDevice(): Device
    {
        $device = Device::query()->create([
            'name' => 'Sprayer Publik',
            'location' => 'Brebes',
            'api_key' => uniqid('public-', true),
            'mode' => 'automatic',
            'sprayer_status' => 'on',
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
