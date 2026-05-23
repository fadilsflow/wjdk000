<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SprayerController;
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

// Sprayer control
Route::get('/sprayer', [SprayerController::class, 'index'])->name('sprayer.control');

// History
Route::get('/history/sensor', [App\Http\Controllers\HistoryController::class, 'sensor'])->name('history.sensor');
Route::get('/history/spray', [App\Http\Controllers\HistoryController::class, 'spray'])->name('history.spray');
Route::get('/history/notification', [App\Http\Controllers\HistoryController::class, 'notification'])->name('history.notification');

// Admin
Route::get('/admin/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
Route::get('/admin/devices', [App\Http\Controllers\Admin\DeviceController::class, 'index'])->name('admin.devices.index');
Route::get('/admin/whatsapp', [App\Http\Controllers\Admin\WhatsappController::class, 'index'])->name('admin.whatsapp.index');

// Profile
Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

// Auth — guest
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store']);

Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);

Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');

// Auth — authenticated
Route::get('/verify-email', EmailVerificationPromptController::class)->name('verification.notice');
Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)->name('verification.verify');
Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->name('verification.send');

Route::get('/confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
Route::post('/confirm-password', [ConfirmablePasswordController::class, 'store']);

Route::put('/password', [PasswordController::class, 'update'])->name('password.update');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
