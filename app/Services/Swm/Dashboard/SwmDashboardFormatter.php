<?php

namespace App\Services\Swm\Dashboard;

use App\Services\Formatting\Currency;
use App\Services\Formatting\CurrencyFormatter;

final class SwmDashboardFormatter
{
    public function __construct(
        protected CurrencyFormatter $currency,
    ) {
    }

    public function integer(int|float|null $value): string
    {
        return $this->currency->format(Currency::TK, $value);
    }

    public function decimal(int|float|null $value, int $decimals = 2): string
    {
        return number_format((float) ($value ?? 0), $decimals, '.', ',');
    }

    public function percent(int|float|null $value, int $decimals = 1): string
    {
        return $this->decimal($value, $decimals).' %';
    }

    public function percentValue(int|float|null $value, int $decimals = 1): string
    {
        return $this->decimal($value, $decimals);
    }

    public function safePercent(float $numerator, float $denominator, int $decimals = 1): string
    {
        if ($denominator <= 0) {
            return $this->decimal(0, $decimals);
        }

        return $this->decimal(($numerator / $denominator) * 100, $decimals);
    }
}
