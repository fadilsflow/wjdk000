<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateThresholdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'device_id'          => ['required', 'integer', 'exists:devices,id'],
            'min_soil_moisture'  => ['required', 'numeric', 'min:0', 'max:100'],
            'max_temperature'    => ['required', 'numeric', 'min:0', 'max:100'],
            'min_air_humidity'   => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
