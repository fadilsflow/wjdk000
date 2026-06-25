<?php

declare(strict_types=1);

namespace App\Http\Requests\Sprayer;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateSprayerModeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'mode' => ['required', 'in:manual,automatic'],
        ];
    }
}
