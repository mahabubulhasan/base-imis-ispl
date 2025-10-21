<?php
// Last Modified Date: 10-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)    
namespace App\Http\Requests\Fsm;

use Illuminate\Foundation\Http\FormRequest;

class TreatmentPlantTestRequest extends FormRequest
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


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */

    public function messages()
    {
        return [
            'treatment_plant_id.required' => __('Treatment Plant is required.'),
            'date.required' => __('Sample Date is required.'),
            'temperature.integer' => __('Temperature °C must be number.'),
            'ph.integer' => __('pH must be number.'),
            'cod.integer' => __('COD (mg/l) must be number.'),
            'bod.integer' => __('BOD (mg/l) must be number.'),
            'tss.integer' => __('TSS (mg/l) must be number.'),
            'ecoli.integer' => __('Ecoli must be an integer.'),
            'cod.required' => __('COD (mg/l) is required.'),
            'ph.required' => __('pH is required.'),
            'bod.required' => __('BOD (mg/l) is required.'),
            'tss.required' => __('TSS (mg/l) is required.'),
            'ecoli.required' => __('Ecoli is required.'),
            'temperature.required' => __('Temperature °C is required.'),
            'ph.between' => __('pH must be between 0 and 14.'),
        ];
    }

    public function rules()
    {

        $rules = ($this->isMethod('POST') ? $this->store() : $this->update());
        return $rules;
    }



    public function store()
    {
        $rules = [
            'treatment_plant_id' => 'required',
            'date' => 'required|date|before_or_equal:today',
            'temperature' => 'required|numeric|min:0',
            'ph' => 'required|numeric|between:0,14',
            'cod' => 'required|numeric|min:0',
            'bod' => 'required|numeric|min:0',
            'tss' => 'required|numeric|min:0',
            'ecoli' => 'required|integer|min:0'
        ];

        return $rules;
    }




    public function update()
    {
        return [
            'treatment_plant_id' => 'required',
            'date' => 'required',
            'temperature' => 'required|numeric|min:0',
            'ph' => 'required|numeric|between:0,14',
            'cod' => 'required|numeric|min:0',
            'bod' => 'required|numeric|min:0',
            'tss' => 'required|numeric|min:0',
            'ecoli' => 'required|integer|min:0'
        ];
    }
}
