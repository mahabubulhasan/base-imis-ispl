<?php

namespace App\Http\Requests\BuildingInfo;

use App\Models\BuildingInfo\Household;
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
        $householdPk = is_object($household) ? $household->id : null;

        $wasteBinIdRules = ['nullable', 'integer'];
        if ($householdPk) {
            $wasteBinIdRules[] = Rule::exists('pgsql.swm.waste_bins', 'id')->where(function ($query) use ($householdPk) {
                $query->where('household_id', $householdPk)->whereNull('deleted_at');
            });
        }

        $rules = [
            'household_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pgsql.building_info.households', 'household_id')->ignore($householdPk),
            ],
            'household_owner_name' => ['required', 'string', 'max:255'],
            'father_or_husband_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in([Household::STATUS_ACTIVE, Household::STATUS_INACTIVE])],
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
            'road_no' => ['nullable', 'string', 'max:255'],
            'road_name' => ['required', 'string', 'max:255'],
            'holding_number' => ['required', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:255'],
            'waste_charge' => ['nullable', 'numeric', 'min:0'],
            'is_owner' => ['sometimes', 'boolean'],
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
        ];

        if ($this->boolean('is_owner') && $this->boolean('waste_bin_provided')) {
            $rules['waste_bins'] = ['required', 'array', 'min:1'];
            $rules['waste_bins.*.id'] = $wasteBinIdRules;
            $rules['waste_bins.*.waste_bin_type_id'] = [
                'required',
                'integer',
                Rule::exists('pgsql.swm.waste_bin_types', 'id')->where(fn ($q) => $q->whereNull('deleted_at')),
            ];
            $rules['waste_bins.*.total_capacity_kg'] = ['required', 'numeric', 'min:0.01'];
        } else {
            $rules['waste_bins'] = ['nullable', 'array'];
        }

        return $rules;
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

        if (! $this->boolean('is_owner') || ! $this->boolean('waste_bin_provided')) {
            return;
        }

        $bins = $this->input('waste_bins', []);
        if (! is_array($bins)) {
            $this->merge(['waste_bins' => []]);

            return;
        }

        $bins = array_values(array_filter($bins, function ($row) {
            if (! is_array($row)) {
                return false;
            }

            return ! empty($row['waste_bin_type_id']) || ! empty($row['total_capacity_kg']);
        }));

        $household = $this->route('household');
        foreach ($bins as &$row) {
            if (! is_array($row)) {
                continue;
            }
            if (isset($row['id']) && ($row['id'] === '' || $row['id'] === null)) {
                unset($row['id']);
            } elseif (! $household && array_key_exists('id', $row)) {
                unset($row['id']);
            }
        }
        unset($row);

        $this->merge(['waste_bins' => $bins]);
    }
}
