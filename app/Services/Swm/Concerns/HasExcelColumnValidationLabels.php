<?php

namespace App\Services\Swm\Concerns;

use App\Support\Swm\SwmExcelColumns;

trait HasExcelColumnValidationLabels
{
    /**
     * @return array<int, array{key: string, label?: string}>
     */
    abstract protected function excelColumnDefinitions(): array;

    /**
     * @return array<string, string>
     */
    protected function formOnlyValidationLabels(): array
    {
        return [];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributeLabels(): array
    {
        return array_merge(
            SwmExcelColumns::labelMap($this->excelColumnDefinitions()),
            $this->formOnlyValidationLabels()
        );
    }
}
