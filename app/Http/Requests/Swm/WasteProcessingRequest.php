<?php

namespace App\Http\Requests\Swm;

use App\Http\Requests\Concerns\MapsValidationAttributes;
use App\Services\Swm\WasteProcessingService;
use Illuminate\Foundation\Http\FormRequest;

class WasteProcessingRequest extends FormRequest
{
    use MapsValidationAttributes;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
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
            'entry_at' => ['required', 'date'],
            'report_date' => ['required', 'date'],
            'reporting_month' => ['required', 'date_format:Y-m-d'],
            'waste_processing_site_name' => ['nullable', 'string', 'max:255'],
            'waste_received_ton' => ['nullable', 'numeric', 'min:0'],
            'organic_waste_composted_ton' => ['nullable', 'numeric', 'min:0'],
            'inorganic_waste_recycled_ton' => ['nullable', 'numeric', 'min:0'],
            'waste_incinerated_ton' => ['nullable', 'numeric', 'min:0'],
            'waste_burned_open_air_ton' => ['nullable', 'numeric', 'min:0'],
            'residual_waste_landfilled_ton' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string'],
        ];
    }

    /** @return array<string, string> */
    protected function validationAttributeLabels(): array
    {
        return app(WasteProcessingService::class)->validationAttributeLabels();
    }
}
