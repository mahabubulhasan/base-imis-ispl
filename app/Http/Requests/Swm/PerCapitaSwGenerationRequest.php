<?php

namespace App\Http\Requests\Swm;

use Illuminate\Foundation\Http\FormRequest;

class PerCapitaSwGenerationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('Edit SW Per Capita Generation Setting') ?? false;
    }

    public function rules(): array
    {
        return [
            'per_capita_sw_generation_kg_per_day' => [
                'required',
                'numeric',
                'min:0',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],
        ];
    }
}
