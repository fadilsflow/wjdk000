<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

class HistoryController extends Controller
{
    public function sensor(): View
    {
        $readings = [
            ['time' => '10:00', 'temp' => 31.5, 'hum' => 70, 'soil' => 35, 'rain' => 'no_rain', 'sprayer' => 'on', 'condition' => 'kritis'],
            ['time' => '09:45', 'temp' => 31.2, 'hum' => 71, 'soil' => 38, 'rain' => 'no_rain', 'sprayer' => 'on', 'condition' => 'waspada'],
            ['time' => '09:30', 'temp' => 30.8, 'hum' => 72, 'soil' => 42, 'rain' => 'no_rain', 'sprayer' => 'off', 'condition' => 'normal'],
            ['time' => '09:15', 'temp' => 30.5, 'hum' => 73, 'soil' => 45, 'rain' => 'no_rain', 'sprayer' => 'off', 'condition' => 'normal'],
            ['time' => '09:00', 'temp' => 30.1, 'hum' => 74, 'soil' => 48, 'rain' => 'rain', 'sprayer' => 'off', 'condition' => 'normal'],
            ['time' => '08:45', 'temp' => 29.8, 'hum' => 75, 'soil' => 52, 'rain' => 'rain', 'sprayer' => 'off', 'condition' => 'normal'],
            ['time' => '08:30', 'temp' => 29.5, 'hum' => 76, 'soil' => 55, 'rain' => 'no_rain', 'sprayer' => 'off', 'condition' => 'normal'],
            ['time' => '08:15', 'temp' => 29.2, 'hum' => 77, 'soil' => 50, 'rain' => 'no_rain', 'sprayer' => 'off', 'condition' => 'normal'],
            ['time' => '08:00', 'temp' => 28.8, 'hum' => 78, 'soil' => 48, 'rain' => 'no_rain', 'sprayer' => 'off', 'condition' => 'normal'],
            ['time' => '07:45', 'temp' => 28.5, 'hum' => 79, 'soil' => 50, 'rain' => 'no_rain', 'sprayer' => 'off', 'condition' => 'normal'],
        ];

        return view('history.sensor', compact('readings'));
    }

    public function spray(): View
    {
        $logs = [
            ['time' => '10:00', 'trigger' => 'automatic', 'status' => 'on', 'reason' => 'Tanah kering, kondisi kritis', 'by' => 'Sistem'],
            ['time' => '09:45', 'trigger' => 'manual', 'status' => 'off', 'reason' => 'Penyemprotan selesai', 'by' => 'Petani'],
            ['time' => '09:30', 'trigger' => 'automatic', 'status' => 'on', 'reason' => 'Soil moisture < threshold', 'by' => 'Sistem'],
            ['time' => '09:00', 'trigger' => 'manual', 'status' => 'off', 'reason' => 'Pengecekan rutin', 'by' => 'Admin'],
            ['time' => '08:30', 'trigger' => 'automatic', 'status' => 'on', 'reason' => 'Deteksi tanah kering', 'by' => 'Sistem'],
            ['time' => '07:00', 'trigger' => 'automatic', 'status' => 'off', 'reason' => 'Hujan terdeteksi', 'by' => 'Sistem'],
            ['time' => '06:30', 'trigger' => 'automatic', 'status' => 'on', 'reason' => 'Tanah kering, tidak hujan', 'by' => 'Sistem'],
            ['time' => '06:00', 'trigger' => 'manual', 'status' => 'off', 'reason' => 'Overridde user', 'by' => 'Petani'],
            ['time' => '05:30', 'trigger' => 'manual', 'status' => 'on', 'reason' => 'Permintaan petani', 'by' => 'Petani'],
            ['time' => '05:00', 'trigger' => 'automatic', 'status' => 'off', 'reason' => 'Tanah cukup basah', 'by' => 'Sistem'],
        ];

        return view('history.spray', compact('logs'));
    }

    public function notification(): View
    {
        $notifications = [
            ['time' => '10:00', 'type' => 'Penyemprotan Dimulai', 'phone' => '+6281xxxxxxx', 'message' => 'Penyemprotan otomatis dimulai...', 'status' => 'sent'],
            ['time' => '09:45', 'type' => 'Penyemprotan Berhenti', 'phone' => '+6281xxxxxxx', 'message' => 'Penyemprotan dihentikan...', 'status' => 'sent'],
            ['time' => '09:30', 'type' => 'Kondisi Kritis', 'phone' => '+6281xxxxxxx', 'message' => 'Tanah kering, segera lakukan...', 'status' => 'sent'],
            ['time' => '09:00', 'type' => 'Hujan Terdeteksi', 'phone' => '+6281xxxxxxx', 'message' => 'Hujan terdeteksi, penyemprotan...', 'status' => 'sent'],
            ['time' => '08:30', 'type' => 'Penyemprotan Dimulai', 'phone' => '+6281xxxxxxx', 'message' => 'Penyemprotan otomatis dimulai...', 'status' => 'failed'],
            ['time' => '08:00', 'type' => 'Kondisi Waspada', 'phone' => '+6281xxxxxxx', 'message' => 'Kelembapan tanah menurun...', 'status' => 'sent'],
            ['time' => '07:30', 'type' => 'Penyemprotan Dimulai', 'phone' => '+6281xxxxxxx', 'message' => 'Penyemprotan otomatis dimulai...', 'status' => 'sent'],
            ['time' => '07:00', 'type' => 'Penyemprotan Berhenti', 'phone' => '+6281xxxxxxx', 'message' => 'Penyemprotan dihentikan...', 'status' => 'sent'],
            ['time' => '06:30', 'type' => 'Hujan Terdeteksi', 'phone' => '+6281xxxxxxx', 'message' => 'Hujan terdeteksi, penyemprotan...', 'status' => 'sent'],
            ['time' => '06:00', 'type' => 'Penyemprotan Dimulai', 'phone' => '+6281xxxxxxx', 'message' => 'Penyemprotan otomatis dimulai...', 'status' => 'sent'],
        ];

        return view('history.notification', compact('notifications'));
    }
}
