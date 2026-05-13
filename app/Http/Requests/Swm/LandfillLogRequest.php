<?php

namespace App\Http\Requests\Swm;

use App\Models\Swm\LandfillLog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class LandfillLogRequest extends FormRequest
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

        foreach (['source_wards', 'source_sts_ids'] as $field) {
            $val = $this->input($field);
            if (is_string($val)) {
                $decoded = json_decode($val, true);
                if (is_array($decoded)) {
                    $this->merge([$field => $decoded]);
                } elseif (trim($val) === '') {
                    $this->merge([$field => []]);
                } else {
                    $parts = array_filter(array_map('trim', explode(',', $val)), fn ($v) => $v !== '');
                    $this->merge([$field => array_values($parts)]);
                }
            }
        }

        if ($this->input('waste_type_id') === '' || $this->input('waste_type_id') === null) {
            $this->merge(['waste_type_id' => null]);
        }

        if (is_array($this->input('source_wards'))) {
            $clean = array_values(array_unique(array_filter(
                array_map(static fn ($v) => is_scalar($v) ? trim((string) $v) : null, $this->input('source_wards')),
                static fn ($v) => $v !== null && $v !== ''
            )));
            $this->merge(['source_wards' => $clean]);
        }

        if (is_array($this->input('source_sts_ids'))) {
            $clean = array_values(array_unique(array_filter(
                array_map(static fn ($v) => is_scalar($v) ? (int) $v : null, $this->input('source_sts_ids')),
                static fn ($v) => $v !== null && $v > 0
            )));
            $this->merge(['source_sts_ids' => $clean]);
        }
    }

    public function rules(): array
    {
        $orgId = $this->input('organization_id');

        return [
            'organization_id' => [
                'required',
                'integer',
                Rule::exists('pgsql.swm.organizations', 'id')->where(fn ($q) => $q->whereNull('deleted_at')),
            ],
            'vehicle_id' => [
                'required',
                'integer',
                Rule::exists('pgsql.swm.vehicles', 'id')->where(function ($q) use ($orgId) {
                    return $q->whereNull('deleted_at')
                        ->when($orgId, fn ($qq) => $qq->where('organization_id', (int) $orgId));
                }),
            ],
            'vehicle_type_id' => [
                'nullable',
                'integer',
                Rule::exists('pgsql.swm.vehicle_types', 'id')->where(fn ($q) => $q->whereNull('deleted_at')),
            ],
            'vehicle_type_name' => ['nullable', 'string', 'max:255'],
            'driver_name' => ['nullable', 'string', 'max:255'],
            'landfill_id' => [
                'nullable',
                'integer',
                Rule::exists('pgsql.swm.landfills', 'id')->where(fn ($q) => $q->whereNull('deleted_at')),
            ],
            'landfill_name' => ['nullable', 'string', 'max:255'],
            'waste_type_id' => [
                'nullable',
                'integer',
                Rule::exists('pgsql.swm.waste_types', 'id')->where(fn ($q) => $q->whereNull('deleted_at')),
            ],
            'waste_type_name' => ['nullable', 'string', 'max:255'],
            'quantity_ton' => ['nullable', 'numeric', 'min:0'],
            'weighbridge_weight_ton' => ['nullable', 'numeric', 'min:0'],
            'source_sts_ids' => ['nullable', 'array'],
            'source_sts_ids.*' => [
                'nullable',
                'integer',
                Rule::exists('pgsql.swm.sts', 'id')->where(fn ($q) => $q->whereNull('deleted_at')),
            ],
            'source_wards' => ['nullable', 'array'],
            'source_wards.*' => [
                'nullable',
                'string',
                'max:50',
                Rule::exists('pgsql.layer_info.wards', 'ward'),
            ],
            'entry_at' => ['required', 'date'],
            'operation_date' => ['required', 'date'],
            'operation_status' => ['required', Rule::in([
                LandfillLog::STATUS_COMPLETED,
                LandfillLog::STATUS_PENDING,
            ])],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
