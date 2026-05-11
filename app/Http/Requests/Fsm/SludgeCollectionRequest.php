<?php
// Last Modified: 2026-05-04
// Developed By: Streams Tech Ltd.
// Description: Validation rules for sludge collection create/update requests across API and web flows.

namespace App\Http\Requests\Fsm;

use Illuminate\Foundation\Http\FormRequest;

class SludgeCollectionRequest extends FormRequest
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
        if ($this->isApiSaveRequest()) {
            return [
                'application_id' => 'required|integer|exists:fsm.applications,id',
                'date' => 'required|date|after_or_equal:today',
                'no_of_trips' => 'required|integer|min:1',
                'entry_time' => 'required|date_format:H:i',
                'exit_time' => 'required|date_format:H:i|after:entry_time',
            ];
        }

        $rules= ($this->isMethod('POST')? $this->store() : $this->update());
        return $rules;
    }

    protected function prepareForValidation()
    {
        if ($this->has('applicationId') && !$this->has('application_id')) {
            $this->merge([
                'application_id' => $this->input('applicationId'),
            ]);
        }
    }

    public function store()
    {
        return [
            'treatment_plant_id' => 'required|integer',
            'date' => 'required|date|after_or_equal:today',
            'no_of_trips' => 'required|integer|min:1',
            'entry_time' => 'required|date_format:H:i',
            'exit_time' => 'required|date_format:H:i|after:entry_time',

        ];
    }
    public function update()
    {
        return [
            'date' => 'required|date|after_or_equal:today',
            'no_of_trips' => 'required|integer|min:1',
            'entry_time' => 'required|date_format:H:i',
            'exit_time' => 'required|date_format:H:i|after:entry_time',
            'treatment_plant_id' => 'required|integer',

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
            'application_id.required' => __('The Application ID is required.'),
            'application_id.exists' => __('The selected Application ID is invalid.'),
            'treatment_plant_id.required' => __('The Treatment Plant is required.'),
            'date.required' => __('The Date is required.'),
            'no_of_trips.required'=> __('The No. of Trips is required.'),
            'no_of_trips.integer'=> __('The No. of Trips must be an integer.'),
            'no_of_trips.min'=> __('The No. of Trips must be at least 1.'),
            'entry_time.required' => __('The Entry Time is required.'),
            'exit_time.required' => __('The Exit Time is required.'),
            'exit_time.after' => __('The Exit Time must be after the Entry Time.'),

        ];
    }

    private function isApiSaveRequest()
    {
        return $this->is('api/save-sludge-collection') && $this->isMethod('POST');
    }
}
