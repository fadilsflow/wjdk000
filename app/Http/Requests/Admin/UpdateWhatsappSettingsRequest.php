<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateWhatsappSettingsRequest extends FormRequest
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
            'recipient_phone' => ['required', 'string', 'max:20'],
            'critical_condition_template' => ['required', 'string', 'max:2000'],
            'spray_start_template' => ['required', 'string', 'max:2000'],
            'spray_stop_template' => ['required', 'string', 'max:2000'],
            'rain_detected_template' => ['required', 'string', 'max:2000'],
        ];
    }
}
