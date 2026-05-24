<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

final class StoreSensorReadingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'api_key' => ['required', 'string'],
            'temperature' => ['required', 'numeric', 'between:-50,100'],
            'air_humidity' => ['required', 'numeric', 'between:0,100'],
            'soil_moisture' => ['required', 'numeric', 'between:0,100'],
            'rain_status' => ['required', 'in:rain,no_rain'],
            'sprayer_status' => ['required', 'in:on,off'],
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
