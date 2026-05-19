<?php

namespace Tests\Unit;

use App\Services\Formatting\Currency;
use App\Services\Formatting\CurrencyFormatter;
use InvalidArgumentException;
use Tests\TestCase;

class CurrencyFormatterTest extends TestCase
{
    public function test_formats_tk_with_thousands_separator(): void
    {
        $formatter = new CurrencyFormatter();

        $this->assertSame('1,234', $formatter->format(Currency::TK, 1234.6));
    }

    public function test_formats_tk_without_thousands_separator(): void
    {
        $formatter = new CurrencyFormatter();

        $this->assertSame('1234', $formatter->format(Currency::TK, '1,234.00', false));
    }

    public function test_null_amount_returns_empty_string(): void
    {
        $formatter = new CurrencyFormatter();

        $this->assertSame('', $formatter->format(Currency::TK, null));
    }

    public function test_format_or_dash_returns_missing_for_null(): void
    {
        $formatter = new CurrencyFormatter();

        $this->assertSame('—', $formatter->formatOrDash(Currency::TK, null));
    }

    public function test_zero_returns_formatted_zero(): void
    {
        $formatter = new CurrencyFormatter();

        $this->assertSame('0', $formatter->zero(Currency::TK));
    }

    public function test_input_value_rounds_to_whole_number_for_tk(): void
    {
        $formatter = new CurrencyFormatter();

        $this->assertSame(250, $formatter->inputValue(Currency::TK, '250.00'));
        $this->assertSame(250, $formatter->inputValue(Currency::TK, 250.6));
        $this->assertNull($formatter->inputValue(Currency::TK, null));
    }

    public function test_unknown_currency_throws(): void
    {
        $formatter = new CurrencyFormatter();

        $this->expectException(InvalidArgumentException::class);
        $formatter->format('usd', 100);
    }
}
