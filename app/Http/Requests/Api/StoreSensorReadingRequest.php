<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

final class StoreSensorReadingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $apiKey = $this->input('api_key');
        $humidity = $this->input('air_humidity', $this->input('humidity', 0));
        if ($humidity === null || $humidity === '') {
            $humidity = 0;
        }
        $soilMoisture = $this->input('soil_moisture', $this->input('soilPercent'));
        $rainStatus = $this->input('rain_status');
        $sprayerStatus = $this->input('sprayer_status');

        if ($rainStatus === null && $this->has('raining')) {
            $rainStatus = $this->boolean('raining') ? 'rain' : 'no_rain';
        }

        if ($sprayerStatus === null && $this->has('pumpOn')) {
            $sprayerStatus = $this->boolean('pumpOn') ? 'on' : 'off';
        }

        $this->merge([
            'api_key' => is_string($apiKey) && $apiKey !== '' ? $apiKey : $this->header('X-Api-Key'),
            'air_humidity' => $humidity,
            'soil_moisture' => $soilMoisture,
            'rain_status' => $rainStatus,
            'sprayer_status' => $sprayerStatus,
            'soil_raw' => $this->input('soil_raw', $this->input('soilRaw')),
            'rain_raw' => $this->input('rain_raw', $this->input('rainRaw')),
            'simulation_mode' => $this->input('simulation_mode', $this->input('simulationMode', false)),
            'recorded_at' => $this->input('recorded_at', now()->toIso8601String()),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'api_key' => ['required', 'string'],
            'temperature' => ['required', 'numeric', 'between:-50,100'],
            'air_humidity' => ['nullable', 'numeric', 'between:0,100'],
            'soil_moisture' => ['required', 'numeric', 'between:0,100'],
            'rain_status' => ['required', 'in:rain,no_rain'],
            'sprayer_status' => ['required', 'in:on,off'],
            'soil_raw' => ['nullable', 'integer', 'between:0,4095'],
            'rain_raw' => ['nullable', 'integer', 'between:0,4095'],
            'simulation_mode' => ['required', 'boolean'],
            'recorded_at' => ['required', 'date'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validasi data sensor gagal.',
            'data' => null,
            'errors' => $validator->errors(),
        ], 422));
    }
}
