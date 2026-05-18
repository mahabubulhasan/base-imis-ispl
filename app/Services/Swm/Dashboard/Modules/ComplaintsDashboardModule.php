<?php

namespace App\Services\Swm\Dashboard\Modules;

use App\Services\Swm\Dashboard\Complaints\ComplaintsDashboardMetrics;
use App\Services\Swm\Dashboard\Contracts\SwmDashboardModuleInterface;
use App\Services\Swm\Dashboard\DashboardReportingPeriod;
use App\Services\Swm\Dashboard\SwmDashboardFormatter;
use Carbon\Carbon;

class ComplaintsDashboardModule implements SwmDashboardModuleInterface
{
    public function __construct(
        protected SwmDashboardFormatter $formatter,
        protected ComplaintsDashboardMetrics $metrics,
    ) {}

    public function key(): string
    {
        return 'complaints';
    }

    public function label(): string
    {
        return __('Complaints');
    }

    public function permission(): ?string
    {
        return null;
    }

    public function build(DashboardReportingPeriod $period): array
    {
        return [
            'submodules' => [
                $this->overviewSubmodule($period),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function overviewSubmodule(DashboardReportingPeriod $period): array
    {
        $agg = $this->metrics->aggregate($period);

        return [
            'key' => 'overview',
            'title' => __('Complaints'),
            'blocks' => [
                [
                    'type' => 'tiles',
                    'items' => $this->tileItems($agg),
                ],
                [
                    'type' => 'kpis',
                    'subsection' => __('Key Performance Indicators'),
                    'items' => $this->kpiItems($agg),
                ],
                [
                    'type' => 'charts',
                    'subsection' => __('Visualizations'),
                    'items' => [
                        $this->complaintsByTypeChart($agg),
                        $this->complaintsByWardChart($agg),
                        $this->complaintChannelChart($agg),
                        $this->resolutionTimeByTypeChart($agg),
                        $this->complaintTypeByWardHeatmapChart($agg),
                        $this->complaintTrendChart($agg),
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $agg
     * @return list<array<string, string>>
     */
    protected function tileItems(array $agg): array
    {
        $total = (int) ($agg['total'] ?? 0);
        $avgDays = $agg['avg_resolution_days'];

        return [
            [
                'label' => __('Total Complaints Received'),
                'value' => $this->formatter->integer($total),
                'icon' => 'fa-inbox',
            ],
            [
                'label' => __('Resolved Complaints'),
                'value' => $this->formatter->integer($agg['status_resolved'] ?? 0),
                'icon' => 'fa-circle-check',
            ],
            [
                'label' => __('Pending Complaints'),
                'value' => $this->formatter->integer($agg['status_pending'] ?? 0),
                'icon' => 'fa-clock',
            ],
            [
                'label' => __('Others Complaints'),
                'value' => $this->formatter->integer($agg['status_others'] ?? 0),
                'icon' => 'fa-ellipsis',
            ],
            [
                'label' => __('Average Resolution Time (Days)'),
                'value' => $this->formatter->integer(
                    $avgDays !== null ? (int) round((float) $avgDays) : 0,
                ),
                'icon' => 'fa-calendar-day',
            ],
            [
                'label' => __('Duplicate-Complaint Rate'),
                'value' => $this->formatter->percent(
                    $total > 0 ? ((float) ($agg['duplicate_yes'] ?? 0) / (float) $total) * 100 : 0.0,
                ),
                'icon' => 'fa-copy',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $agg
     * @return list<array<string, mixed>>
     */
    protected function kpiItems(array $agg): array
    {
        $total = (int) ($agg['total'] ?? 0);
        $resolved = (int) ($agg['status_resolved'] ?? 0);
        $pct = $total > 0 ? ((float) $resolved / (float) $total) * 100 : 0.0;

        return [
            [
                'name' => __('Complaint Resolution'),
                'value' => $this->formatter->percentValue($pct),
                'unit' => '%',
                'showFrequency' => true,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $agg
     * @return array<string, mixed>
     */
    protected function complaintsByTypeChart(array $agg): array
    {
        $byType = $agg['by_type'] ?? [];
        ksort($byType, SORT_NATURAL);
        $labels = [];
        $data = [];
        foreach ($byType as $key => $cnt) {
            $labels[] = $this->metrics->complaintTypeLabel((string) $key);
            $data[] = (int) $cnt;
        }

        return [
            'id' => 'swmChartComplaintsByType',
            'type' => 'bar',
            'title' => __('Complaints by Type'),
            'labels' => $labels,
            'datasets' => [
                ['data' => $data],
            ],
            'options' => [
                'unitX' => __('Complaint Type'),
                'integerYTicks' => true,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $agg
     * @return array<string, mixed>
     */
    protected function complaintsByWardChart(array $agg): array
    {
        $byWard = $agg['by_ward'] ?? [];
        uksort($byWard, static function (string $a, string $b): int {
            if ($a === '__unknown__') {
                return 1;
            }
            if ($b === '__unknown__') {
                return -1;
            }

            return strnatcasecmp($a, $b);
        });
        $labels = [];
        $data = [];
        foreach ($byWard as $key => $cnt) {
            $labels[] = $key === '__unknown__' ? __('Unknown') : (string) $key;
            $data[] = (int) $cnt;
        }

        return [
            'id' => 'swmChartComplaintsByWard',
            'type' => 'bar',
            'title' => __('Complaints by Ward'),
            'labels' => $labels,
            'datasets' => [
                ['data' => $data],
            ],
            'options' => [
                'unitX' => __('Ward'),
                'integerYTicks' => true,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $agg
     * @return array<string, mixed>
     */
    protected function complaintChannelChart(array $agg): array
    {
        $byChannel = $agg['by_channel'] ?? [];
        $channels = config('swm_complaints.submitted_through', []);

        $labels = [];
        $data = [];
        foreach ($channels as $key => $_label) {
            $cnt = (int) ($byChannel[$key] ?? 0);
            if ($cnt <= 0) {
                continue;
            }
            $labels[] = $this->metrics->complaintChannelLabel((string) $key);
            $data[] = $cnt;
        }

        foreach ($byChannel as $key => $cnt) {
            if (isset($channels[$key]) || (int) $cnt <= 0) {
                continue;
            }
            $labels[] = (string) $key;
            $data[] = (int) $cnt;
        }

        if ($labels === []) {
            $labels = [__('No data')];
            $data = [0];
        }

        return [
            'id' => 'swmChartComplaintsChannel',
            'type' => 'doughnut',
            'title' => __('Complaint Channel'),
            'labels' => $labels,
            'datasets' => [
                ['data' => $data],
            ],
            'options' => [],
        ];
    }

    /**
     * @param  array<string, mixed>  $agg
     * @return array<string, mixed>
     */
    protected function resolutionTimeByTypeChart(array $agg): array
    {
        $byType = $agg['avg_resolution_by_type'] ?? [];
        $labels = [];
        $data = [];
        foreach ($byType as $typeKey => $avgDays) {
            $labels[] = $this->metrics->complaintTypeLabel((string) $typeKey);
            $data[] = (int) round((float) $avgDays);
        }

        return [
            'id' => 'swmChartComplaintsResolutionByType',
            'type' => 'horizontalBar',
            'title' => __('Resolution Time by Complaint Type'),
            'labels' => $labels,
            'datasets' => [
                ['data' => $data],
            ],
            'options' => [
                'unitX' => __('Days'),
                'integerXTicks' => true,
            ],
            'height' => max(280, count($labels) * 36),
        ];
    }

    /**
     * @param  array<string, mixed>  $agg
     * @return array<string, mixed>
     */
    protected function complaintTypeByWardHeatmapChart(array $agg): array
    {
        $wards = $agg['heatmap_wards'] ?? [];
        $rows = $agg['heatmap_rows'] ?? [];

        $heatmapRows = [];
        foreach ($rows as $row) {
            $heatmapRows[] = [
                'rowLabel' => (string) ($row['row_label'] ?? ''),
                'values' => array_map(static fn ($v) => (int) $v, $row['values'] ?? []),
            ];
        }

        return [
            'id' => 'swmChartComplaintsTypeByWardHeatmap',
            'type' => 'heatmap',
            'title' => __('Complaint Type by Ward'),
            'wards' => $wards,
            'heatmapRows' => $heatmapRows,
            'options' => [
                'unit' => '',
                'valueDisplay' => 'count',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $agg
     * @return array<string, mixed>
     */
    protected function complaintTrendChart(array $agg): array
    {
        $trend = $agg['trend_12m'] ?? [];

        $labels = [];
        $values = [];
        foreach ($trend as $monthKey => $cnt) {
            $labels[] = Carbon::parse($monthKey)->format('M Y');
            $values[] = (int) $cnt;
        }

        return [
            'id' => 'swmChartComplaintsTrend12m',
            'type' => 'line',
            'title' => __('Complaint Trend (Last 12 Months)'),
            'labels' => $labels,
            'datasets' => [
                ['data' => $values],
            ],
            'options' => [
                'unitX' => __('Month'),
                'integerYTicks' => true,
            ],
        ];
    }
}
