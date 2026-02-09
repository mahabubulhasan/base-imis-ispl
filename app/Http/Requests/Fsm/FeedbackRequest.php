<?php
// Last Modified Date: 18-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)
namespace App\Http\Requests\Fsm;

use Illuminate\Foundation\Http\FormRequest;

class FeedbackRequest extends FormRequest
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
            'application_id' => 'nullable|integer|exists:fsm.applications,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_gender' => 'nullable|string|in:Male,Female,Other',
            'customer_number' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string|max:255',
            'fsm_service_quality' => 'required|boolean',
            'wear_ppe' => 'required|boolean',
            'comments' => 'nullable|string|max:1000',
            'service_quality_price' => 'nullable|integer|between:1,4',
            'service_delivery_efficiency' => 'nullable|integer|between:1,4',
            'fsm_quality_level' => 'nullable|integer|between:-1,4',
            'price_reasonable' => 'nullable|boolean',
            'advertising_media' => 'nullable|string|max:250',
            'safety_measures' => 'nullable|string|max:250',
            'payment_mechanism_comments' => 'nullable|string|max:1000',
            'apply_in_future' => 'nullable|integer|in:0,1',
            'apply_in_future_comments' => 'nullable|string|max:1000',
            'recommend_service' => 'nullable|integer|in:0,1',
            'recommend_service_comments' => 'nullable|string|max:1000',
    ];
    }

    public function update()
    {
        return [
            'application_id' => 'nullable|integer|exists:fsm.applications,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_gender' => 'nullable|string|in:Male,Female,Other',
            'customer_number' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string|max:255',
            'fsm_service_quality' => 'required|boolean',
            'wear_ppe' => 'required|boolean',
            'comments' => 'nullable|string|max:1000',
            'service_quality_price' => 'nullable|integer|between:1,4',
            'service_delivery_efficiency' => 'nullable|integer|between:1,4',
            'fsm_quality_level' => 'nullable|integer|between:-1,4',
            'price_reasonable' => 'nullable|boolean',
            'advertising_media' => 'nullable|string|max:250',
            'safety_measures' => 'nullable|string|max:250',
            'payment_mechanism_comments' => 'nullable|string|max:1000',
            'apply_in_future' => 'nullable|integer|in:0,1',
            'apply_in_future_comments' => 'nullable|string|max:1000',
            'recommend_service' => 'nullable|integer|in:0,1',
            'recommend_service_comments' => 'nullable|string|max:1000',
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
            'fsm_service_quality.required' => __('The FSM Service Quality is required.'),
            'wear_ppe.required' => __('The Wear PPE is required.'),

        ];
    }
}
