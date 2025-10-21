<?php

namespace App\Http\Requests\UtilityInfo;

use Illuminate\Foundation\Http\FormRequest;

class RoadLineRequest extends FormRequest
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
            'carrying_width' => $this->cleanNumber($this->input('carrying_width')),
            'right_of_way' => $this->cleanNumber($this->input('right_of_way')),
        ]);
    }

    /**
     * Helper method to remove commas from numbers.
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
        $rules = [];

        switch ($this->method()) {
            case 'GET':
            case 'DELETE':
                {
                    $rules = [];
                    break;
                }
            case 'POST':
                {
                    $rules = [
                        'name' => 'required|max:255',
                        'length' => 'required|numeric',
                        'carrying_width' => 'required|numeric',
                        'hierarchy' => 'nullable',
                        'surface_type' => 'nullable',
                        'right_of_way' => 'required|numeric',
                    ];
                    break;
                }
            case 'PUT':
            case 'PATCH':
                {
                    $rules = [
                        'name' => 'required|max:255',
                        'length' => 'required|numeric',
                        'carrying_width' => 'required|numeric',
                        'hierarchy' => 'nullable',
                        'surface_type' => 'nullable',
                        'right_of_way' => 'required|numeric',
                    ];
                    break;
                }
            default:
                break;
        }

        return $rules;
    }

    /**
     * Custom validation logic after base validation.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->right_of_way < $this->carrying_width) {
                $validator->errors()->add('right_of_way', __('The Right of Way (m) must be greater than or equal to the Carrying Width.'));
            }
        });
    }


    public function messages()
    {
        return [
            'name.required' => __('The road name is required.'),
            'length.required' => __('The road length (m) is required.'),
            'carrying_width.required' => __('The carrying width (m) is required.'),
            'right_of_way.required' => __('The right of way (m) is required.'),
            'name.regex' => __('The name field should contain only letters and spaces.'),
            'length.numeric' => __('The Road Length (m) must be a number.'),
            'carrying_width.numeric' => __('The Carrying Width of the Road (m) must be a number.'),
        ];
    }
}
