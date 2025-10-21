<?php

namespace App\Http\Requests\UtilityInfo;

use Illuminate\Foundation\Http\FormRequest;

class CreateSewerConnectionRequest extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
          "bin" => 'unique:buildingInfo.building_surveys,temp_building_code|required|string',
            "sewer_code" => "required|string",
            
        ];
    }

    public function messages()
    {
        return [
            "bin.unique" => __('The bin is already registered.'),
            "bin.required" => __('The bin is required.'),
            "bin.string" => __('The bin should be string.'),    
            "sewer_code" => __('The sewer code should be string.'),
            "sewer_code.required" => __('The sewer code is required.'),
          
        ];
    }
}
