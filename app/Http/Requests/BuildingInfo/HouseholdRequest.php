<?php

namespace App\Http\Requests\BuildingInfo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HouseholdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $household = $this->route('household');
        $ignoreId = is_object($household) ? $household->id : null;

        return [
            'household_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pgsql.building_info.households', 'household_id')->ignore($ignoreId),
            ],
            'household_owner_name' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'regex:/^[0-9]+$/'],
            'area_mohalla_name' => ['nullable', 'string', 'max:255'],
            'sub_location' => ['nullable', 'string', 'max:255'],
            'bin' => [
                'nullable',
                'string',
                Rule::exists('pgsql.building_info.buildings', 'bin')->where(function ($query) {
                    return $query->whereNull('deleted_at');
                }),
            ],
            'ward' => ['required', 'integer', 'min:1'],
            'road_no_name' => ['required', 'string', 'max:255'],
            'holding_number' => ['required', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:255'],
            'waste_charge' => ['nullable', 'numeric', 'min:0'],
            'is_owner' => ['sometimes', 'boolean'],
            'functional_use' => ['nullable', 'string', 'max:255'],
            'is_lic' => ['sometimes', 'boolean'],
            'lic_id' => [
                'nullable',
                'integer',
                Rule::requiredIf(fn () => $this->boolean('is_lic')),
                Rule::exists('pgsql.layer_info.low_income_communities', 'id')->where(function ($query) {
                    return $query->whereNull('deleted_at');
                }),
            ],
            'number_of_family_members' => ['nullable', 'integer', 'min:0'],
            'using_this_service_since' => ['nullable', 'date'],
            'segregation_practiced' => ['sometimes', 'boolean'],
            'waste_bin_provided' => ['sometimes', 'boolean'],
            'daily_waste_volume' => ['nullable', 'numeric', 'min:0'],
            'survey_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'van_puller_id' => [
                'nullable',
                'integer',
                Rule::exists('pgsql.swm.workers', 'id')->where(function ($query) {
                    return $query->whereNull('deleted_at');
                }),
            ],
            'number_of_waste_bins' => ['nullable', 'integer', 'min:1', Rule::requiredIf(fn () => $this->boolean('is_owner') && $this->boolean('waste_bin_provided'))],
            'total_capacity_kg' => ['nullable', 'numeric', 'min:0.01', Rule::requiredIf(fn () => $this->boolean('is_owner') && $this->boolean('waste_bin_provided'))],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_owner' => $this->boolean('is_owner'),
            'is_lic' => $this->boolean('is_lic'),
            'segregation_practiced' => $this->boolean('segregation_practiced'),
            'waste_bin_provided' => $this->boolean('waste_bin_provided'),
            'van_puller_id' => $this->filled('van_puller_id') ? (int) $this->input('van_puller_id') : null,
            'lic_id' => $this->boolean('is_lic') && $this->filled('lic_id') ? (int) $this->input('lic_id') : null,
        ]);
    }
}
