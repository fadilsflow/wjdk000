<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sensor_readings', function (Blueprint $table): void {
            $table->unsignedSmallInteger('soil_raw')->nullable()->after('soil_moisture');
            $table->unsignedSmallInteger('rain_raw')->nullable()->after('rain_status');
            $table->boolean('simulation_mode')->default(false)->after('sprayer_status');
        });
    }

    public function down(): void
    {
        Schema::table('sensor_readings', function (Blueprint $table): void {
            $table->dropColumn(['soil_raw', 'rain_raw', 'simulation_mode']);
        });
    }
};
