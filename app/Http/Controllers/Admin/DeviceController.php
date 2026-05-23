<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DeviceController extends Controller
{
    public function index(): View
    {
        $devices = [
            ['id' => 1, 'name' => 'Sprayer Lahan A', 'location' => 'Brebes', 'api_key' => 'sspiot_a1b2c3d4e5f6', 'mode' => 'automatic', 'sprayer_status' => 'on'],
            ['id' => 2, 'name' => 'Sprayer Lahan B', 'location' => 'Brebes Timur', 'api_key' => 'sspiot_f6e5d4c3b2a1', 'mode' => 'manual', 'sprayer_status' => 'off'],
        ];

        $thresholds = [
            'device_id' => 1,
            'min_soil_moisture' => 40,
            'max_temperature' => 32,
            'min_air_humidity' => 60,
        ];

        return view('admin.devices.index', compact('devices', 'thresholds'));
    }
}
