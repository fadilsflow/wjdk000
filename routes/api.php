<?php

declare(strict_types=1);

use App\Http\Controllers\Api\DeviceCommandController;
use App\Http\Controllers\Api\SensorReadingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:60,1', 'device.auth'])->group(function (): void {
    Route::post('/sensor-readings', [SensorReadingController::class, 'store']);
    Route::get('/devices/{device}/command', [DeviceCommandController::class, 'show']);
});
