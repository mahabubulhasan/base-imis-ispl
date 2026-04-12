<?php

namespace App\Http\Requests\Swm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        switch ($this->method()) {
            case 'GET':
            case 'DELETE':
                return [];
            case 'POST':
            case 'PUT':
            case 'PATCH':
                return [
                    'name' => ['required', 'string', 'max:255'],
                    'location' => ['nullable', 'string', 'max:2000'],
                    'operator_name' => ['required', 'string', 'max:255'],
                    'contact_number' => ['required', 'regex:/^[0-9]+$/'],
                    'capacity' => ['nullable', 'string', 'max:255'],
                    'segregation_practiced' => ['sometimes', 'boolean'],
                    'destination_landfill_id' => [
                        'nullable',
                        'integer',
                        Rule::exists('pgsql.swm.landfills', 'id')->where(function ($query) {
                            return $query->whereNull('deleted_at');
                        }),
                    ],
                ];
            default:
                return [];
        }
    }

    protected function prepareForValidation(): void
    {
        $dest = $this->input('destination_landfill_id');
        $this->merge([
            'segregation_practiced' => $this->boolean('segregation_practiced'),
            'destination_landfill_id' => ($dest === '' || $dest === null) ? null : (int) $dest,
        ]);
    }
}
