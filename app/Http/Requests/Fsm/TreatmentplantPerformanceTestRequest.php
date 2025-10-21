<?php
// Last Modified Date: 18-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Http\Requests\Fsm;

use Illuminate\Foundation\Http\FormRequest;

class TreatmentplantPerformanceTestRequest extends FormRequest
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
        $rules= ($this->isMethod('POST')? $this->store() : $this->update());
        return $rules;   
    }
    
    public function store()
    {
        return [
            'tss_standard' => 'nullable|numeric',
            'ecoli_standard' => 'nullable|integer',
            'ph_min' => 'nullable|numeric|between:0,14',
            'ph_max' => 'nullable|numeric|between:0,14',
            'bod_standard' => 'nullable|numeric',
        ];
    }
    public function update()
    {
        return [
            'tss_standard' => 'nullable|numeric',
            'ecoli_standard' => 'nullable|integer',
            'ph_min' => 'nullable|numeric|between:0,14',
            'ph_max' => 'nullable|numeric|between:0,14',
            'bod_standard' => 'nullable|numeric',
          
        ];
    }

    /**
     * Get the messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'tss_standard.numeric' => __('The TSS Standard is numeric.'),
            'ecoli_standard.integer' => __('The ECOLI Standard is integer.'),
            'ph_min.numeric' => __('The pH Minimum is numeric.'),
            'ph_min.between' => __('The pH Minimum between or equal to 0 and 14.'),
            'ph_max.numeric' => __('The pH Maximum is numeric.'),
            'ph_max.between' => __('The pH Maximum between or equal to 0 and 14.'),
            'bod_standard.numeric' => __('The BOD Standard is numeric.'),

        ];
    }
}
