<?php

namespace App\Services\Formatting;

use InvalidArgumentException;

final class CurrencyFormatter
{
    public function format(
        string $currency,
        int|float|string|null $amount,
        bool $groupThousands = true,
    ): string {
        if ($amount === null || $amount === '') {
            return $this->config($currency)['empty'];
        }

        $normalized = is_string($amount) ? str_replace(',', '', $amount) : $amount;
        $value = (float) $normalized;

        $config = $this->config($currency);
        $thousandsSeparator = $groupThousands ? $config['thousands_separator'] : '';

        return number_format(
            $value,
            $config['decimals'],
            $config['decimal_separator'],
            $thousandsSeparator,
        );
    }

    public function formatOrDash(
        string $currency,
        int|float|string|null $amount,
        bool $groupThousands = true,
    ): string {
        if ($amount === null || $amount === '') {
            return $this->config($currency)['missing'];
        }

        return $this->format($currency, $amount, $groupThousands);
    }

    public function zero(string $currency): string
    {
        return $this->format($currency, 0);
    }

    /**
     * Value suitable for HTML number inputs (no thousands separators, currency decimal rules).
     */
    public function inputValue(string $currency, int|float|string|null $amount): int|float|null
    {
        if ($amount === null || $amount === '') {
            return null;
        }

        $normalized = is_string($amount) ? str_replace(',', '', $amount) : $amount;
        $value = (float) $normalized;
        $decimals = $this->config($currency)['decimals'];

        if ($decimals === 0) {
            return (int) round($value);
        }

        return round($value, $decimals);
    }

    /**
     * @return array{decimals: int, decimal_separator: string, thousands_separator: string, empty: string, missing: string}
     */
    protected function config(string $currency): array
    {
        $currencies = config('formatting.currencies', []);

        if (! isset($currencies[$currency])) {
            throw new InvalidArgumentException("Unknown currency code: {$currency}");
        }

        return $currencies[$currency];
    }
}
