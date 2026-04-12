<?php

namespace App\Http\Requests\Swm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function vehicleTypeId(): ?int
    {
        $vt = $this->route('vehicle_type');

        return is_object($vt) ? (int) $vt->id : ($vt !== null ? (int) $vt : null);
    }

    public function rules(): array
    {
        $id = $this->vehicleTypeId();

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
                        Rule::unique('pgsql.swm.vehicle_types', 'name')->whereNull('deleted_at'),
                    ],
                ];
            case 'PUT':
            case 'PATCH':
                return [
                    'name' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique('pgsql.swm.vehicle_types', 'name')->whereNull('deleted_at')->ignore($id),
                    ],
                ];
            default:
                return [];
        }
    }

    public function messages(): array
    {
        return [
            'name.required' => __('The vehicle type name is required.'),
            'name.unique' => __('The vehicle type name has already been taken.'),
        ];
    }
}
