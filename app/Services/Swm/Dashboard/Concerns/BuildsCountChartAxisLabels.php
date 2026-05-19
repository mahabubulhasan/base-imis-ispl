<?php

namespace App\Services\Swm\Dashboard\Concerns;

trait BuildsCountChartAxisLabels
{
    protected function countChartAxisY(string $entity): string
    {
        return __('Number of :entity', ['entity' => $entity]);
    }
}
