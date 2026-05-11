<?php
// Last Modified: 2026-05-03
// Developed By: Streams Tech Ltd.
// Description: Validation rules for creating and updating drain network records.

namespace App\Http\Requests\UtilityInfo;

use App\Http\Requests\Request;

class DrainRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'diameter' => $this->cleanNumber($this->input('diameter')),
            'length' => $this->cleanNumber($this->input('length')),
        ]);
    }

    /**
     * Remove commas from number inputs.
     */
    private function cleanNumber($value)
    {
        return $value !== null ? str_replace(',', '', $value) : null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        switch ($this->method()) {
            case 'GET':
            case 'DELETE':
                return [];

            case 'POST':
                {
                    return [
                        'drain_code' => 'nullable|string|unique:utility_info.drains,code',
                        'road_code' => 'required|string',
                        'cover_type' => 'nullable',
                        'surface_type' => 'nullable',
                        'size' => 'required|numeric',
                        'length' => 'required|numeric',
                        'treatment_plant_id'  => 'nullable',
                    ];
                }
            case 'PUT':
            case 'PATCH':
                {
                    return [
                        'cover_type' => 'nullable',
                        'surface_type' => 'nullable',
                        'size' => 'required|numeric',
                        'length' => 'required|numeric',
                        'treatment_plant_id'  => 'nullable',
                    ];
                }
            default:break;
        }

        return [];
    }

     public function messages()
    {
        return [
            'road_code.required' => __('The Road Code is required.'),
            'size.required' => __('The Width (mm) is required.'),
            'size.numeric' => __('The Width (mm) must be a number.'),
            'name.regex' => __('The name field should contain only contain letters and spaces.'),
            'diameter.numeric' => __('The Diameter must be a number.'),
            'length.numeric' => __('The Length (m) must be a number.'),
            'length.required' => __('The Length (m) is required.'),
            ];
    }
}
