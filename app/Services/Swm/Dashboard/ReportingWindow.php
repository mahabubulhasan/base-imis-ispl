<?php

namespace App\Services\Swm\Dashboard;

use Carbon\Carbon;

final class ReportingWindow
{
    public function __construct(
        public readonly ?Carbon $epoch,
        public readonly Carbon $periodEnd,
        public readonly int $days,
    ) {
    }

    public static function between(Carbon $epoch, Carbon $periodEnd): self
    {
        $epoch = $epoch->copy()->startOfDay();
        $periodEnd = $periodEnd->copy();

        return new self(
            $epoch,
            $periodEnd,
            max(1, $epoch->diffInDays($periodEnd) + 1),
        );
    }

    public static function empty(Carbon $periodEnd): self
    {
        return new self(null, $periodEnd->copy(), 0);
    }

    public function hasData(): bool
    {
        return $this->days > 0;
    }
}
