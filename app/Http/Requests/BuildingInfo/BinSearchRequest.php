<?php

namespace App\Http\Requests\BuildingInfo;

use Illuminate\Foundation\Http\FormRequest;

class BinSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'min:1', 'max:50'],
            'type' => ['required', 'string', 'in:building_bin,preconnected_bin'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }
}
