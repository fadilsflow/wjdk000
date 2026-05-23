<?php

use App\Http\Controllers\LandingController;
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

// Public landing
Route::get('/', [LandingController::class, 'index'])->name('home');

// Dashboard (authenticated app)
Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

// History
Route::get('/history/sensor', [App\Http\Controllers\HistoryController::class, 'sensor'])->name('history.sensor');
Route::get('/history/spray', [App\Http\Controllers\HistoryController::class, 'spray'])->name('history.spray');
Route::get('/history/notification', [App\Http\Controllers\HistoryController::class, 'notification'])->name('history.notification');

// Admin
Route::get('/admin/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
Route::get('/admin/devices', [App\Http\Controllers\Admin\DeviceController::class, 'index'])->name('admin.devices.index');
Route::get('/admin/whatsapp', [App\Http\Controllers\Admin\WhatsappController::class, 'index'])->name('admin.whatsapp.index');
