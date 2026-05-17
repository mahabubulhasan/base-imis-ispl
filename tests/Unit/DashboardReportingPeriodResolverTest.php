<?php

namespace Tests\Unit;

use App\Services\Swm\Dashboard\DashboardReportingPeriod;
use App\Services\Swm\Dashboard\DashboardReportingPeriodResolver;
use App\Services\Swm\Dashboard\ReportingWindow;
use Carbon\Carbon;
use Tests\TestCase;

class DashboardReportingPeriodResolverTest extends TestCase
{
    private DashboardReportingPeriodResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = app(DashboardReportingPeriodResolver::class);
    }

    public function test_resolves_to_month_and_period_end(): void
    {
        Carbon::setTestNow('2026-05-15 12:00:00');

        $period = $this->resolver->resolve('2026-03');

        $this->assertSame('2026-03', $period->toMonthString());
        $this->assertSame('2026-03-31', $period->periodEndDateString());
        $this->assertGreaterThanOrEqual(0, $period->reportingDays());

        Carbon::setTestNow();
    }

    public function test_defaults_to_previous_month_when_no_input(): void
    {
        Carbon::setTestNow('2026-05-15 12:00:00');

        $period = $this->resolver->resolve(null);

        $this->assertSame('2026-04', $period->toMonthString());
        $this->assertSame('2026-04-30', $period->periodEndDateString());

        Carbon::setTestNow();
    }

    public function test_clamps_current_and_future_months_to_latest_allowed(): void
    {
        Carbon::setTestNow('2026-05-15 12:00:00');

        $this->assertSame('2026-04', $this->resolver->resolve('2026-05')->toMonthString());
        $this->assertSame('2026-04', $this->resolver->resolve('2027-01')->toMonthString());

        Carbon::setTestNow();
    }

    public function test_latest_allowed_to_month_string(): void
    {
        Carbon::setTestNow('2026-05-15 12:00:00');

        $this->assertSame('2026-04', $this->resolver->latestAllowedToMonthString());

        Carbon::setTestNow();
    }

    public function test_exposes_three_independent_windows(): void
    {
        Carbon::setTestNow('2026-05-15 12:00:00');

        $period = $this->resolver->resolve('2026-04');

        $this->assertInstanceOf(ReportingWindow::class, $period->window(DashboardReportingPeriod::SOURCE_HOUSEHOLD));
        $this->assertInstanceOf(ReportingWindow::class, $period->window(DashboardReportingPeriod::SOURCE_LANDFILL));
        $this->assertInstanceOf(ReportingWindow::class, $period->window(DashboardReportingPeriod::SOURCE_COMPLAINT));
        $this->assertCount(3, $period->windows());

        Carbon::setTestNow();
    }

    public function test_reporting_days_delegates_to_household_window(): void
    {
        Carbon::setTestNow('2026-05-15 12:00:00');

        $period = $this->resolver->resolve('2026-04');

        $this->assertSame(
            $period->window(DashboardReportingPeriod::SOURCE_HOUSEHOLD)->days,
            $period->reportingDays(),
        );

        Carbon::setTestNow();
    }
}
