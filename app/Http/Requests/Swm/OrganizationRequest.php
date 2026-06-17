<?php

namespace App\Http\Requests\Swm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
class OrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function organizationId(): ?int
    {
        $org = $this->route('organization');

        return is_object($org) ? (int) $org->id : ($org !== null ? (int) $org : null);
    }

    public function rules(): array
    {
        $id = $this->organizationId();
        $passwordRules = [
            'required_if:create_user,1',
            'nullable',
            Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised(),
            'confirmed',
        ];

        switch ($this->method()) {
            case 'GET':
            case 'DELETE':
                return [];
            case 'POST':
                $emailRules = [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('pgsql.swm.organizations', 'email')->where(function ($query) {
                        return $query->whereNull('deleted_at');
                    }),
                ];
                if ($this->boolean('create_user')) {
                    $emailRules[] = Rule::unique('pgsql.auth.users', 'email');
                }

                return [
                    'name' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique('pgsql.swm.organizations', 'name')->where(function ($query) {
                            return $query->whereNull('deleted_at');
                        }),
                    ],
                    'email' => $emailRules,
                    'address' => ['required', 'string', 'max:2000'],
                    'contact_person_name' => ['required', 'string', 'max:255'],
                    'contact_number' => ['required', 'regex:/^[0-9]+$/'],
                    'organization_type_id' => ['required', 'integer', Rule::exists('pgsql.swm.organization_types', 'id')->whereNull('deleted_at')],
                    'service_wards' => ['nullable', 'array'],
                    'service_wards.*' => [
                        'integer',
                        Rule::exists('pgsql.layer_info.wards', 'ward'),
                    ],
                    'remarks' => ['nullable', 'string', 'max:2000'],
                    'status' => 'required|boolean',
                    'password' => $passwordRules,
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'name' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique('pgsql.swm.organizations', 'name')
                            ->where(function ($query) {
                                return $query->whereNull('deleted_at');
                            })
                            ->ignore($id),
                    ],
                    'email' => [
                        'required',
                        'email',
                        'max:255',
                        Rule::unique('pgsql.swm.organizations', 'email')
                            ->where(function ($query) {
                                return $query->whereNull('deleted_at');
                            })
                            ->ignore($id),
                        Rule::unique('pgsql.auth.users', 'email')->ignore($id, 'swm_organization_id'),
                    ],
                    'address' => ['required', 'string', 'max:2000'],
                    'contact_person_name' => ['required', 'string', 'max:255'],
                    'contact_number' => ['required', 'regex:/^[0-9]+$/'],
                    'organization_type_id' => ['required', 'integer', Rule::exists('pgsql.swm.organization_types', 'id')->whereNull('deleted_at')],
                    'service_wards' => ['nullable', 'array'],
                    'service_wards.*' => [
                        'integer',
                        Rule::exists('pgsql.layer_info.wards', 'ward'),
                    ],
                    'remarks' => ['nullable', 'string', 'max:2000'],
                    'status' => 'required|boolean',
                    'password' => $passwordRules,
                ];
            default:
                return [];
        }
    }

    public function messages(): array
    {
        return [
            'name.required' => __('The organization name is required.'),
            'email.required' => __('The Email is required.'),
            'email.email' => __('Please enter a valid email address.'),
            'email.unique' => __('The email has already been taken.'),
            'address.required' => __('The Address is required.'),
            'contact_person_name.required' => __('The contact person name is required.'),
            'contact_number.required' => __('The contact number is required.'),
            'organization_type_id.required' => __('The organization type is required.'),
            'organization_type_id.exists' => __('Please select a valid organization type.'),
            'service_wards.array' => __('Service wards must be a list.'),
            'service_wards.*.integer' => __('Each service ward must be a valid ward number.'),
            'service_wards.*.exists' => __('One or more selected service wards are invalid.'),
            'status.required' => __('The Status is required.'),
            'password.required_if' => __('The Password is required when create user is on.'),
            'password.confirmed' => __('The Confirm Password does not match the Password.'),
            'password.uncompromised' => __('The given password has appeared in a data leak. Please choose a different password.'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $serviceWards = $this->input('service_wards');
        if (is_string($serviceWards)) {
            $serviceWards = $serviceWards === '' ? null : array_filter(array_map('trim', explode(',', $serviceWards)));
        }
        if (is_array($serviceWards)) {
            $serviceWards = array_values(array_filter($serviceWards, function ($value) {
                return $value !== '' && $value !== null;
            }));
            $serviceWards = array_map('intval', $serviceWards);
            if (empty($serviceWards)) {
                $serviceWards = null;
            }
        }

        $this->merge([
            'service_wards' => $serviceWards,
        ]);
    }
}
