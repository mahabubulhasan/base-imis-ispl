<?php

namespace App\Services\Swm\Dashboard\Concerns;

use App\Services\Swm\Dashboard\DashboardReportingPeriod;
use App\Services\Swm\Dashboard\ReportingWindow;
use Illuminate\Database\Eloquent\Builder;

trait BuildsCumulativeDateQueries
{
    protected function whereThroughPeriodEnd(Builder $query, string $column, DashboardReportingPeriod $period): Builder
    {
        return $query->where($column, '<=', $period->periodEnd);
    }

    protected function whereWithinWindow(Builder $query, string $column, ReportingWindow $window): Builder
    {
        if (! $window->hasData()) {
            return $query;
        }

        return $query
            ->where($column, '>=', $window->epoch)
            ->where($column, '<=', $window->periodEnd);
    }
}
