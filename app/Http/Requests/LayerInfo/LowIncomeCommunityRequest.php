<?php

namespace App\Http\Requests\LayerInfo;

use Illuminate\Foundation\Http\FormRequest;

class LowIncomeCommunityRequest extends FormRequest
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
    public function rules()
    {
        $rules = ($this->isMethod('POST') ? $this->store() : $this->update());
        return $rules;
    }
    public function messages()
{
    return [
        'community_name.required' => __('The Community Name is required.'),
        'lic_status.required' => __('The LIC Status is required.'),
        'lic_status.boolean' => __('The LIC Status must be Yes or No.'),
        'area_decima.numeric' => __('The Area (Decima) must be a number.'),
        'area_decima.min' => __('The Area (Decima) must be at least 0.'),
        'representative_name.max' => __('The Representative\'s Name may not be greater than 255 characters.'),
        'representative_contact_no.max' => __('The Representative\'s Contact No. may not be greater than 50 characters.'),
        'no_of_buildings.required' => __('The No. of Buildings is required.'),
        'no_of_buildings.integer' => __('The No. of Buildings must be an integer.'),
        'no_of_buildings.min' => __('The No. of Buildings must be at least 0.'),

        'population_total.required' => __('The Population is required.'),
        'population_total.integer' => __('The Population must be an integer.'),
        'population_total.min' => __('The Population must be at least 0.'),

        'number_of_households.required' => __('The No. of Households is required.'),
        'number_of_households.integer' => __('The No. of Households must be an integer.'),
        'number_of_households.min' => __('The No. of Households must be at least 0.'),

        'population_male.integer' => __('The Male Population must be an integer.'),
        'population_male.min' => __('The Male Population must be at least 0.'),

        'population_female.integer' => __('The Female Population must be an integer.'),
        'population_female.min' => __('The Female Population must be at least 0.'),

        'population_others.integer' => __('The Other Population must be an integer.'),
        'population_others.min' => __('The Other Population must be at least 0.'),

        'water_connection_status.required' => __('The Water Connection Status is required.'),
        'water_connection_status.boolean' => __('The Water Connection Status must be Yes or No.'),
        'no_of_wate_points.required_if' => __('No. of Water Points is required when Water Connection Status is Yes.'),
        'no_of_wate_points.integer' => __('No. of Water Points must be an integer.'),
        'no_of_wate_points.min' => __('No. of Water Points must be at least 0.'),
        'sanitation_status.required' => __('The Sanitation Status is required.'),
        'sanitation_status.boolean' => __('The Sanitation Status must be Yes or No.'),

        'no_of_septic_tank.integer' => __('The No. of Septic Tanks must be an integer.'),
        'no_of_septic_tank.min' => __('The No. of Septic Tanks must be at least 0.'),

        'no_of_holding_tank.integer' => __('The No. of Holding Tanks must be an integer.'),
        'no_of_holding_tank.min' => __('The No. of Holding Tanks must be at least 0.'),

        'no_of_pit.integer' => __('The No. of Pits must be an integer.'),
        'no_of_pit.min' => __('The No. of Pits must be at least 0.'),

        'no_of_sewer_connection.integer' => __('The No. of Sewer Connections must be an integer.'),
        'no_of_sewer_connection.min' => __('The No. of Sewer Connections must be at least 0.'),

        'no_of_community_toilets.integer' => __('The No. of Community Toilets must be an integer.'),
        'no_of_community_toilets.min' => __('The No. of Community Toilets must be at least 0.'),
        'no_of_community_toilets.required_if' => __('No. of Community Toilets is required when Sanitation Status is Yes.'),
        'remarks.max' => __('Remarks may not be greater than 1000 characters.'),

        'geom.required' => __('The Area is required.'),
    ];
}

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function store()
{
    return [
            'community_name' => 'required',
            'lic_status' => 'required|boolean',
            'area_decima' => 'nullable|numeric|min:0',
            'representative_name' => 'nullable|string|max:255',
            'representative_contact_no' => 'nullable|string|max:50',
            'no_of_buildings' => 'required|integer|min:0',
            'population_total' => 'required|integer|min:0',
            'number_of_households' => 'required|integer|min:0',
            'population_male' => 'nullable|integer|min:0',
            'population_female' => 'nullable|integer|min:0',
            'population_others' => 'nullable|integer|min:0',
            'water_connection_status' => 'required|boolean',
            'no_of_wate_points' => 'nullable|required_if:water_connection_status,1,true|integer|min:0',
            'sanitation_status' => 'required|boolean',
            'no_of_septic_tank' => 'nullable|integer|min:0',
            'no_of_holding_tank' => 'nullable|integer|min:0',
            'no_of_pit' => 'nullable|integer|min:0',
            'no_of_sewer_connection' => 'nullable|integer|min:0',
            'no_of_community_toilets' => 'nullable|required_if:sanitation_status,1,true|integer|min:0',
            'remarks' => 'nullable|string|max:1000',
            'geom' => 'required',

    ];
}


    public function update()
    {
        return [
            'community_name' => 'required',
            'lic_status' => 'required|boolean',
            'area_decima' => 'nullable|numeric|min:0',
            'representative_name' => 'nullable|string|max:255',
            'representative_contact_no' => 'nullable|string|max:50',
            'no_of_buildings' => 'required|integer|min:0',
            'population_total' => 'required|integer|min:0',
            'number_of_households' => 'required|integer|min:0',
            'population_male' => 'nullable|integer|min:0',
            'population_female' => 'nullable|integer|min:0',
            'population_others' => 'nullable|integer|min:0',
            'water_connection_status' => 'required|boolean',
            'no_of_wate_points' => 'nullable|required_if:water_connection_status,1,true|integer|min:0',
            'sanitation_status' => 'required|boolean',
            'no_of_septic_tank' => 'nullable|integer|min:0',
            'no_of_holding_tank' => 'nullable|integer|min:0',
            'no_of_pit' => 'nullable|integer|min:0',
            'no_of_sewer_connection' => 'nullable|integer|min:0',
            'no_of_community_toilets' => 'nullable|required_if:sanitation_status,1,true|integer|min:0',
            'remarks' => 'nullable|string|max:1000',
            'geom' => 'required',

        ];
    }

}
// Last Modified Date: 10-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)    for .php files
