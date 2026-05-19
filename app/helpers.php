<?php

use App\Services\Formatting\CurrencyFormatter;

if (! function_exists('currency')) {
    function currency(
        int|float|string|null $amount,
        ?string $currencyCode = null,
        bool $groupThousands = true,
    ): string {
        $code = $currencyCode ?? config('formatting.default_currency', 'tk');

        return app(CurrencyFormatter::class)->format($code, $amount, $groupThousands);
    }
}

if (! function_exists('currency_input')) {
    function currency_input(
        int|float|string|null $amount,
        ?string $currencyCode = null,
    ): int|float|null {
        $code = $currencyCode ?? config('formatting.default_currency', 'tk');

        return app(CurrencyFormatter::class)->inputValue($code, $amount);
    }
}
