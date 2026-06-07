<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Device;
use App\Models\ThresholdSetting;
use App\Models\User;
use App\Models\WhatsappSetting;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $adminPassword = env('ADMIN_SEED_PASSWORD');

        if (! is_string($adminPassword) || $adminPassword === '') {
            throw new RuntimeException('ADMIN_SEED_PASSWORD must be set before running database seeder.');
        }

        User::query()->updateOrCreate(
            ['email' => (string) env('ADMIN_SEED_EMAIL', 'admin@smartsprayer.test')],
            [
                'name' => (string) env('ADMIN_SEED_NAME', 'Admin Smart Sprayer'),
                'password' => Hash::make($adminPassword),
                'role' => 'admin',
                'phone_number' => env('ADMIN_SEED_PHONE') ?: null,
                'email_verified_at' => now(),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'petani@smartsprayer.test'],
            [
                'name' => 'Petani Smart Sprayer',
                'password' => Hash::make('password'),
                'role' => 'petani',
                'phone_number' => '+6281234567890',
                'email_verified_at' => now(),
            ],
        );

        if (class_exists(Factory::class)) {
            User::factory()->count(2)->create();
        }

        $device = Device::query()->firstOrCreate(
            ['name' => (string) env('DEVICE_SEED_NAME', 'Smart Sprayer Brebes')],
            [
                'location' => (string) env('DEVICE_SEED_LOCATION', 'Brebes, Jawa Tengah'),
                'api_key' => env('DEVICE_SEED_API_KEY') ?: Str::random(32),
                'mode' => 'automatic',
                'sprayer_status' => 'off',
            ],
        );

        ThresholdSetting::query()->firstOrCreate(
            ['device_id' => $device->id],
            [
                'min_soil_moisture' => 40.0,
                'max_temperature' => 35.0,
                'min_air_humidity' => 60.0,
            ],
        );

        WhatsappSetting::query()->firstOrCreate(
            ['id' => 1],
            [
                'recipient_phone' => '+628123456700',
                'critical_condition_template' => 'Kritis {{device_name}} {{condition_status}} {{soil_moisture}}',
                'spray_start_template' => 'Mulai {{device_name}} {{sprayer_status}} {{reason}}',
                'spray_stop_template' => 'Stop {{device_name}} {{sprayer_status}} {{reason}}',
                'rain_detected_template' => 'Hujan {{device_name}} {{rain_status}} {{sprayer_status}}',
            ],
        );
    }
}
