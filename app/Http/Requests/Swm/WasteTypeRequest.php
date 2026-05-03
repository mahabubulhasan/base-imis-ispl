<?php

namespace App\Http\Requests\Swm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WasteTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function wasteTypeId(): ?int
    {
        $wt = $this->route('waste_type');

        return is_object($wt) ? (int) $wt->id : ($wt !== null ? (int) $wt : null);
    }

    public function rules(): array
    {
        $id = $this->wasteTypeId();

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
                        Rule::unique('pgsql.swm.waste_types', 'name')->whereNull('deleted_at'),
                    ],
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'name' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique('pgsql.swm.waste_types', 'name')->whereNull('deleted_at')->ignore($id),
                    ],
                ];
            default:
                return [];
        }
    }

    public function messages(): array
    {
        return [
            'name.required' => __('The waste type name is required.'),
            'name.unique' => __('The waste type name has already been taken.'),
        ];
    }
}
