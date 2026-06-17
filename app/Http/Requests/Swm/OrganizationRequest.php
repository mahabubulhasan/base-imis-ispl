<?php

namespace App\Http\Requests\Swm;

use App\Http\Requests\Concerns\MapsValidationAttributes;
use App\Services\Swm\OrganizationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class OrganizationRequest extends FormRequest
{
    use MapsValidationAttributes;

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

                return $this->withPasswordRules([
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
                ]);
            case 'PUT':
            case 'PATCH':
                return $this->withPasswordRules([
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
                ]);
            default:
                return [];
        }
    }

    public function messages(): array
    {
        return [
            'email.unique' => __('The email has already been taken.'),
            'password.required' => __('The Password is required when create user is enabled.'),
            'password.confirmed' => __('The Confirm Password does not match the Password when create user is enabled.'),
            'password.uncompromised' => __('The given password has appeared in a data leak. Please choose a different password.'),
            'service_wards.array' => __('Service wards must be a list.'),
            'service_wards.*.integer' => __('Each service ward must be a valid ward number.'),
            'service_wards.*.exists' => __('One or more selected service wards are invalid.'),
        ];
    }

    /** @return array<string, string> */
    protected function validationAttributeLabels(): array
    {
        return app(OrganizationService::class)->validationAttributeLabels();
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

        if (! $this->boolean('create_user')) {
            $this->merge([
                'password' => null,
                'password_confirmation' => null,
            ]);
        }
    }

    /** @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    protected function withPasswordRules(array $rules): array
    {
        if ($this->boolean('create_user')) {
            $rules['password'] = [
                'required',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
                'confirmed',
            ];
        }

        return $rules;
    }
}
