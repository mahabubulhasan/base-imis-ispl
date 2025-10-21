<?php
// Last Modified Date: 09-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)    
namespace App\Http\Requests\Fsm;
use Illuminate\Validation\Rule;

use Illuminate\Foundation\Http\FormRequest;

class VacuTugRequest extends FormRequest
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
        
            $id = $this->route()->desludging_vehicle;
      
              switch ($this->method()) {
                  case 'GET':
                  case 'DELETE':
                      {
                          return [];
                      }
                  case 'POST':
                      {
                          return [
                              'license_plate_number' => [
                                  'required',
                                  'string',
                                  Rule::unique('pgsql.fsm.desludging_vehicles', 'license_plate_number')
                                      ->where(function ($query) use ($id) {
                                          $query->whereNull('deleted_at')
                                                ->where('id', '<>', $id);
                                      }),],
                                     
                                      'service_provider_id' => 'required|integer',
                                      'capacity' => 'required|numeric|min:0.01',
                                      'width' => 'required|numeric|min:0.01',
                                      'comply_with_maintainance_standards' => 'required|boolean',
                                      'status' => 'required|boolean'
                          ];
                      }
                  case 'PUT':
                case 'PATCH':
                          {
                              return [
                                'license_plate_number' => [
                                    'required',
                                    'string',
                                    Rule::unique('pgsql.fsm.desludging_vehicles', 'license_plate_number')
                                        ->where(function ($query) use ($id) {
                                            $query->where('id', '!=', $id)->whereNull('deleted_at');
                                        })
                                        ->ignore($id),
                                ],
                                
                                  'service_provider_id' => 'required|integer',
                                  'capacity' => 'required|numeric|min:0.01',
                                  'width' => 'required|numeric|min:0.01',
                                  'comply_with_maintainance_standards' => 'required|boolean',
                                  'status' => 'required|boolean'
                              ];
                          }
                             
                  default:break;
              }
    }
    public function messages()
    {
        return[
            'license_plate_number.required' => __('The Vehicle License Plate Number is required.'),
            //'license_plate_number.integer' => 'The Vehicle License plate number must be an integer.',
            'capacity.required' => __('The Capacity (m³) is required.'),
            'capacity.numeric' => __('The Capacity (m³) must be numeric.'),
            'capacity.min' => __('The Capacity must be positive value.'),
            'width.required' => __('The Width (m) is required.'),
            'width.numeric' => __('The Width (m) must be numeric.'),
            'width.min' => __('The Width must be positive value.'),
            'service_provider_id.required' => __('The Service Provider is required.'),
            'service_provider_id.integer' => __('The Service Provider Id must be integer.'),
            'comply_with_maintainance_standards.required' => __('The Comply with Maintainance Standard is required.'),
            'status.required' => __('The Status is required.')
        ];
    }
}
