<?php

namespace App\Http\Requests\UtilityInfo;

use App\Http\Requests\Request;

class SewerLineRequest extends Request
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
            'length' => $this->cleanNumber($this->input('length')),
            'diameter' => $this->cleanNumber($this->input('diameter')),
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
                        'road_code' => 'required|string',
                        'location' => 'required|string',
                        'length' => 'required|numeric',
                        'diameter' => 'required|numeric',
                        'treatment_plant_id'  => 'nullable',
                    ];
                }
            case 'PUT':
            case 'PATCH':
                {
                    return [
                       'location' => 'required|string',
                        'length' => 'required|numeric',
                        'diameter' => 'required|numeric',
                        'treatment_plant_id'  => 'nullable',
                    ];
                }
            default:break;
        }
    }

     public function messages()
    {
        return [
            'name.regex' => __('The name field should contain only contain letters and spaces.'),
            'length.numeric' => __('The Length (m) must be a number.'),
            'location.string' => __('The Location must be a string.'),
            'diameter.numeric' => __('The Diameter must be a number.'),
            'length.required' =>  __('The Length(m) is required.'),
            'location.required' =>  __('The Location is required.'),
            'diameter.required' =>  __('The Diameter(mm) is required.')
            ];
    }
}
