<?php

namespace App\Http\Requests\Swm;

use App\Models\Swm\WasteBinType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WasteBinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'household_id' => [
                'required',
                'integer',
                Rule::exists('pgsql.building_info.households', 'id')->where(fn ($q) => $q->whereNull('deleted_at')),
            ],
            'waste_bin_type_id' => [
                'required',
                'integer',
                Rule::exists('pgsql.swm.waste_bin_types', 'id')->where(fn ($q) => $q->whereNull('deleted_at')),
            ],
            'type_other_detail' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn () => $this->isOthersSpecifyType()),
            ],
            'placed_at_buildings' => ['sometimes', 'boolean'],
            'sub_location' => ['nullable', 'string', 'max:255'],
            'ward_no' => [
                'nullable',
                'integer',
                Rule::exists('pgsql.layer_info.wards', 'ward'),
            ],
            'road_no' => ['nullable', 'string', 'max:255'],
            'road_name' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'bin' => ['nullable', 'string', 'max:255'],
            'total_capacity_kg' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $placed = $this->boolean('placed_at_buildings');
        $this->merge([
            'placed_at_buildings' => $placed,
            ...(! $placed ? ['bin' => null] : []),
        ]);
    }

    private function isOthersSpecifyType(): bool
    {
        $id = $this->input('waste_bin_type_id');

        if (! $id) {
            return false;
        }

        return WasteBinType::query()
            ->where('id', $id)
            ->where('name', WasteBinType::OTHERS_SPECIFY_NAME)
            ->whereNull('deleted_at')
            ->exists();
    }
}
