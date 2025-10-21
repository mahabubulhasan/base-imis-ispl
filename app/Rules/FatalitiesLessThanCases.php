<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class FatalitiesLessThanCases implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */

     private $casesAttribute;
    public function __construct($casesAttribute)
    {
        $this->casesAttribute = $casesAttribute;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Get the value of the corresponding cases attribute
        $casesValue = request()->input($this->casesAttribute);

        // Check if cases value is not null and fatalities value is less than cases value
        return is_null($casesValue) || ($value <= $casesValue);
    }


    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('The fatalities must be less than the corresponding cases.');
    }
}
