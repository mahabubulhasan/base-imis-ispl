<?php

namespace App\Http\Requests\Swm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrganizationTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function organizationTypeId(): ?int
    {
        $type = $this->route('organization_type');

        return is_object($type) ? (int) $type->id : ($type !== null ? (int) $type : null);
    }

    public function rules(): array
    {
        $id = $this->organizationTypeId();

        switch ($this->method()) {
            case 'GET':
            case 'DELETE':
                return [];
            case 'POST':
                return [
                    'name' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique('pgsql.swm.organization_types', 'name')->whereNull('deleted_at'),
                    ],
                    'description' => [
                        'nullable',
                        'string',
                        'max:1000',
                    ],
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'name' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique('pgsql.swm.organization_types', 'name')->whereNull('deleted_at')->ignore($id),
                    ],
                    'description' => [
                        'nullable',
                        'string',
                        'max:1000',
                    ],
                ];
            default:
                return [];
        }
    }

    public function messages(): array
    {
        return [
            'name.required' => __('The organization type name is required.'),
            'name.unique' => __('The organization type name has already been taken.'),
        ];
    }
}
