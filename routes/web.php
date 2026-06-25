<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\DeviceController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WhatsappController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\SprayerController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/latest', [DashboardController::class, 'latest'])->name('dashboard.latest');

Route::get('/sprayer', [SprayerController::class, 'index'])->name('sprayer.control');
Route::get('/sprayer/latest', [SprayerController::class, 'latest'])->name('sprayer.latest');
Route::post('/sprayer/mode', [SprayerController::class, 'updateMode'])->name('sprayer.mode.update');
Route::post('/sprayer/status', [SprayerController::class, 'updateStatus'])->name('sprayer.status.update');

Route::get('/history/sensor', [HistoryController::class, 'sensor'])->name('history.sensor');
Route::get('/history/spray', [HistoryController::class, 'spray'])->name('history.spray');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/devices', [DeviceController::class, 'index'])->name('devices.index');
    Route::post('/devices', [DeviceController::class, 'store'])->name('devices.store');
    Route::put('/devices/{device}', [DeviceController::class, 'update'])->name('devices.update');
    Route::put('/threshold', [DeviceController::class, 'updateThreshold'])->name('threshold.update');
    Route::get('/whatsapp', [WhatsappController::class, 'index'])->name('whatsapp.index');
    Route::put('/whatsapp', [WhatsappController::class, 'update'])->name('whatsapp.update');
});
