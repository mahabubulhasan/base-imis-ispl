<?php

namespace App\Http\Requests\UtilityInfo;

use App\Http\Requests\Request;
use App\Models\UtilityInfo\WaterSupplys;

class WaterSupplysRequest extends Request
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
                        'road_code' => 'required',
                        'project_name'=> 'required|string',
                        'type'=>'nullable|string',
                        'material_type'=>'nullable|string',
                        'diameter' => 'required|numeric',
                        'length' => 'required|numeric',
                       
                    ];
                }
            case 'PUT':
            case 'PATCH':
                {
                    return [
                        'project_name'=> 'required|string',
                        'type'=>'nullable|string',
                        'material_type'=>'nullable|string',
                        'diameter' => 'required|numeric',
                        'length' => 'required|numeric',
                    ];
                }
            default:break;
        }
    }
    
    public function messages()
    {
        return [
            'name.regex' => __('The name field should contain only contain letters and spaces.'),
            'diameter.numeric' => __('The Diameter must be a number.'),
            'length.numeric' => __('The Length (m) must be a number.'),
            'name.string' => __('This Project Name must be a string.'),
            'road_code.required' => __('The Road Code is required.'),
            'project_name.required' => __('The Project Name is required.'),
            'project_name.string' => __('The Project Name must be a string.'),
            'type.string' => __('The Type must be a string.'),
            'material_type.string' => __('The Material Type must be a string.'),
            'diameter.required' =>__( 'The Diameter (mm) is required.'),
            'length.required' => __('The Length (m) is required.'),
        ];

    }

}
