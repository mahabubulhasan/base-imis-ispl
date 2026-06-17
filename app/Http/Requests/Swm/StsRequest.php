<?php

namespace App\Http\Requests\Swm;

use App\Http\Requests\Concerns\MapsValidationAttributes;
use App\Services\Swm\StsService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StsRequest extends FormRequest
{
    use MapsValidationAttributes;

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
                    'ward_no' => [
                        'required',
                        'integer',
                        Rule::exists('pgsql.layer_info.wards', 'ward'),
                    ],
                    'road_id' => ['nullable', 'string', 'max:255'],
                    'road_name' => ['nullable', 'string', 'max:255'],
                    'latitude' => ['nullable', 'numeric', 'between:-90,90'],
                    'longitude' => ['nullable', 'numeric', 'between:-180,180'],
                    'operator_name' => ['required', 'string', 'max:255'],
                    'contact_number' => ['required', 'regex:/^[0-9]+$/'],
                    'capacity' => ['nullable', 'string', 'max:255'],
                    'area' => ['nullable', 'numeric', 'min:0'],
                    'source_wards' => ['nullable', 'array'],
                    'source_wards.*' => [
                        'integer',
                        Rule::exists('pgsql.layer_info.wards', 'ward'),
                    ],
                    'segregation_practiced' => ['sometimes', 'boolean'],
                    'waste_type_ids' => ['nullable', 'array'],
                    'waste_type_ids.*' => [
                        'integer',
                        Rule::exists('pgsql.swm.waste_types', 'id')->whereNull('deleted_at'),
                    ],
                    'destination_landfill_id' => [
                        'nullable',
                        'integer',
                        Rule::exists('pgsql.swm.landfills', 'id')->where(function ($query) {
                            return $query->whereNull('deleted_at');
                        }),
                    ],
                    'operational_status' => ['required', 'in:active,inactive'],
                ];
            default:
                return [];
        }
    }

    protected function prepareForValidation(): void
    {
        $dest = $this->input('destination_landfill_id');
        $ward = $this->input('ward_no');
        $lat = $this->input('latitude');
        $lng = $this->input('longitude');
        $area = $this->input('area');

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

        $this->merge([
            'segregation_practiced' => $this->boolean('segregation_practiced'),
            'destination_landfill_id' => ($dest === '' || $dest === null) ? null : (int) $dest,
            'ward_no' => ($ward === '' || $ward === null) ? null : (int) $ward,
            'latitude' => ($lat === '' || $lat === null) ? null : $lat,
            'longitude' => ($lng === '' || $lng === null) ? null : $lng,
            'area' => ($area === '' || $area === null) ? null : $area,
            'source_wards' => $sourceWards,
            'waste_type_ids' => $wasteTypeIds,
        ]);
    }

    /** @return array<string, string> */
    protected function validationAttributeLabels(): array
    {
        return app(StsService::class)->validationAttributeLabels();
    }
}
