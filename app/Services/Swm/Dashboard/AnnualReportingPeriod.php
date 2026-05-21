<?php

namespace App\Services\Swm\Dashboard;

use Carbon\Carbon;

final class AnnualReportingPeriod
{
    public readonly int $year;

    public readonly Carbon $yearStart;

    public readonly Carbon $yearEnd;

    public readonly int $daysInYear;

    public function __construct(int $year)
    {
        $this->year = $year;
        $this->yearStart = Carbon::create($year, 1, 1)->startOfDay();
        $this->yearEnd = Carbon::create($year, 12, 31)->endOfDay();
        $this->daysInYear = $this->yearStart->isLeapYear() ? 366 : 365;
    }

    public static function forYear(int $year): self
    {
        return new self($year);
    }

    public function yearEndDateString(): string
    {
        return $this->yearEnd->toDateString();
    }

    public function yearStartDateString(): string
    {
        return $this->yearStart->toDateString();
    }
}
