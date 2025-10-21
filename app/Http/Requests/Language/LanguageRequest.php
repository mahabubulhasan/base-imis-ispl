<?php

namespace App\Http\Requests\Language;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class LanguageRequest extends FormRequest
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

    // 🆕 Normalize code to lowercase before validation
    protected function prepareForValidation()
    {
        if ($this->has('code')) {
            $this->merge([
                'code' => strtolower($this->input('code')), // Converts 'NEP' to 'nep'
            ]);
        }
    }

    public function rules()
    {
        $rules = ($this->isMethod('POST') ? $this->store() : $this->update());
        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => __('Language Name required.'),
            'code.required' => __('Language Code required.'),
            'status.required' => __('Language Status required.'),
            'code.unique' => __('Language Code already exists.'),
            'code.regex' => __('Language Code must contain only letters and be up to 4 characters.'),
        ];
    }

    /**
     * Validation rules for storing a new language.
     */
    public function store()
    {
        return [
            'name' => 'required',
            'status' => 'required',
            'code' => [
                'required',
                'regex:/^[a-zA-Z]{1,4}$/',
                Rule::unique('pgsql.language.languages', 'code')
                    ->where(function ($query) {
                        return $query->whereNull('deleted_at');
                    }),
            ],
        ];
    }

    /**
     * Validation rules for updating an existing language.
     */
    public function update()
    {
        $id = request()->route('setup');

        return [
            'name' => 'required',
            'status' => 'required',
            'code' => [
                'required',
                'regex:/^[a-zA-Z]{1,4}$/',
                Rule::unique('pgsql.language.languages', 'code')
                    ->where(function ($query) {
                        return $query->whereNull('deleted_at');
                    })
                    ->ignore($id),
            ],
        ];
    }
}
