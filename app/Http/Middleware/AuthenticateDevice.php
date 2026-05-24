<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Repositories\DeviceRepository;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AuthenticateDevice
{
    public function __construct(
        private readonly DeviceRepository $deviceRepository,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $this->resolveApiKey($request);

        if ($apiKey === null) {
            return $this->unauthorizedResponse('API key perangkat wajib dikirim.');
        }

        $device = $this->deviceRepository->findByApiKey($apiKey);

        if ($device === null) {
            return $this->unauthorizedResponse('Perangkat tidak terdaftar.');
        }

        $routeDevice = $request->route('device');

        if ($routeDevice !== null && (int) $routeDevice->id !== (int) $device->id) {
            return $this->unauthorizedResponse('API key tidak cocok dengan perangkat tujuan.');
        }

        $request->attributes->set('authenticatedDevice', $device);

        return $next($request);
    }

    private function resolveApiKey(Request $request): ?string
    {
        $apiKey = $request->input('api_key');

        if (is_string($apiKey) && $apiKey !== '') {
            return $apiKey;
        }

        $headerApiKey = $request->header('X-Api-Key');

        return is_string($headerApiKey) && $headerApiKey !== '' ? $headerApiKey : null;
    }

    private function unauthorizedResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => null,
        ], 401);
    }
}
