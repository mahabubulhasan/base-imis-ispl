<?php

namespace App\Http\Requests\Swm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StsLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $wards = $this->input('source_wards');
        if (is_string($wards)) {
            $decoded = json_decode($wards, true);
            if (is_array($decoded)) {
                $this->merge(['source_wards' => $decoded]);
            } elseif (trim($wards) === '') {
                $this->merge(['source_wards' => []]);
            } else {
                $parts = array_filter(array_map('trim', explode(',', $wards)), fn ($v) => $v !== '');
                $this->merge(['source_wards' => array_values($parts)]);
            }
        }

        $wtIds = $this->input('waste_type_ids');
        if (is_string($wtIds)) {
            $decoded = json_decode($wtIds, true);
            if (is_array($decoded)) {
                $this->merge(['waste_type_ids' => $decoded]);
            } elseif (trim($wtIds) === '') {
                $this->merge(['waste_type_ids' => []]);
            }
        }

        if (is_array($this->input('waste_type_ids'))) {
            $cleanWt = array_values(array_unique(array_filter(
                array_map(static fn ($v) => is_scalar($v) ? (int) $v : null, $this->input('waste_type_ids')),
                static fn ($v) => $v !== null && $v > 0
            )));
            $this->merge(['waste_type_ids' => $cleanWt]);
        }

        if (is_array($this->input('source_wards'))) {
            $clean = array_values(array_unique(array_filter(
                array_map(static fn ($v) => is_scalar($v) ? trim((string) $v) : null, $this->input('source_wards')),
                static fn ($v) => $v !== null && $v !== ''
            )));
            $this->merge(['source_wards' => $clean]);
        }
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => [
                'required',
                'integer',
                Rule::exists('pgsql.swm.vehicles', 'id')->where(fn ($q) => $q->whereNull('deleted_at')),
            ],
            'vehicle_type_id' => [
                'nullable',
                'integer',
                Rule::exists('pgsql.swm.vehicle_types', 'id')->where(fn ($q) => $q->whereNull('deleted_at')),
            ],
            'vehicle_type_name' => ['nullable', 'string', 'max:255'],
            'driver_name' => ['nullable', 'string', 'max:255'],
            'sts_id' => [
                'nullable',
                'integer',
                Rule::exists('pgsql.swm.sts', 'id')->where(fn ($q) => $q->whereNull('deleted_at')),
            ],
            'sts_name' => ['nullable', 'string', 'max:255'],
            'waste_type_ids' => ['nullable', 'array'],
            'waste_type_ids.*' => [
                'integer',
                Rule::exists('pgsql.swm.waste_types', 'id')->where(fn ($q) => $q->whereNull('deleted_at')),
            ],
            'quantity_ton' => ['nullable', 'numeric', 'min:0'],
            'source_wards' => ['nullable', 'array'],
            'source_wards.*' => [
                'nullable',
                'string',
                'max:50',
                Rule::exists('pgsql.layer_info.wards', 'ward'),
            ],
            'entry_at' => ['required', 'date'],
            'operation_date' => ['required', 'date'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
