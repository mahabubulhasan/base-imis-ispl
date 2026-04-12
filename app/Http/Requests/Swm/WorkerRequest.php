<?php

namespace App\Http\Requests\Swm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class WorkerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (Auth::user()?->swm_organization_id) {
            $this->merge([
                'organization_id' => Auth::user()->swm_organization_id,
            ]);
        }
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
                    'organization_id' => [
                        'required',
                        'integer',
                        Rule::exists('pgsql.swm.organizations', 'id')->where(function ($query) {
                            return $query->whereNull('deleted_at');
                        }),
                    ],
                    'work_type_id' => [
                        'required',
                        'integer',
                        Rule::exists('pgsql.swm.work_types', 'id')->where(function ($query) {
                            return $query->whereNull('deleted_at');
                        }),
                    ],
                    'name' => ['required', 'string', 'max:255'],
                    'mobile' => ['required', 'regex:/^[0-9]+$/'],
                    'email' => ['nullable', 'email', 'max:255'],
                ];
            default:
                return [];
        }
    }

    public function messages(): array
    {
        return [
            'organization_id.required' => __('The organization is required.'),
            'work_type_id.required' => __('The work type is required.'),
            'name.required' => __('The worker name is required.'),
            'mobile.required' => __('The mobile number is required.'),
            'mobile.regex' => __('The mobile number may only contain digits.'),
        ];
    }
}
