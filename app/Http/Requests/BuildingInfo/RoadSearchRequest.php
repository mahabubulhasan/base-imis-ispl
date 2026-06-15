<?php

namespace App\Http\Requests\BuildingInfo;

use Illuminate\Foundation\Http\FormRequest;

class RoadSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ward' => ['required', 'string', 'max:10'],
            'q' => ['sometimes', 'nullable', 'string', 'min:1', 'max:100'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:200'],
        ];
    }
}
