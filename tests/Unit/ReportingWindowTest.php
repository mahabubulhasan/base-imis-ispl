<?php

namespace Tests\Unit;

use App\Services\Swm\Dashboard\ReportingWindow;
use Carbon\Carbon;
use Tests\TestCase;

class ReportingWindowTest extends TestCase
{
    public function test_between_counts_inclusive_days_may_13_through_may_31(): void
    {
        $epoch = Carbon::parse('2026-05-13')->startOfDay();
        $periodEnd = Carbon::parse('2026-05-31')->endOfDay();

        $window = ReportingWindow::between($epoch, $periodEnd);

        $this->assertSame(19, $window->days);
        $this->assertTrue($window->hasData());
        $this->assertSame('2026-05-13', $window->epoch->toDateString());
    }

    public function test_empty_window_has_zero_days_and_no_data(): void
    {
        $periodEnd = Carbon::parse('2026-05-31')->endOfDay();

        $window = ReportingWindow::empty($periodEnd);

        $this->assertSame(0, $window->days);
        $this->assertFalse($window->hasData());
        $this->assertNull($window->epoch);
    }
}
