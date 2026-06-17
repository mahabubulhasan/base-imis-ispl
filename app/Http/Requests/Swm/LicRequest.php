<?php

namespace App\Http\Requests\Swm;

use App\Http\Requests\Concerns\MapsValidationAttributes;
use App\Services\Swm\LicService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LicRequest extends FormRequest
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
                    'lic_id' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique('pgsql.swm.lics', 'lic_id')->ignore(optional($this->route('lic'))->id),
                    ],
                    'representative_name' => ['required', 'string', 'max:255'],
                    'contact_no' => ['required', 'regex:/^[0-9]+$/'],
                    'number_of_hhs' => ['required', 'integer', 'min:0'],
                    'total_population' => ['required', 'integer', 'min:0'],
                ];
            default:
                return [];
        }
    }

    /** @return array<string, string> */
    protected function validationAttributeLabels(): array
    {
        return app(LicService::class)->validationAttributeLabels();
    }
}
