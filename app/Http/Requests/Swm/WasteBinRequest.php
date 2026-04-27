<?php

namespace App\Http\Requests\Swm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WasteBinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'household_id' => [
                'required',
                'integer',
                Rule::exists('pgsql.building_info.households', 'id')->where(fn ($q) => $q->whereNull('deleted_at')),
            ],
            'bin' => ['nullable', 'string', 'max:255'],
            'number_of_waste_bins' => ['required', 'integer', 'min:1'],
            'total_capacity_kg' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
