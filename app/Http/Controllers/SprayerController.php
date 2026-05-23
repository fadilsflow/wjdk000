<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

class SprayerController extends Controller
{
    public function index(): View
    {
        $device = [
            'mode' => 'manual',
            'sprayer_status' => 'off',
        ];

        $lastSensor = [
            'rain_status' => 'no_rain',
            'soil_moisture' => 35,
            'condition_status' => 'kritis',
        ];

        $recentLogs = [
            ['time' => '10:00', 'trigger' => 'automatic', 'status' => 'on', 'reason' => 'Tanah kering, tidak hujan', 'by' => 'Sistem'],
            ['time' => '09:45', 'trigger' => 'manual', 'status' => 'off', 'reason' => 'Penyemprotan selesai', 'by' => 'Petani'],
            ['time' => '09:30', 'trigger' => 'automatic', 'status' => 'on', 'reason' => 'Tanah kering', 'by' => 'Sistem'],
            ['time' => '09:00', 'trigger' => 'manual', 'status' => 'off', 'reason' => 'Cek alat', 'by' => 'Admin'],
            ['time' => '08:30', 'trigger' => 'automatic', 'status' => 'on', 'reason' => 'Soil moisture turun', 'by' => 'Sistem'],
        ];

        return view('sprayer.control', compact('device', 'lastSensor', 'recentLogs'));
    }
}
