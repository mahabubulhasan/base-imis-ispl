<?php

namespace App\Services\Swm\Dashboard;

use App\Models\BuildingInfo\Household;
use App\Models\Swm\Complaint;
use App\Models\Swm\LandfillLog;
use Carbon\Carbon;

class DashboardReportingPeriodResolver
{
    public function resolve(?string $toMonthInput): DashboardReportingPeriod
    {
        $toMonth = $this->parseToMonth($toMonthInput);
        $periodEnd = $toMonth->copy()->endOfMonth()->endOfDay();

        return new DashboardReportingPeriod($toMonth, $periodEnd, [
            DashboardReportingPeriod::SOURCE_HOUSEHOLD => $this->resolveWindow(
                $periodEnd,
                Household::query()->min('created_at'),
            ),
            DashboardReportingPeriod::SOURCE_LANDFILL => $this->resolveWindow(
                $periodEnd,
                LandfillLog::query()->min('operation_date'),
            ),
            DashboardReportingPeriod::SOURCE_COMPLAINT => $this->resolveWindow(
                $periodEnd,
                Complaint::query()->min('date_time'),
            ),
        ]);
    }

    public function latestAllowedToMonth(): Carbon
    {
        return now()->subMonth()->startOfMonth();
    }

    public function latestAllowedToMonthString(): string
    {
        return $this->latestAllowedToMonth()->format('Y-m');
    }

    public function clampToMonth(Carbon $month): Carbon
    {
        $month = $month->copy()->startOfMonth();
        $latest = $this->latestAllowedToMonth();

        if ($month->gt($latest)) {
            return $latest->copy();
        }

        return $month;
    }

    protected function parseToMonth(?string $toMonthInput): Carbon
    {
        if ($toMonthInput && preg_match('/^\d{4}-\d{2}$/', $toMonthInput)) {
            return $this->clampToMonth(Carbon::createFromFormat('Y-m', $toMonthInput));
        }

        $default = config('swm_dashboard.default_to_month');

        if ($default && preg_match('/^\d{4}-\d{2}$/', $default)) {
            return $this->clampToMonth(Carbon::createFromFormat('Y-m', $default));
        }

        return $this->latestAllowedToMonth();
    }

    protected function resolveWindow(Carbon $periodEnd, mixed $minDate): ReportingWindow
    {
        if ($minDate === null) {
            return ReportingWindow::empty($periodEnd);
        }

        return ReportingWindow::between(Carbon::parse($minDate), $periodEnd);
    }
}
