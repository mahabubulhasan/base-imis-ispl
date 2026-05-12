<?php

namespace App\Http\Requests\Swm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LandfillTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function landfillTypeId(): ?int
    {
        $landfillType = $this->route('landfill_type');

        return is_object($landfillType) ? (int) $landfillType->id : ($landfillType !== null ? (int) $landfillType : null);
    }

    public function rules(): array
    {
        $id = $this->landfillTypeId();

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
                        Rule::unique('pgsql.swm.landfill_types', 'name')->whereNull('deleted_at'),
                    ],
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'name' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique('pgsql.swm.landfill_types', 'name')->whereNull('deleted_at')->ignore($id),
                    ],
                ];
            default:
                return [];
        }
    }

    public function messages(): array
    {
        return [
            'name.required' => __('The landfill type name is required.'),
            'name.unique' => __('The landfill type name has already been taken.'),
        ];
    }
}
