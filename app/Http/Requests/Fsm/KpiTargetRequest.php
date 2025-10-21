<?php

namespace App\Http\Requests\Fsm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KpiTargetRequest extends FormRequest
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
        $rules = ($this->isMethod('POST') ? $this->store() : $this->update());
        return $rules;   
    }

    /**
     * Get the validation rules for storing a new record.
     *
     * @return array
     */
    public function store()
    {
        return [
            'indicator_id' => 'required|integer',
            'year' => [
                'required',
                'integer',
                'digits:4',
                'before_or_equal:' . now()->format('Y'),
                Rule::unique('pgsql.fsm.kpi_targets', 'year')
                    ->where(function ($query) {
                        $query->where('indicator_id', request()->input('indicator_id'))
                              ->whereNull('deleted_at');
                    })
            ],
            'target' => 'required|integer|max:100|min:0',
        ];
    }

    /**
     * Get the validation rules for updating an existing record.
     *
     * @return array
     */
    public function update()
    {
        $id = request()->route('kpi_target');
        return [
            'indicator_id' => 'required|integer',
            'year' => [
                'required',
                'integer',
                'digits:4',
                'before_or_equal:' . now()->format('Y'),
                Rule::unique('pgsql.fsm.kpi_targets', 'year')
                    ->where(function ($query) use($id)  {
                        $query->where('indicator_id', request()->input('indicator_id'))
                              ->whereNull('deleted_at');
                    })->ignore(request()->route('kpi_target'), 'id')
            ],
            'target' => 'required|integer|max:100|min:0',
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'indicator_id.required' => __('The Indicator is required.'),
            'year.required' => __('The Year is required.'),
            'year.unique' => __('The Indicator for the year is already present.'),
            'year.integer' => __('The Year must be an integer.'),
            'year.before_or_equal' => __('The Year must not be greater than current year.'),
            'year.digits' => __('The Year must be in 20** format.'),
            'target.required' => __('The Target (%) is required.'),
            'target.integer' => __('The Target (%) must be an integer.'),
            'target.max' => __('The target must not be greater than 100.'),
        ];
    }
}

