<?php
// Last Modified Date: 09-02-2026
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)
namespace App\Http\Requests\Fsm;

use Illuminate\Foundation\Http\FormRequest;

class PublicFeedbackRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Public form, no authorization required
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Application details
            'application_id' => 'required|integer|exists:fsm.applications,id',

            // Customer information (auto-populated but editable)
            'customer_name' => 'required|string|max:255',
            'customer_number' => 'required|string|max:20',
            'customer_gender' => 'nullable|string|in:Male,Female,Other',
            'customer_address' => 'nullable|string|max:255',

            // Q1: Safety measures (checkboxes as comma-separated string)
            'safety_measures' => 'required|string|max:250',

            // Q2: Attitude of emptiers (1-4 scale)
            'fsm_quality_level' => 'required|integer|between:1,4',

            // Q3: Response time of emptying service (1-4 scale)
            'service_delivery_efficiency' => 'required|integer|between:1,4',

            // Q4: Overall service satisfaction (1-4 scale) - stored as boolean conversion
            'overall_satisfaction' => 'required|integer|between:1,4',

            // Q5: Price satisfaction (1-4 scale)
            'service_quality_price' => 'required|integer|between:1,4',

            // Q6: Dissatisfaction reasons (conditional, can be null)
            'comments' => 'nullable|string|max:1000',

            // Q7: Payment mechanism (Yes=1, No=0)
            'payment_mechanism_satisfied' => 'nullable|integer|in:0,1',
            'payment_mechanism_comments' => 'nullable|string|max:1000',

            // Q8: Apply in future (Yes=1, No=0)
            'apply_in_future' => 'required|integer|in:0,1',
            'apply_in_future_comments' => 'nullable|string|max:1000',

            // Q9: Recommend service (Yes=1, No=0)
            'recommend_service' => 'required|integer|in:0,1',
            'recommend_service_comments' => 'nullable|string|max:1000',

            // Q10: How did you hear (checkboxes as comma-separated string)
            'advertising_media' => 'required|string|max:250',

            // Anti-spam fields
            'website' => 'nullable|string|max:0', // Honeypot field, must be empty
            'form_loaded_at' => 'required|integer',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'application_id.required' => 'Application Number is required.',
            'application_id.exists' => 'Invalid Application Number. Please check and try again.',
            'customer_name.required' => 'Service Receiver Name is required.',
            'customer_number.required' => 'Service Receiver Contact is required.',
            'safety_measures.required' => 'Please select at least one safety measure.',
            'fsm_quality_level.required' => 'Please rate the attitude of the emptiers.',
            'fsm_quality_level.between' => 'Invalid rating value.',
            'service_delivery_efficiency.required' => 'Please rate the response time.',
            'service_delivery_efficiency.between' => 'Invalid rating value.',
            'overall_satisfaction.required' => 'Please rate your overall satisfaction with the service.',
            'overall_satisfaction.between' => 'Invalid rating value.',
            'service_quality_price.required' => 'Please rate your satisfaction with the price.',
            'service_quality_price.between' => 'Invalid rating value.',
            'apply_in_future.required' => 'Please indicate if you would apply in the future.',
            'apply_in_future.in' => 'Invalid value for future application.',
            'recommend_service.required' => 'Please indicate if you would recommend this service.',
            'recommend_service.in' => 'Invalid value for recommendation.',
            'advertising_media.required' => 'Please select how you heard about the FSM service.',
            'website.max' => 'Invalid form submission.', // Honeypot triggered
            'form_loaded_at.required' => 'Invalid form submission.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Ensure honeypot field exists even if not sent
        if (!$this->has('website')) {
            $this->merge(['website' => null]);
        }
    }
}
