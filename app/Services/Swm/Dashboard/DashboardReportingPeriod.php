<?php

namespace App\Services\Swm\Dashboard;

use Carbon\Carbon;
use InvalidArgumentException;

final class DashboardReportingPeriod
{
    public const SOURCE_HOUSEHOLD = 'household';

    public const SOURCE_LANDFILL = 'landfill';

    public const SOURCE_COMPLAINT = 'complaint';

    /** @param array<string, ReportingWindow> $windows */
    public function __construct(
        public readonly Carbon $toMonth,
        public readonly Carbon $periodEnd,
        private readonly array $windows,
    ) {
    }

    public function window(string $source): ReportingWindow
    {
        if (! isset($this->windows[$source])) {
            throw new InvalidArgumentException("Unknown reporting window source: {$source}");
        }

        return $this->windows[$source];
    }

    /** @return array<string, ReportingWindow> */
    public function windows(): array
    {
        return $this->windows;
    }

    public function reportingDays(): int
    {
        return $this->window(self::SOURCE_HOUSEHOLD)->days;
    }

    public function toMonthString(): string
    {
        return $this->toMonth->format('Y-m');
    }

    public function periodEndDateString(): string
    {
        return $this->periodEnd->toDateString();
    }
}
