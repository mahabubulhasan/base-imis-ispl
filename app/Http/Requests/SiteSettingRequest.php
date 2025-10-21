<?php
// Last Modified Date: 18-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Http\Requests;

use App\Http\Requests\Request;

class SiteSettingRequest extends Request
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
        
        switch ($this->method()) 
        {
            case 'GET':
            case 'DELETE':
                {
                    return [];
                }
            case 'POST':
                {
                    return [
                        'name' => 'required|max:255',
                        'value' => 'required|integer',
                        
                    ];
                }
            case 'PUT':
            case 'PATCH':
                {
                    return [
                        'name' => 'nullable|max:255',
                        'width' => 'nullable|numeric',
                        'length' => 'nullable|numeric',
                        'carrying_width' => 'nullable|numeric',
                    ];
                }
            default:break;
        }
    }
     public function messages()
    {
        return [
            'name.regex' => __('The name field should contain only contain letters and spaces.'),
            'name.required' => __('Name is required.'),
            'value.required' => __('Value is required.'),
            'value.integer' => __('Value must be an integer.'),
            ];
    }
}
