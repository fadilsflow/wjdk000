<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\JsonResponse;

final class DeviceCommandController extends Controller
{
    public function show(Device $device): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Perintah perangkat berhasil diambil.',
            'data' => [
                'mode' => $device->mode,
                'sprayer_command' => $device->sprayer_status,
            ],
            'errors' => null,
        ]);
    }
}
