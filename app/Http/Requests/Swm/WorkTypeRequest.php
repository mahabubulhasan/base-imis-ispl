<?php

namespace App\Http\Requests\Swm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WorkTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function workTypeId(): ?int
    {
        $wt = $this->route('work_type');

        return is_object($wt) ? (int) $wt->id : ($wt !== null ? (int) $wt : null);
    }

    public function rules(): array
    {
        $id = $this->workTypeId();

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
                        Rule::unique('pgsql.swm.work_types', 'name')->whereNull('deleted_at'),
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
                        Rule::unique('pgsql.swm.work_types', 'name')->whereNull('deleted_at')->ignore($id),
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
            'name.required' => __('The work type name is required.'),
            'name.unique' => __('The work type name has already been taken.'),
        ];
    }
}
