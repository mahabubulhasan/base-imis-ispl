<?php

namespace App\Http\Requests\Swm;

use Illuminate\Foundation\Http\FormRequest;

class LandfillRequest extends FormRequest
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
                    'reuse_practiced' => ['sometimes', 'boolean'],
                    'monthly_waste_for_composting' => ['nullable', 'string', 'max:2000'],
                    'treatment' => ['sometimes', 'boolean'],
                ];
            default:
                return [];
        }
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'segregation_practiced' => $this->boolean('segregation_practiced'),
            'reuse_practiced' => $this->boolean('reuse_practiced'),
            'treatment' => $this->boolean('treatment'),
        ]);
    }
}
