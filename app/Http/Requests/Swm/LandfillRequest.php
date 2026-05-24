<?php

namespace App\Http\Requests\Swm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LandfillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
                    'name' => ['required', 'string', 'max:255'],
                    'location' => ['nullable', 'string', 'max:2000'],
                    'operator_name' => ['required', 'string', 'max:255'],
                    'contact_number' => ['required', 'regex:/^[0-9]+$/'],
                    'capacity' => ['nullable', 'numeric', 'min:0'],
                    'area' => ['nullable', 'numeric', 'min:0'],
                    'landfill_type_id' => [
                        'nullable',
                        'integer',
                        Rule::exists('pgsql.swm.landfill_types', 'id')->whereNull('deleted_at'),
                    ],
                    'source_sts_ids' => ['nullable', 'array'],
                    'source_sts_ids.*' => [
                        'integer',
                        Rule::exists('pgsql.swm.sts', 'id')->whereNull('deleted_at'),
                    ],
                    'source_wards' => ['nullable', 'array'],
                    'source_wards.*' => [
                        'integer',
                        Rule::exists('pgsql.layer_info.wards', 'ward'),
                    ],
                    'segregation_practiced' => ['nullable', 'boolean'],
                    'waste_type_ids' => ['nullable', 'array'],
                    'waste_type_ids.*' => [
                        'integer',
                        Rule::exists('pgsql.swm.waste_types', 'id')->whereNull('deleted_at'),
                    ],
                    'weighbridge_facility_available' => ['nullable', 'boolean'],
                    'boundary_wall_available' => ['nullable', 'boolean'],
                    'lighting_arrangement_available' => ['nullable', 'boolean'],
                    'manpower_deployed' => ['nullable', 'integer', 'min:0'],
                    'adequate_covering_arrangement_available' => ['nullable', 'boolean'],
                    'gas_control_system_available' => ['nullable', 'boolean'],
                    'leachate_collection_system_available' => ['nullable', 'boolean'],
                    'operational_status' => ['required', 'in:active,inactive'],
                ];
            default:
                return [];
        }
    }

    protected function prepareForValidation(): void
    {
        $sourceStsIds = $this->input('source_sts_ids');
        if (is_string($sourceStsIds)) {
            $sourceStsIds = $sourceStsIds === '' ? null : array_filter(array_map('trim', explode(',', $sourceStsIds)));
        }
        if (is_array($sourceStsIds)) {
            $sourceStsIds = array_values(array_filter($sourceStsIds, function ($v) {
                return $v !== '' && $v !== null;
            }));
            $sourceStsIds = array_map('intval', $sourceStsIds);
            if (empty($sourceStsIds)) {
                $sourceStsIds = null;
            }
        }

        $sourceWards = $this->input('source_wards');
        if (is_string($sourceWards)) {
            $sourceWards = $sourceWards === '' ? null : array_filter(array_map('trim', explode(',', $sourceWards)));
        }
        if (is_array($sourceWards)) {
            $sourceWards = array_values(array_filter($sourceWards, function ($v) {
                return $v !== '' && $v !== null;
            }));
            $sourceWards = array_map('intval', $sourceWards);
            if (empty($sourceWards)) {
                $sourceWards = null;
            }
        }

        $wasteTypeIds = $this->input('waste_type_ids');
        if (is_string($wasteTypeIds)) {
            $wasteTypeIds = $wasteTypeIds === '' ? null : array_filter(array_map('trim', explode(',', $wasteTypeIds)));
        }
        if (is_array($wasteTypeIds)) {
            $wasteTypeIds = array_values(array_filter($wasteTypeIds, function ($v) {
                return $v !== '' && $v !== null;
            }));
            $wasteTypeIds = array_map('intval', $wasteTypeIds);
            if (empty($wasteTypeIds)) {
                $wasteTypeIds = null;
            }
        }

        $capacity = $this->input('capacity');
        $area = $this->input('area');
        $landfillTypeId = $this->input('landfill_type_id');
        $manpowerDeployed = $this->input('manpower_deployed');

        $this->merge([
            'segregation_practiced' => $this->nullableBoolean('segregation_practiced'),
            'weighbridge_facility_available' => $this->nullableBoolean('weighbridge_facility_available'),
            'boundary_wall_available' => $this->nullableBoolean('boundary_wall_available'),
            'lighting_arrangement_available' => $this->nullableBoolean('lighting_arrangement_available'),
            'adequate_covering_arrangement_available' => $this->nullableBoolean('adequate_covering_arrangement_available'),
            'gas_control_system_available' => $this->nullableBoolean('gas_control_system_available'),
            'leachate_collection_system_available' => $this->nullableBoolean('leachate_collection_system_available'),
            'source_sts_ids' => $sourceStsIds,
            'source_wards' => $sourceWards,
            'waste_type_ids' => $wasteTypeIds,
            'capacity' => ($capacity === '' || $capacity === null) ? null : $capacity,
            'area' => ($area === '' || $area === null) ? null : $area,
            'landfill_type_id' => ($landfillTypeId === '' || $landfillTypeId === null) ? null : (int) $landfillTypeId,
            'manpower_deployed' => ($manpowerDeployed === '' || $manpowerDeployed === null) ? null : (int) $manpowerDeployed,
        ]);
    }

    protected function nullableBoolean(string $key): ?bool
    {
        if (! $this->has($key)) {
            return null;
        }

        $value = $this->input($key);
        if ($value === '' || $value === null) {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}
