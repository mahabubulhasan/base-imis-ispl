<?php

namespace App\Http\Requests\Swm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PrimaryCollectionSiteRequest extends FormRequest
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
                    'customer_id' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique('pgsql.swm.primary_collection_sites', 'customer_id')->ignore(optional($this->route('primary_collection_site'))->id),
                    ],
                    'customer_name' => ['required', 'string', 'max:255'],
                    'contact_number' => ['required', 'regex:/^[0-9]+$/'],
                    'area_mohalla_name' => ['nullable', 'string', 'max:255'],
                    'bin' => [
                        'required',
                        'string',
                        Rule::exists('pgsql.building_info.buildings', 'bin')->where(function ($query) {
                            return $query->whereNull('deleted_at');
                        }),
                    ],
                    'tax_id' => ['nullable', 'string', 'max:255'],
                    'waste_charge' => ['nullable', 'numeric', 'min:0'],
                    'is_owner' => ['sometimes', 'boolean'],
                    'functional_use' => ['nullable', 'string', 'max:255'],
                    'is_lic' => ['sometimes', 'boolean'],
                    'lic_id' => [
                        'nullable',
                        'string',
                        'max:255',
                        Rule::requiredIf(fn () => $this->boolean('is_lic')),
                        Rule::exists('pgsql.swm.lics', 'lic_id')->where(function ($query) {
                            return $query->whereNull('deleted_at');
                        }),
                    ],
                    'number_of_family_members' => ['nullable', 'integer', 'min:0'],
                    'using_this_service_since' => ['nullable', 'date'],
                    'segregation_practiced' => ['sometimes', 'boolean'],
                    'waste_bin_provided' => ['sometimes', 'boolean'],
                    'daily_waste_volume' => ['nullable', 'numeric', 'min:0'],
                    'remarks' => ['nullable', 'string', 'max:2000'],
                    'survey_date' => ['nullable', 'date'],
                    'van_puller_id' => [
                        'nullable',
                        'integer',
                        Rule::exists('pgsql.swm.workers', 'id')->where(function ($query) {
                            return $query->whereNull('deleted_at');
                        }),
                    ],
                ];
            default:
                return [];
        }
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_owner' => $this->boolean('is_owner'),
            'is_lic' => $this->boolean('is_lic'),
            'segregation_practiced' => $this->boolean('segregation_practiced'),
            'waste_bin_provided' => $this->boolean('waste_bin_provided'),
            'van_puller_id' => $this->filled('van_puller_id') ? (int) $this->input('van_puller_id') : null,
            'lic_id' => $this->boolean('is_lic') ? $this->input('lic_id') : null,
        ]);
    }
}
