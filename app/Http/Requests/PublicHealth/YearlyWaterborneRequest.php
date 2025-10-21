<?php

namespace App\Http\Requests\PublicHealth;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\AtLeastOneFieldRequired;
use App\Rules\FatalitiesLessThanCases;

class YearlyWaterborneRequest extends FormRequest
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


    public function messages()
    {
        return [
            'infected_disease.required' => __('The Infected Disease Name is required.'),
            'year.required' => __('The Year is required.'),
            'male_cases.required' => __('Number of Male Cases required.'),
            'female_cases.required' => __('Number of Female Cases required.'),
            'other_cases.required' => __('Number of Other Cases required.'),
            'male_cases.numeric' => __('Number of Male Cases should be number.'),
            'female_cases.numeric' => __('Number of Female Cases should be number.'),
            'other_cases.numeric' => __('Number of Other Cases should be number.'),
            'male_fatalities.lte' => __('The Male Fatalities should be less than Male Cases.'),
            'female_fatalities.lte' => __('The Female Fatalities should be less than Female Cases.'),
            'other_fatalities.lte' => __('The Other Fatalities should be less than Other Cases.'),

            // 'total_no_of_cases.required' => 'The Total Number Cases is required.',
        ];
    }

    public function rules()
    {
        return [
            'infected_disease' => 'required',
            'year' => 'required',
            'male_cases' => ['required', 'numeric', 'min:0', new AtLeastOneFieldRequired],
            'female_cases' => ['required', 'numeric', 'min:0', new AtLeastOneFieldRequired],
            'other_cases' => ['required', 'numeric', 'min:0', new AtLeastOneFieldRequired],
            'male_fatalities' => ['nullable', 'numeric', 'min:0', 'lte:male_cases'],
            'female_fatalities' => ['nullable', 'numeric', 'min:0', 'lte:female_cases'],
            'other_fatalities' => ['nullable', 'numeric', 'min:0', 'lte:other_cases'],

        ];
    }
}
