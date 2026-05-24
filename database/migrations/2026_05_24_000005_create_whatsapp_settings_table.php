<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('recipient_phone', 20)->nullable();
            $table->text('critical_condition_template')->nullable();
            $table->text('spray_start_template')->nullable();
            $table->text('spray_stop_template')->nullable();
            $table->text('rain_detected_template')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_settings');
    }
};
