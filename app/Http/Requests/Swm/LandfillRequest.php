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
                    'segregation_practiced' => ['sometimes', 'boolean'],
                    'reuse_practiced' => ['sometimes', 'boolean'],
                    'waste_type_ids' => ['nullable', 'array'],
                    'waste_type_ids.*' => [
                        'integer',
                        Rule::exists('pgsql.swm.waste_types', 'id')->whereNull('deleted_at'),
                    ],
                    'monthly_waste_for_composting' => ['nullable', 'numeric', 'min:0'],
                    'treatment' => ['sometimes', 'boolean'],
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
        $composting = $this->input('monthly_waste_for_composting');

        $this->merge([
            'segregation_practiced' => $this->boolean('segregation_practiced'),
            'reuse_practiced' => $this->boolean('reuse_practiced'),
            'treatment' => $this->boolean('treatment'),
            'source_sts_ids' => $sourceStsIds,
            'source_wards' => $sourceWards,
            'waste_type_ids' => $wasteTypeIds,
            'capacity' => ($capacity === '' || $capacity === null) ? null : $capacity,
            'area' => ($area === '' || $area === null) ? null : $area,
            'monthly_waste_for_composting' => ($composting === '' || $composting === null) ? null : $composting,
        ]);
    }
}
