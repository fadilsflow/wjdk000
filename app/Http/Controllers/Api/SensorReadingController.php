<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreSensorReadingRequest;
use App\Models\Device;
use App\Services\IotSensorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

final class SensorReadingController extends Controller
{
    public function __construct(
        private readonly IotSensorService $iotSensorService,
    ) {}

    public function store(StoreSensorReadingRequest $request): JsonResponse
    {
        /** @var Device $device */
        $device = $request->attributes->get('authenticatedDevice');

        try {
            $result = $this->iotSensorService->storeReading($device, $request->validated());
        } catch (ValidationException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Pemrosesan data sensor gagal.',
                'data' => null,
                'errors' => $exception->errors(),
            ], 422);
        }

        return response()->json($result);
    }
}
