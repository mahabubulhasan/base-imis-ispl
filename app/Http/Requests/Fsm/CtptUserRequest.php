<?php
// Last Modified Date: 09-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Http\Requests\Fsm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class CtptUserRequest extends FormRequest
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
        
      
     
     /**
      * Get the validation rules that apply to the request.
      *
      * @return array
      */
     public function store()
     { 
         
         
         return [
             
            'toilet_id' => 'required',
            'date' =>'required|date|before_or_equal:'.date('m/d/Y'),
            'no_male_user' => 'required|numeric|min:0',
            'no_female_user' => 'required|numeric|min:0',
     ];
     }
 
     public function update()
     {
        return[
        'no_male_user' => 'required|numeric|min:0',
        'no_female_user' => 'required|numeric|min:0',

        ];
     }
 
     public function messages()
     {
         return[
             'toilet_id.required'=> __('The Toilet Name is required.'),
             'date.required'=> __('The Date is required.'),
             'date.before_or_equal'=> __('The date must be todays.'),
             'no_male_user.required'=> __('The No. of Male Users is required.'),
             'no_male_user.numeric'=> __('The No. of Male Users must be numeric.'),
             'no_male_user.min'=> __('The No. of Male Users  must be positive value.'),
             'no_female_user.required'=> __('The No. of Female Users is required.'),
             'no_female_user.numeric'=> __('The No. of Female must be numeric.'),
             'no_female_user.min'=> __('The No. of Female must be positive value.'),
         ];
     }
 }

