<?php

namespace Tests\Unit;

use App\Services\Swm\Dashboard\SwmDashboardFormatter;
use Tests\TestCase;

class SwmDashboardFormatterTest extends TestCase
{
    public function test_integer_formatting(): void
    {
        $f = new SwmDashboardFormatter();
        $this->assertSame('1,234', $f->integer(1234.6));
    }

    public function test_decimal_and_percent(): void
    {
        $f = new SwmDashboardFormatter();
        $this->assertSame('42.80', $f->decimal(42.8));
        $this->assertSame('52.5 %', $f->percent(52.5));
    }

    public function test_safe_percent_handles_zero_denominator(): void
    {
        $f = new SwmDashboardFormatter();
        $this->assertSame('0.0', $f->safePercent(10, 0));
    }
}
