<?php

namespace App\Http\Requests\Concerns;

trait MapsValidationAttributes
{
    /**
     * @return array<string, string>
     */
    abstract protected function validationAttributeLabels(): array;

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return $this->validationAttributeLabels();
    }
}
