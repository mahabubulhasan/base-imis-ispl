<?php

namespace App\Http\Requests\BuildingInfo;

use Illuminate\Foundation\Http\FormRequest;

class LicSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'nullable', 'string', 'min:3', 'max:100'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:500'],
        ];
    }
}
