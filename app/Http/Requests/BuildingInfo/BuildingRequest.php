<?php

namespace App\Http\Requests\BuildingInfo;

use Illuminate\Foundation\Http\FormRequest;
use Validator, Input, Redirect;
class BuildingRequest extends FormRequest
{
    /**
     * Determine if the user  authorized to make this request.
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
    public function messages()
    {
        return [
            //owner part
            'owner_name.required' => 'Owner Name is required.',
            'owner_contact.integer' => 'Owner Contact should be integer value',
            'owner_gender.required' => 'Owner Gender is required',
            'owner_contact.required' => 'Owner Contact Number is required',


            //Building Information
            'main_building.required' => "The Main Building is required.",
            'building_associated_to.required_if' => "The BIN of Main Building is required.",
            'ward.required' => 'Ward Number is required.',
            'floor_count.required' => 'Number of Floors is required',
            'tax_code.required' => 'Tax Code/Holding ID is required',
            'road_code.required' => 'Road Code is required.',
            'structure_type_id.required' => 'Structure Type is required.',
            'functional_use_id.required' => 'Functional Use of Building is required.',
            'house_number.unique' => 'The House Number is Already Taken',

            'main_building.required' => "The Main Building status required.",
            'building_associated_to.required_if' => "BIN of Main Building required.",
            'ward.required' => 'Ward Number required.',
            'tax_code.required' => 'Tax Code/Holding ID required',
            'road_code.required' => 'Road Code  required.',
            'structure_type_id.required' => 'Structure Type  required.',
            'functional_use_id.required' => 'Functional Use of Building  required.',
            'house_number.unique' => 'The House Number is Taken',
            //population Validation
            'diff_abled_male_pop.lte' => 'The Differently Abled Male Population must not exceed the Male Population.',
            'diff_abled_female_pop.lte' => 'The Differently Abled Female Population must not exceed the Female Population.',
            'diff_abled_others_pop.lte' => 'The Differently Abled Other Population must not exceed the Other Population.',

            'construction_year.required' => 'Building Construction Date is required.',
            //lic
            'low_income_hh.required' => 'Is Low Income House is required.',

            'lic_id.required_if' => 'LIC Name is required.',
            //Water Source Information
            'water_source_id.required' => "Main Drinking Water Source is required",
            'watersupply_pipe_code.required_if' => 'Water Supply Pipe Line Code is required',

            //Sanitation System Information
            'toilet_status.required' => 'Presence of Toilet is required',
            'sewer_code.required_if' => 'Sewer Code is required.',
            'drain_code.required_if' => 'Drain Code is required.',
            'ctpt_name.required_if' => 'Community Toilet Name is required.',

            'size.required_if' => 'Containment Volume (m³) is required. Enter dimensions to auto generate.',

            'geom.required_if' => 'Building Footprint (KML) is required.',
            'floor_count.numeric' => 'Number of Floors should be numeric value.',
            'use_category_id.required_with' => 'Use Category of Building is required.',

            'depth.numeric' => 'Tank Depth should be numeric value.',
            'tank_length.numeric' => 'Tank Length should be numeric value.',
            'tank_width.numeric' => 'Tank Width should be numeric value.',
            'pit_depth.numeric' => 'Pit Depth should be numeric value.',
            'pit_diameter.integer' => 'Pit Diameter should be integer value.',
            'size.numeric' => 'Containment Volume should be numeric value.',
            'floor_count.min' => 'Number of Floors should be positive and non-zero value.',
            'household_with_private_toilet.lte' => 'Household with Private Toilet must not exceed the Number of Households',
            'population_with_private_toilet.lte' => 'Population with Private Toilet must not exceed the Population of Building',
            'toilet_count.min' => 'Number of Toilets should be at least 1.',
            'toilet_count.integer' => 'Number of Toilets should be integer value.',
            'toilet_count.required_if' => 'Number of Toilets is required.',
            'household_served.required_unless' => 'Number of Households is required',
            'population_served.required_unless' => 'Population of Building is required',
            'household_served.integer'=> 'Number of Household should be integer value',
            'population_served.integer'=> 'Number of Population should be integer value',
            'distance_from_well.integer' => 'Distance of containment from well should be integer value.',
            'distance_from_well.min' => 'Distance of containment from well should be positive value.',
            'depth.min' => 'Tank depth should be positive value.',
            'tank_length.min' => 'Tank length should be positive value.',
            'tank_width.min' => 'Tank width Count should be positive value.',
            'pit_depth.min' => 'Pit Depth should be positive value.',
            'pit_diameter.min' => 'Pit Diameter Count should be positive value.',
            'size.min' => 'Containment Volume should be positive value.',
            'construction_year' => 'Cant Select Future Value',
            'sanitation_system_id.required_if' => 'Toilet Connection is required.',
            //containment message for validation
            'type_id.required_if' => 'Containment Type is required when Toilet Connection  Septic or Pit/Holding.',
            'defecation_place.required_if' => 'Defecation Place is required',
            'build_contain.required_if' => 'BIN of Pre-Connected Building is required',
            // 'construction_date.required_if'=>'Containment Construction date  required.',
            'pit_shape.required_if' => 'Pit shape is required.',

        ];
    }

    public function rules()
    {
        $rules = ($this->isMethod('POST') ? $this->store() : $this->update());
        return $rules;
    }

    public function store()
    {
        Validator::extend(
            'file_extension',
            function ($attribute, $value, $parameters, $validator) {
                if (!in_array($value->getClientOriginalExtension(), $parameters)) {
                    return false;
                } else {
                    return true;
                }
            },
            'Building Footprint (KML) file must be kml format'
        );
        $use_cat = $this->input('use_category_id');
        return [
            // // Owner Infomation
            'owner_name' => 'required',
            'owner_gender' => 'required',
            'owner_contact' => 'integer|min:0|required',
            //Building Information
            'main_building' => 'required',
            'building_associated_to' => 'required_if:main_building,0',
            'ward' => 'required',
            'road_code' => 'required',
            'house_number' => 'nullable|unique:pgsql.building_info.buildings,house_number',
            // Allow comma-separated tax codes; max length ~250 chars
            'tax_code' => [
                'required',
                'string',
                'max:250',
                // Pattern: code or multiple codes separated by commas with optional spaces
                'regex:/^\s*\d{2}-\d{3}-\d{4}-\d{2}(\s*,\s*\d{2}-\d{3}-\d{4}-\d{2})*\s*$/'
            ],
            'structure_type_id' => 'required',
             //year of building Construction
            'construction_year' => 'required|date|before_or_equal:today',
             //year of building Construction
            'construction_year' => 'required|date|before_or_equal:today',
            'floor_count' => 'required|numeric|min:0.1',
            'functional_use_id' => 'required',
            'use_category_id' => 'required_with:functional_use_id',
            'household_served' => [
                // not required if use cat is Public Toilet or Community Toilet
                'required_unless:use_category_id,34,35',
                'nullable',
                'integer',
                'min:0',
            ],
            'male_population' => 'nullable|integer|min:0',
            'female_population' => 'nullable|integer|min:0',
            'other_population' => 'nullable|integer|min:0',
            'diff_abled_male_pop' => 'nullable|integer|min:0|exclude_if:diff_abled_male_pop,0|lte:male_population',
            'diff_abled_female_pop' => 'nullable|integer|min:0|exclude_if:diff_abled_female_pop,0|lte:female_population',
            'diff_abled_others_pop' => 'nullable|integer|min:0|exclude_if:diff_abled_others_pop,0|lte:other_population',
            'population_served' => [
                // not required if use cat is Public Toilet or Community Toilet
                'required_unless:use_category_id,34,35',
                'nullable',
                'integer',
                'min:0',
            ],
           
            //Lic Information
            'low_income_hh' => 'required',
            // 'lic_status' => 'required_if:low_income_hh,1',
            'lic_id' => 'required_if:lic_status,1',
            //water source Information
            'water_source_id' => 'required',
            'watersupply_pipe_code' => 'required_if:water_source_id,1',
            //sanitation system Information
            'toilet_status' => ['required',
             function ($attribute, $value, $fail) use ($use_cat) {
                if (($use_cat == 34 && $value != true) || ($use_cat == 35 && $value != true) ) {
                    $fail("The Toilet Presence must be Yes when Use Category is Public Toilet or Community Toilet");
                }
            }
            ],
            'toilet_count' => 'exclude_if:toilet_status,0 |required_if:toilet_status,1| integer| min:1',
            'sanitation_system_id' => 'required_if:toilet_status,1',
            'defecation_place' => 'required_if:toilet_status,0',
            'ctpt_name' => 'exclude_if:toilet_status,1 | required_if:defecation_place,9',
            'household_with_private_toilet' => 'nullable |min:0 | lte:household_served',
            'population_with_private_toilet' =>'nullable|min:0| lte:population_served',
           
            // containment validation
            // exclude if has been used to ensure cascading parent values are also checked below 
            // child values are validated. E.g., dont validation type_id if toilet is no
            'type_id' => 'exclude_if:toilet_status,0 | required_if:sanitation_system_id,3,4',
            'size' =>  'exclude_if:toilet_status,0 | required_if:sanitation_system_id,3,4',
            // exclude if has been used to ensure cascading parent values are also checked below 
            // child values are validated. E.g., dont validation type_id if toilet is no
            'type_id' => 'exclude_if:toilet_status,0 | required_if:sanitation_system_id,3,4',
            'size' =>  'exclude_if:toilet_status,0 | required_if:sanitation_system_id,3,4',
            'depth' => 'numeric|nullable|min:0',
            'tank_length' => 'numeric|nullable|min:0',
            'tank_width' => 'numeric|nullable|min:0',
            'pit_depth' => 'numeric|nullable|min:0',
            'pit_diameter' => 'numeric|nullable|min:0',
            'build_contain' => 'exclude_if:toilet_status,0 | required_if:sanitation_system_id,11',
            //drain and sewer code
            'sewer_code' => 'exclude_if:toilet_status,0 |exclude_if:type_id,2,14 | required_if:sanitation_system_id,1| required_if:type_id,1,13',
            'drain_code' => 'exclude_if:toilet_status,0 |exclude_if:type_id,1,13 |  required_if:sanitation_system_id,2 | required_if:type_id,2,14',
            'geom' => 'required_if:kml,null|file_extension:kml|max:1024',
            'house_image' => 'nullable|image|mimes:jpeg,jpg|max:5120', // 5MB = 5120KB

        ];
    }

    public function update()
    {
        $bin = $this->input('building'); 
        $use_cat = $this->input('use_category_id');
        Validator::extend('file_extension', function ($attribute, $value, $parameters, $validator) {
            if (!in_array($value->getClientOriginalExtension(), $parameters)) {
                return false;
            } else {
                return true;
            }
        }, 'File must be kml format');
        return [
            //  compulsory fields
            // Owner Information
            'owner_name' => 'required',
            'owner_contact' => 'integer|min:0|required',
            'owner_gender' => 'required',
            //Building Information
            'main_building' => 'required',
            // associated bin required only if not main building
            'building_associated_to' => 'required_if:main_building,0',
            'ward' => 'required',
            'road_code' => 'required',
            'house_number' => 'nullable|unique:pgsql.building_info.buildings,bin,' . $bin . ',bin',
            // Allow comma-separated tax codes; max length ~250 chars
            'tax_code' => [
                'required',
                'string',
                'max:250',
                'regex:/^\s*\d{2}-\d{3}-\d{4}-\d{2}(\s*,\s*\d{2}-\d{3}-\d{4}-\d{2})*\s*$/'
            ],
            'structure_type_id' => 'required',
            'use_category_id' => 'required_with:functional_use_id',
            //year of building Construction
            'construction_year' => 'required|date|before_or_equal:today',
            'floor_count' => 'required|numeric|min:0.1',
            'functional_use_id' => 'required',
            'household_served' => [
                // not required if use cat is Public Toilet or Community Toilet
                'required_unless:use_category_id,34,35',
                'integer',
                'nullable',
                'min:0'
            ],
            'population_served' => [
                // not required if use cat is Public Toilet or Community Toilet
                'required_unless:use_category_id,34,35',
                'integer',
                'nullable',
                'min:0'
            ],
            'diff_abled_male_pop' => 'nullable|integer|min:0|exclude_if:diff_abled_male_pop,0|lte:male_population',
            'diff_abled_female_pop' => 'nullable|integer|min:0|exclude_if:diff_abled_female_pop,0|lte:female_population',
            'diff_abled_others_pop' => 'nullable|integer|min:0|exclude_if:diff_abled_others_pop,0|lte:other_population',
            //Lic Information
            'low_income_hh' => 'required',
            'lic_id' => 'required_if:lic_status,1',
            //water source Information
            'water_source_id' => 'required',
            //Lic Information
            'low_income_hh' => 'required',
            'lic_id' => 'required_if:lic_status,1',
            //water source Information
            'water_source_id' => 'required',
            'watersupply_pipe_code' => 'required_if:water_source_id,1',

            //sanitation system Information
            'toilet_status' => ['required',
            function ($attribute, $value, $fail) use ($use_cat) {
               if (($use_cat == 34 && $value != true) || ($use_cat == 35 && $value != true) ) {
                   $fail("The Toilet Presence must be Yes when Use Category is Public Toilet or Community Toilet");
               }
           }
           ],
            'toilet_count' => 'exclude_if:toilet_status,0 |required_if:toilet_status,1 | integer | min:1',
            'sanitation_system_id' => 'required_if:toilet_status,1',
            'defecation_place' => 'required_if:toilet_status,0',
            'ctpt_name' => 'exclude_if:toilet_status,1 | required_if:defecation_place,9',
            'household_with_private_toilet' => 'nullable |min:0| lte:household_served',
            'population_with_private_toilet' => 'nullable|min:0| lte:population_served',

            //drain and sewer code
            'sewer_code' => 'exclude_if:toilet_status,0 |required_if:sanitation_system_id,1',
            'drain_code' => 'exclude_if:toilet_status,0 |required_if:sanitation_system_id,2',
            'build_contain' => 'exclude_if:toilet_status,0 |required_if:sanitation_system_id,11',
            'house_image' => 'nullable|image|mimes:jpeg,jpg|max:5120', // 5MB = 5120KB
            'geom' => 'nullable|file_extension:kml|max:1024',
        ];
    }
}
