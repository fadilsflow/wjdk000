<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Device;
use App\Models\NotificationLog;
use App\Models\SensorReading;
use App\Models\SprayLog;
use App\Models\ThresholdSetting;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\UsesMysqlTestDatabase;
use Tests\TestCase;

final class SprintThreeDatabaseSchemaTest extends TestCase
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

    public function test_domain_tables_exist_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasTable('devices'));
        $this->assertTrue(Schema::hasTable('sensor_readings'));
        $this->assertTrue(Schema::hasTable('threshold_settings'));
        $this->assertTrue(Schema::hasTable('spray_logs'));
        $this->assertTrue(Schema::hasTable('notification_logs'));

        $this->assertTrue(Schema::hasColumns('devices', ['name', 'location', 'api_key', 'mode', 'sprayer_status']));
        $this->assertTrue(Schema::hasColumns('sensor_readings', ['device_id', 'temperature', 'air_humidity', 'soil_moisture', 'rain_status', 'sprayer_status', 'condition_status', 'recorded_at']));
        $this->assertTrue(Schema::hasColumns('threshold_settings', ['device_id', 'min_soil_moisture', 'max_temperature', 'min_air_humidity']));
        $this->assertTrue(Schema::hasColumns('spray_logs', ['device_id', 'trigger_type', 'status', 'reason', 'created_by']));
        $this->assertTrue(Schema::hasColumns('notification_logs', ['device_id', 'type', 'recipient_phone', 'message', 'status', 'sent_at']));
    }

    public function test_models_persist_and_resolve_relationships(): void
    {
        $user = User::factory()->admin()->create();

        $device = Device::query()->create([
            'name' => 'Sprayer Lahan A',
            'location' => 'Brebes',
            'api_key' => 'device-key-001',
            'mode' => 'automatic',
            'sprayer_status' => 'off',
        ]);

        $threshold = ThresholdSetting::query()->create([
            'device_id' => $device->id,
            'min_soil_moisture' => 40.5,
            'max_temperature' => 32.5,
            'min_air_humidity' => 60.0,
        ]);

        $reading = SensorReading::query()->create([
            'device_id' => $device->id,
            'temperature' => 31.2,
            'air_humidity' => 70.1,
            'soil_moisture' => 35.9,
            'rain_status' => 'no_rain',
            'sprayer_status' => 'off',
            'condition_status' => 'kritis',
            'recorded_at' => now(),
        ]);

        $sprayLog = SprayLog::query()->create([
            'device_id' => $device->id,
            'trigger_type' => 'manual',
            'status' => 'on',
            'reason' => 'Tes kontrol',
            'created_by' => $user->id,
        ]);

        $notificationLog = NotificationLog::query()->create([
            'device_id' => $device->id,
            'type' => 'spray_start',
            'recipient_phone' => '+628123456789',
            'message' => 'Sprayer mulai.',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->assertSame('device-key-001', $device->getAttributes()['api_key']);
        $this->assertArrayNotHasKey('api_key', $device->toArray());
        $this->assertSame($threshold->id, $device->thresholdSetting?->id);
        $this->assertSame($reading->id, $device->sensorReadings()->first()?->id);
        $this->assertSame($sprayLog->id, $device->sprayLogs()->first()?->id);
        $this->assertSame($notificationLog->id, $device->notificationLogs()->first()?->id);
        $this->assertSame($user->id, $sprayLog->creator?->id);
        $this->assertIsFloat($reading->temperature);
        $this->assertIsFloat($threshold->min_soil_moisture);
    }
}
