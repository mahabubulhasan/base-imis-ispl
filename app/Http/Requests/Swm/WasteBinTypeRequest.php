<?php

namespace App\Http\Requests\Swm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WasteBinTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function wasteBinTypeId(): ?int
    {
        $wbt = $this->route('waste_bin_type');

        return is_object($wbt) ? (int) $wbt->id : ($wbt !== null ? (int) $wbt : null);
    }

    public function rules(): array
    {
        $id = $this->wasteBinTypeId();

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
                        Rule::unique('pgsql.swm.waste_bin_types', 'name')->whereNull('deleted_at'),
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
                        Rule::unique('pgsql.swm.waste_bin_types', 'name')->whereNull('deleted_at')->ignore($id),
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
            'name.required' => __('The waste bin type name is required.'),
            'name.unique' => __('The waste bin type name has already been taken.'),
        ];
    }
}
