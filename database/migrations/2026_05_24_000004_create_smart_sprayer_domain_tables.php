<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('location');
            $table->string('api_key')->unique();
            $table->string('mode')->default('manual');
            $table->string('sprayer_status')->default('off');
            $table->timestamps();
        });

        Schema::create('threshold_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('device_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('min_soil_moisture', 5, 2);
            $table->decimal('max_temperature', 5, 2);
            $table->decimal('min_air_humidity', 5, 2);
            $table->timestamps();
        });

        Schema::create('sensor_readings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->decimal('temperature', 5, 2);
            $table->decimal('air_humidity', 5, 2);
            $table->decimal('soil_moisture', 5, 2);
            $table->string('rain_status');
            $table->string('sprayer_status');
            $table->string('condition_status');
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['device_id', 'recorded_at']);
        });

        Schema::create('spray_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('trigger_type');
            $table->string('status');
            $table->text('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['device_id', 'created_at']);
        });

        Schema::create('notification_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('recipient_phone', 20);
            $table->text('message');
            $table->string('status');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['device_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('spray_logs');
        Schema::dropIfExists('sensor_readings');
        Schema::dropIfExists('threshold_settings');
        Schema::dropIfExists('devices');
    }
};
