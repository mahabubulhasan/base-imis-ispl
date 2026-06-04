<?php

namespace App\Services\Swm\Dashboard\Complaints;

use App\Services\Swm\Dashboard\DashboardReportingPeriod;
use App\Services\Swm\Dashboard\ReportingWindow;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class ComplaintsDashboardMetrics
{
    /**
     * @return array{
     *     total: int,
     *     status_resolved: int,
     *     status_pending: int,
     *     status_others: int,
     *     duplicate_yes: int,
     *     avg_resolution_days: float|null,
     *     by_type: array<string, int>,
     *     by_ward: array<string, int>,
     *     status_by_ward: array<string, array{resolved: int, pending: int, others: int}>,
     *     by_channel: array<string, int>,
     *     avg_resolution_by_type: array<string, float>,
     *     trend_12m: array<string, int>,
     *     heatmap_wards: list<string>,
     *     heatmap_rows: list<array{row_key: string, row_label: string, values: list<int>}>
     * }
     */
    public function aggregate(DashboardReportingPeriod $period): array
    {
        $window = $period->window(DashboardReportingPeriod::SOURCE_COMPLAINT);
        if (! $window->hasData()) {
            return $this->emptyAggregate($period);
        }

        $base = $this->baseComplaintQuery($window);

        $total = (int) (clone $base)->count();

        $statusRow = (clone $base)
            ->selectRaw("
                SUM(CASE WHEN complaint_status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                SUM(CASE WHEN complaint_status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN complaint_status NOT IN ('resolved', 'pending') OR complaint_status IS NULL THEN 1 ELSE 0 END) as others
            ")
            ->first();

        $duplicateYes = (int) (clone $base)->where('duplicate_complaint', true)->count();

        $avgResolution = (clone $base)
            ->whereNotNull('resolution_time_days')
            ->avg('resolution_time_days');

        $byType = $this->countsGrouped(clone $base, 'complaint_type');
        $byWard = $this->countsGrouped(clone $base, 'ward_no', true);
        $statusByWard = $this->complaintStatusByWard($window);
        $byChannel = $this->countsGrouped(clone $base, 'submitted_through');

        $avgByType = $this->avgResolutionByType($window);

        $trend12m = $this->complaintTrendLast12Months($period, $window);

        $heatmap = $this->complaintTypeByWardHeatmap($window);

        return [
            'total' => $total,
            'status_resolved' => (int) ($statusRow->resolved ?? 0),
            'status_pending' => (int) ($statusRow->pending ?? 0),
            'status_others' => (int) ($statusRow->others ?? 0),
            'duplicate_yes' => $duplicateYes,
            'avg_resolution_days' => $avgResolution !== null ? (float) $avgResolution : null,
            'by_type' => $byType,
            'by_ward' => $byWard,
            'status_by_ward' => $statusByWard,
            'by_channel' => $byChannel,
            'avg_resolution_by_type' => $avgByType,
            'trend_12m' => $trend12m,
            'heatmap_wards' => $heatmap['wards'],
            'heatmap_rows' => $heatmap['rows'],
        ];
    }

    /**
     * @return array<string, int>
     */
    public function complaintTrendLast12Months(DashboardReportingPeriod $period, ReportingWindow $window): array
    {
        $endMonth = $period->periodEnd->copy()->startOfMonth();
        $startMonth = $endMonth->copy()->subMonths(11);

        $out = [];
        for ($m = $startMonth->copy(); $m->lte($endMonth); $m->addMonth()) {
            $out[$m->format('Y-m-01')] = 0;
        }

        if (! $window->hasData() || $window->epoch === null) {
            return $out;
        }

        $rangeStart = $startMonth->copy()->startOfDay();
        $rangeStart = $rangeStart->max($window->epoch->copy()->startOfDay());

        $rows = DB::table('swm.complaints')
            ->whereNull('deleted_at')
            ->where('date_time', '>=', $rangeStart)
            ->where('date_time', '<=', $period->periodEnd)
            ->selectRaw("
                date_trunc('month', date_time)::date as month_key,
                COUNT(*)::int as cnt
            ")
            ->groupByRaw('date_trunc(\'month\', date_time)')
            ->orderByRaw('date_trunc(\'month\', date_time)')
            ->get();

        foreach ($rows as $row) {
            $key = Carbon::parse($row->month_key)->format('Y-m-01');
            if (array_key_exists($key, $out)) {
                $out[$key] = (int) $row->cnt;
            }
        }

        return $out;
    }

    /**
     * @return array<string, array{resolved: int, pending: int, others: int}>
     */
    protected function complaintStatusByWard(ReportingWindow $window): array
    {
        $wardKeySql = "
            CASE
                WHEN ward_no IS NULL OR TRIM(COALESCE(ward_no, '')) = '' THEN '__unknown__'
                ELSE TRIM(ward_no)
            END
        ";

        $rows = DB::table('swm.complaints')
            ->whereNull('deleted_at')
            ->where('date_time', '>=', $window->epoch)
            ->where('date_time', '<=', $window->periodEnd)
            ->selectRaw("{$wardKeySql} as ward_key")
            ->selectRaw("
                SUM(CASE WHEN complaint_status = 'resolved' THEN 1 ELSE 0 END)::int as resolved,
                SUM(CASE WHEN complaint_status = 'pending' THEN 1 ELSE 0 END)::int as pending,
                SUM(CASE WHEN complaint_status NOT IN ('resolved', 'pending') OR complaint_status IS NULL THEN 1 ELSE 0 END)::int as others
            ")
            ->groupBy(DB::raw($wardKeySql))
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $out[(string) $row->ward_key] = [
                'resolved' => (int) $row->resolved,
                'pending' => (int) $row->pending,
                'others' => (int) $row->others,
            ];
        }

        return $out;
    }

    /**
     * @return array{wards: list<string>, rows: list<array{row_key: string, row_label: string, values: list<int>}>}
     */
    protected function complaintTypeByWardHeatmap(ReportingWindow $window): array
    {
        $rows = DB::table('swm.complaints')
            ->whereNull('deleted_at')
            ->where('date_time', '>=', $window->epoch)
            ->where('date_time', '<=', $window->periodEnd)
            ->selectRaw('complaint_type as type_key')
            ->selectRaw("
                CASE
                    WHEN ward_no IS NULL OR TRIM(COALESCE(ward_no, '')) = '' THEN '__unknown__'
                    ELSE TRIM(ward_no)
                END as ward_key
            ")
            ->selectRaw('COUNT(*)::int as cnt')
            ->groupBy('complaint_type', DB::raw("
                CASE
                    WHEN ward_no IS NULL OR TRIM(COALESCE(ward_no, '')) = '' THEN '__unknown__'
                    ELSE TRIM(ward_no)
                END
            "))
            ->get();

        $wardSet = [];
        $typeSet = [];
        $matrix = [];
        foreach ($rows as $row) {
            $t = (string) $row->type_key;
            $w = (string) $row->ward_key;
            $typeSet[$t] = true;
            $wardSet[$w] = true;
            $matrix[$t][$w] = (int) $row->cnt;
        }

        $wards = array_keys($wardSet);
        usort($wards, static function (string $a, string $b): int {
            if ($a === '__unknown__') {
                return 1;
            }
            if ($b === '__unknown__') {
                return -1;
            }

            return strnatcasecmp($a, $b);
        });

        $types = array_keys($typeSet);
        usort($types, static fn (string $a, string $b) => strnatcasecmp($a, $b));

        $heatmapRows = [];
        foreach ($types as $typeKey) {
            $values = [];
            foreach ($wards as $wk) {
                $values[] = (int) ($matrix[$typeKey][$wk] ?? 0);
            }
            $heatmapRows[] = [
                'row_key' => $typeKey,
                'row_label' => $this->complaintTypeLabel($typeKey),
                'values' => $values,
            ];
        }

        $wardLabels = array_map(fn (string $wk) => $this->wardDisplayLabel($wk), $wards);

        return [
            'wards' => $wardLabels,
            'rows' => $heatmapRows,
        ];
    }

    /**
     * Average resolution time in days, grouped by complaint type (only rows with non-null resolution_time_days contribute to the average).
     *
     * @return array<string, float>
     */
    protected function avgResolutionByType(ReportingWindow $window): array
    {
        $rows = DB::table('swm.complaints')
            ->whereNull('deleted_at')
            ->where('date_time', '>=', $window->epoch)
            ->where('date_time', '<=', $window->periodEnd)
            ->whereNotNull('resolution_time_days')
            ->selectRaw('complaint_type as type_key, AVG(resolution_time_days)::float as avg_days')
            ->groupBy('complaint_type')
            ->havingRaw('AVG(resolution_time_days) IS NOT NULL')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $out[(string) $row->type_key] = round((float) $row->avg_days, 2);
        }

        uasort($out, static fn (float $a, float $b) => $b <=> $a);

        return $out;
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    protected function baseComplaintQuery(ReportingWindow $window)
    {
        return DB::table('swm.complaints')
            ->whereNull('deleted_at')
            ->where('date_time', '>=', $window->epoch)
            ->where('date_time', '<=', $window->periodEnd);
    }

    /**
     * @return array<string, int>
     */
    protected function countsGrouped(
        \Illuminate\Database\Query\Builder $query,
        string $column,
        bool $normalizeWard = false
    ): array {
        if ($normalizeWard) {
            $rows = (clone $query)
                ->selectRaw("
                    CASE
                        WHEN {$column} IS NULL OR TRIM(COALESCE({$column}, '')) = '' THEN '__unknown__'
                        ELSE TRIM({$column})
                    END as g
                ")
                ->selectRaw('COUNT(*)::int as cnt')
                ->groupBy(DB::raw("
                    CASE
                        WHEN {$column} IS NULL OR TRIM(COALESCE({$column}, '')) = '' THEN '__unknown__'
                        ELSE TRIM({$column})
                    END
                "))
                ->orderBy('g')
                ->get();
        } else {
            $rows = (clone $query)
                ->selectRaw("
                    CASE
                        WHEN {$column} IS NULL OR TRIM(COALESCE({$column}, '')) = '' THEN '__unknown__'
                        ELSE TRIM({$column})
                    END as g
                ")
                ->selectRaw('COUNT(*)::int as cnt')
                ->groupBy(DB::raw("
                    CASE
                        WHEN {$column} IS NULL OR TRIM(COALESCE({$column}, '')) = '' THEN '__unknown__'
                        ELSE TRIM({$column})
                    END
                "))
                ->orderBy('g')
                ->get();
        }

        $out = [];
        foreach ($rows as $row) {
            $key = (string) $row->g;
            $out[$key] = (int) $row->cnt;
        }

        return $out;
    }

    /**
     * @param  array<string, int>  $trend12m
     * @param  list<array{row_key: string, row_label: string, values: list<int>}>  $heatmap_rows
     * @return array{
     *     total: int,
     *     status_resolved: int,
     *     status_pending: int,
     *     status_others: int,
     *     duplicate_yes: int,
     *     avg_resolution_days: float|null,
     *     by_type: array<string, int>,
     *     by_ward: array<string, int>,
     *     status_by_ward: array<string, array{resolved: int, pending: int, others: int}>,
     *     by_channel: array<string, int>,
     *     avg_resolution_by_type: array<string, float>,
     *     trend_12m: array<string, int>,
     *     heatmap_wards: list<string>,
     *     heatmap_rows: list<array{row_key: string, row_label: string, values: list<int>}>
     * }
     */
    protected function emptyAggregate(DashboardReportingPeriod $period): array
    {
        $window = $period->window(DashboardReportingPeriod::SOURCE_COMPLAINT);
        $trend = $this->complaintTrendLast12Months($period, $window);

        return [
            'total' => 0,
            'status_resolved' => 0,
            'status_pending' => 0,
            'status_others' => 0,
            'duplicate_yes' => 0,
            'avg_resolution_days' => null,
            'by_type' => [],
            'by_ward' => [],
            'status_by_ward' => [],
            'by_channel' => [],
            'avg_resolution_by_type' => [],
            'trend_12m' => $trend,
            'heatmap_wards' => [],
            'heatmap_rows' => [],
        ];
    }

    public function complaintTypeLabel(string $key): string
    {
        $labels = config('swm_complaints.complaint_types', []);

        return __($labels[$key] ?? $key);
    }

    public function complaintChannelLabel(string $key): string
    {
        $labels = config('swm_complaints.submitted_through', []);

        return __($labels[$key] ?? $key);
    }

    protected function wardDisplayLabel(string $wardKey): string
    {
        if ($wardKey === '__unknown__') {
            return __('Unknown');
        }

        return $wardKey;
    }
}
