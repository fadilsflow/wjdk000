<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Device;
use App\Models\ThresholdSetting;
use Tests\Concerns\UsesMysqlTestDatabase;
use Tests\TestCase;

final class AdminDeviceConfigurationTest extends TestCase
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

    public function test_admin_device_page_renders_registered_devices_from_database(): void
    {
        $device = Device::query()->create([
            'name' => 'Sprayer Admin A',
            'location' => 'Brebes Barat',
            'api_key' => 'device-admin-a',
            'mode' => 'automatic',
            'sprayer_status' => 'on',
        ]);

        ThresholdSetting::query()->create([
            'device_id' => $device->id,
            'min_soil_moisture' => 40,
            'max_temperature' => 32,
            'min_air_humidity' => 60,
        ]);

        $this->get('/admin/devices')
            ->assertOk()
            ->assertSeeText('Sprayer Admin A')
            ->assertSeeText('Brebes Barat')
            ->assertSeeText('Automatic')
            ->assertSeeText('ON')
            ->assertSee('value="40"', false)
            ->assertSee('value="32"', false)
            ->assertSee('value="60"', false);
    }
}
