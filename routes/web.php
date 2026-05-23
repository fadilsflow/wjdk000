<?php

use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Frontend Static
|--------------------------------------------------------------------------
|
| All routes return Blade views with hardcoded dummy data.
| Backend team will wire these to real controllers/auth later.
|
*/

// Home/Dashboard
Route::get('/', fn() => redirect()->route('dashboard'));
Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

// Sprayer
Route::get('/sprayer/control', [App\Http\Controllers\SprayerController::class, 'index'])->name('sprayer.control');

// History
Route::get('/history/sensor', [App\Http\Controllers\HistoryController::class, 'sensor'])->name('history.sensor');
Route::get('/history/spray', [App\Http\Controllers\HistoryController::class, 'spray'])->name('history.spray');
Route::get('/history/notification', [App\Http\Controllers\HistoryController::class, 'notification'])->name('history.notification');

// Admin
Route::get('/admin/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
Route::get('/admin/devices', [App\Http\Controllers\Admin\DeviceController::class, 'index'])->name('admin.devices.index');
Route::get('/admin/whatsapp', [App\Http\Controllers\Admin\WhatsappController::class, 'index'])->name('admin.whatsapp.index');

// Public
Route::get('/public', [PublicController::class, 'summary'])->name('public.summary');
