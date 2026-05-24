<?php

declare(strict_types=1);

namespace App\Http\Requests\Sprayer;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateSprayerStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:on,off'],
        ];
    }
}
