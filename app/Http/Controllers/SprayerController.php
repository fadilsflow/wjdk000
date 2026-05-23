<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

class SprayerController extends Controller
{
    public function index(): View
    {
        $device = [
            'name'           => 'Sprayer Lahan A',
            'mode'           => 'automatic',
            'sprayer_status' => 'off',
        ];

        $sensor = [
            'temperature'      => 31.5,
            'air_humidity'     => 70,
            'soil_moisture'    => 35,
            'rain_status'      => 'no_rain',
            'sprayer_status'   => 'off',
            'condition_status' => 'kritis',
            'recorded_at'      => '2026-05-20 10:00:00',
        ];

        $logs = [
            ['time' => '10:00', 'trigger' => 'automatic', 'status' => 'on',  'reason' => 'Tanah kering, tidak hujan', 'by' => 'Sistem'],
            ['time' => '09:30', 'trigger' => 'automatic', 'status' => 'off', 'reason' => 'Durasi terpenuhi',          'by' => 'Sistem'],
            ['time' => '08:15', 'trigger' => 'manual',    'status' => 'on',  'reason' => '—',                          'by' => 'Petani Demo'],
            ['time' => '08:00', 'trigger' => 'manual',    'status' => 'off', 'reason' => '—',                          'by' => 'Petani Demo'],
        ];

        return view('sprayer.control', compact('device', 'sensor', 'logs'));
    }
}
