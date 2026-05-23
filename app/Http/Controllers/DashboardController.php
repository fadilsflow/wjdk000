<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $sensor = [
            'temperature' => 31.5,
            'air_humidity' => 70,
            'soil_moisture' => 35,
            'rain_status' => 'no_rain',
            'sprayer_status' => 'on',
            'condition_status' => 'kritis',
            'recorded_at' => '2026-05-20 10:00:00',
        ];

        $device = [
            'name' => 'Sprayer Lahan A',
            'mode' => 'automatic',
        ];

        return view('dashboard', compact('sensor', 'device'));
    }
}
