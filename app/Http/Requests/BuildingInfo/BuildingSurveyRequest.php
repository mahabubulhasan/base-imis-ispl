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
            "collected_date" => "required|date_format:Y-m-d",
            "main_building" => "nullable|boolean",
            "building_associated_to" => "required_if:main_building,0",
            "ward" => "required",
            "road_code" => "required",
            "house_number" => "nullable|string|max:255",
            "house_locality" => "nullable|string",
            "structure_type_id" => "required|integer",
            "surveyed_date" => "nullable|date",
            "construction_year" => "required|date|before_or_equal:today",
            "floor_count" => "required|numeric|min:0.1",
            "functional_use_id" => "required|integer",
            "use_category_id" => "required_with:functional_use_id|integer",
            "office_business_name" => "nullable|string|max:255",
            "household_served" => "nullable|integer|min:0",
            "population_served" => "nullable|integer|min:0",
            "male_population" => "nullable|integer|min:0",
            "female_population" => "nullable|integer|min:0",
            "other_population" => "nullable|integer|min:0",
            "diff_abled_male_pop" => "nullable|integer|min:0|lte:male_population",
            "diff_abled_female_pop" => "nullable|integer|min:0|lte:female_population",
            "diff_abled_others_pop" => "nullable|integer|min:0|lte:other_population",
            "low_income_hh" => "nullable|boolean",
            "lic_status" => "nullable|boolean",
            "lic_id" => "required_if:lic_status,1",
            "water_source_id" => "required|integer",
            "water_customer_id" => "nullable|string|max:255",
            "watersupply_pipe_code" => "required_if:water_source_id,1",
            "well_presence_status" => "nullable|boolean",
            "distance_from_well" => "nullable|integer|min:0",
            "swm_customer_id" => "nullable|string|max:255",
            "toilet_status" => "required|boolean",
            "toilet_count" => "required_if:toilet_status,1|nullable|integer|min:1",
            "household_with_private_toilet" => "nullable|integer|min:0|lte:household_served",
            "population_with_private_toilet" => "nullable|integer|min:0|lte:population_served",
            "sanitation_system_id" => "required_if:toilet_status,1",
            "defecation_place" => "required_if:toilet_status,0",
            "ctpt_name" => "required_if:defecation_place,9",
            "build_contain" => "required_if:sanitation_system_id,11",
            "desludging_vehicle_accessible" => "nullable|boolean",
            "sewer_code" => "nullable|string|max:255",
            "drain_code" => "nullable|string|max:255",
            "house_image" => "nullable|image|mimes:jpeg,jpg|max:5120",
            "kml" => "required|file",
            "payload_json" => "nullable|array",
            "payload_json.*" => "nullable",
            "payload_json.main_building" => "nullable|boolean",
            "payload_json.building_associated_to" => "required_if:payload_json.main_building,0|nullable|string|max:255",
            "payload_json.house_locality" => "nullable|string",
            "payload_json.structure_type_id" => "required|integer",
            "payload_json.surveyed_date" => "nullable|date",
            "payload_json.construction_year" => "required|date|before_or_equal:today",
            "payload_json.floor_count" => "required|numeric|min:0.1",
            "payload_json.office_business_name" => "nullable|string|max:255",
            "payload_json.household_served" => "nullable|integer|min:0",
            "payload_json.population_served" => "nullable|integer|min:0",
            "payload_json.male_population" => "nullable|integer|min:0",
            "payload_json.female_population" => "nullable|integer|min:0",
            "payload_json.other_population" => "nullable|integer|min:0",
            "payload_json.diff_abled_male_pop" => "nullable|integer|min:0|lte:payload_json.male_population",
            "payload_json.diff_abled_female_pop" => "nullable|integer|min:0|lte:payload_json.female_population",
            "payload_json.diff_abled_others_pop" => "nullable|integer|min:0|lte:payload_json.other_population",
            "payload_json.low_income_hh" => "nullable|boolean",
            "payload_json.lic_status" => "nullable|boolean",
            "payload_json.lic_id" => "required_if:payload_json.lic_status,1|nullable|integer",
            "payload_json.water_customer_id" => "nullable|string|max:255",
            "payload_json.watersupply_pipe_code" => "required_if:water_source_id,1|nullable|string|max:255",
            "payload_json.well_presence_status" => "nullable|boolean",
            "payload_json.distance_from_well" => "nullable|integer|min:0",
            "payload_json.swm_customer_id" => "nullable|string|max:255",
            "payload_json.toilet_status" => "required|boolean",
            "payload_json.toilet_count" => "required_if:payload_json.toilet_status,1|nullable|integer|min:1",
            "payload_json.household_with_private_toilet" => "nullable|integer|min:0|lte:payload_json.household_served",
            "payload_json.population_with_private_toilet" => "nullable|integer|min:0|lte:payload_json.population_served",
            "payload_json.defecation_place" => "required_if:payload_json.toilet_status,0|nullable|integer",
            "payload_json.ctpt_name" => "required_if:payload_json.defecation_place,9|nullable|string|max:255",
            "payload_json.build_contain" => "required_if:sanitation_system_id,11|nullable|string|max:255",
            "payload_json.desludging_vehicle_accessible" => "nullable|boolean",
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
            "ward.required" => __('Ward Number is required.'),
            "road_code.required" => __('Road Code is required.'),
            "structure_type_id.required" => __('Structure Type is required.'),
            "functional_use_id.required" => __('Functional Use is required.'),
            "construction_year.required" => __('Construction Date is required.'),
            "floor_count.required" => __('Number of Floors is required.'),
            "water_source_id.required" => __('Main Drinking Water Source is required.'),
            "toilet_status.required" => __('Presence of Toilet is required.'),
            "sanitation_system_id.required_if" => __('Toilet Connection is required.'),
            "defecation_place.required_if" => __('Defecation Place is required.'),
            "ctpt_name.required_if" => __('Community Toilet Name is required.'),
            "build_contain.required_if" => __('BIN of Pre-Connected Building is required.'),
            "payload_json.array" => __('Additional payload must be a valid JSON object.'),
            "payload_json.structure_type_id.required" => __('Structure Type is required in payload_json.'),
            "payload_json.construction_year.required" => __('Construction Date is required in payload_json.'),
            "payload_json.floor_count.required" => __('Number of Floors is required in payload_json.'),
            "payload_json.toilet_status.required" => __('Presence of Toilet is required in payload_json.'),
        ];
    }
}
