<?php

namespace App\Http\Requests\BuildingInfo;

use Illuminate\Foundation\Http\FormRequest;

class BuildingSurveyRequest extends FormRequest
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
            "temp_building_code" => 'unique:buildingInfo.building_surveys|required|string',
            "tax_code" => "required|string",
            "collected_date" => "required|date_format:Y-m-d"
        ];
    }

    /**
     * Get the error messages to display if validation fails.
     *
     * @return array
     */
    public function messages()
    {
        return [
            "temp_building_code.unique" => __('The Temporary Building Code is already registered.'),
            "temp_building_code.required" => __('The Temporary Building Code is required.'),
            "temp_building_code.string" => __('The Temporary Building Code should be string.'),
            "tax_code" => __('The tax code should be string.'),
            "tax_code.required" => __('The tax code is required.'),
            "collected_date.required" => __('Collected date is required.'),
            "collected_date.date_format" => __('Collected date should be in YYYY-MM-DD date format.'),
        ];
    }
}
