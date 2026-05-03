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
        $workerId = (int) $this->route('worker');

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
                    'age' => ['nullable', 'integer', 'between:0,120'],
                    'gender' => ['nullable', Rule::in(['male', 'female', 'others'])],
                    'service_area' => ['nullable', 'string', 'max:255'],
                    'employment_type' => ['nullable', Rule::in(['permanent', 'daily', 'contract'])],
                    'status' => ['nullable', Rule::in(['active', 'inactive'])],
                    'department' => ['nullable', 'string', 'max:255'],
                    'supervisor_name' => ['nullable', 'string', 'max:255'],
                    'total_work_experience_years' => ['nullable', 'numeric', 'between:0,99.99'],
                    'organization_work_experience_years' => ['nullable', 'numeric', 'between:0,99.99'],
                    'education_level' => ['nullable', Rule::in(['primary', 'secondary', 'below_ssc', 'ssc', 'hsc', 'bachelor', 'master', 'others'])],
                    'education_level_other' => ['nullable', 'string', 'max:255', 'required_if:education_level,others'],
                    'employee_id' => [
                        'nullable',
                        'string',
                        'max:255',
                        Rule::unique('pgsql.swm.workers', 'employee_id')
                            ->ignore($workerId)
                            ->where(function ($query) {
                                return $query->whereNull('deleted_at');
                            }),
                    ],
                    'national_id_no' => ['nullable', 'string', 'max:255'],
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
            'employee_id.unique' => __('The employee ID must be unique.'),
            'employment_type.in' => __('The employment type must be permanent, daily, or contract.'),
            'education_level_other.required_if' => __('Please specify education details when selecting others.'),
        ];
    }
}
