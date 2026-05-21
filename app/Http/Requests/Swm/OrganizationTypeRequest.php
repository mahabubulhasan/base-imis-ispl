<?php

namespace App\Http\Requests\Swm;

use App\Models\Swm\OrganizationType;
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
                    'code' => [
                        'nullable',
                        'string',
                        Rule::in(array_merge([''], array_keys(OrganizationType::CODE_OPTIONS))),
                        Rule::unique('pgsql.swm.organization_types', 'code')->whereNull('deleted_at'),
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
                    'code' => [
                        'nullable',
                        'string',
                        Rule::in(array_merge([''], array_keys(OrganizationType::CODE_OPTIONS))),
                        Rule::unique('pgsql.swm.organization_types', 'code')->whereNull('deleted_at')->ignore($id),
                        function ($attribute, $value, $fail) use ($id) {
                            if ($id === null) {
                                return;
                            }
                            $type = OrganizationType::find($id);
                            if ($type && in_array($type->code, OrganizationType::seededCodes(), true) && $value !== $type->code) {
                                $fail(__('The code cannot be changed for seeded organization types.'));
                            }
                        },
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
            'code.unique' => __('The organization type code has already been taken.'),
            'code.in' => __('Please select a valid organization type code.'),
        ];
    }
}
