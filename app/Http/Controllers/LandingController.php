<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        $sensor = [
            'temperature'      => 31.5,
            'air_humidity'     => 70,
            'soil_moisture'    => 35,
            'rain_status'      => 'no_rain',
            'condition_status' => 'normal',
            'recorded_at'      => '2026-05-20 10:00:00',
        ];

        return view('landing', compact('sensor'));
    }
}
