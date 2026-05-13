<?php

namespace App\Http\Requests\Swm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class WasteProcessingRequest extends FormRequest
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

        $month = $this->input('reporting_month');
        if (is_string($month) && trim($month) !== '') {
            $parsed = \DateTime::createFromFormat('Y-m', trim($month));
            if ($parsed !== false) {
                $this->merge([
                    'reporting_month' => $parsed->format('Y-m-01'),
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'organization_id' => [
                'required',
                'integer',
                Rule::exists('pgsql.swm.organizations', 'id')->where(fn ($q) => $q->whereNull('deleted_at')),
            ],
            'entry_at' => ['required', 'date'],
            'report_date' => ['required', 'date'],
            'reporting_month' => ['required', 'date_format:Y-m-d'],
            'waste_received_ton' => ['nullable', 'numeric', 'min:0'],
            'organic_waste_composted_ton' => ['nullable', 'numeric', 'min:0'],
            'inorganic_waste_recycled_ton' => ['nullable', 'numeric', 'min:0'],
            'waste_incinerated_ton' => ['nullable', 'numeric', 'min:0'],
            'waste_burned_open_air_ton' => ['nullable', 'numeric', 'min:0'],
            'residual_waste_landfilled_ton' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
