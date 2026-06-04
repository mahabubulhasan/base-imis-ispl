<?php

namespace App\Services\Swm\Dashboard\Modules;

use App\Services\Swm\Dashboard\Concerns\BuildsCountChartAxisLabels;
use App\Services\Swm\Dashboard\Complaints\ComplaintsDashboardMetrics;
use App\Services\Swm\Dashboard\Contracts\SwmDashboardModuleInterface;
use App\Services\Swm\Dashboard\DashboardReportingPeriod;
use App\Services\Swm\Dashboard\SwmDashboardFormatter;
use Carbon\Carbon;

class ComplaintsDashboardModule implements SwmDashboardModuleInterface
{
    use BuildsCountChartAxisLabels;

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
                    'type' => 'charts',
                    'subsection' => __('Visualizations'),
                    'items' => [
                        $this->complaintsByTypeChart($agg),
                        $this->complaintsByWardChart($agg),
                        $this->complaintStatusByWardChart($agg),
                        $this->complaintTypeByWardChart($agg),
                        $this->complaintChannelChart($agg),
                        $this->resolutionTimeByTypeChart($agg),
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
        $resolved = (int) ($agg['status_resolved'] ?? 0);
        $resolutionPct = $total > 0 ? ((float) $resolved / (float) $total) * 100 : 0.0;
        $avgDays = $agg['avg_resolution_days'];

        return [
            [
                'label' => __('Complaint Resolution'),
                'value' => $this->formatter->percent($resolutionPct),
                'icon' => 'fa-percent',
            ],
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
     * @return array<string, mixed>
     */
    protected function complaintsByTypeChart(array $agg): array
    {
        $byType = [];
        foreach ($agg['by_type'] ?? [] as $key => $cnt) {
            $byType[(string) $key] = (int) $cnt;
        }
        $typeKeys = array_keys(config('swm_complaints.complaint_types', []));
        $aligned = $this->alignCountsToCategoryAxis(
            $byType,
            $typeKeys,
            fn (string $key) => $this->metrics->complaintTypeLabel($key),
        );

        return [
            'id' => 'swmChartComplaintsByType',
            'type' => 'bar',
            'title' => __('Complaints by Type'),
            'labels' => $aligned['labels'],
            'datasets' => [
                ['data' => array_map(static fn ($v) => (int) $v, $aligned['values'])],
            ],
            'options' => $this->staticCategoryChartOptions(
                __('Complaint Type'),
                $this->countChartAxisY(__('Complaints')),
                ['integerYTicks' => true],
            ),
        ];
    }

    /**
     * @param  array<string, mixed>  $agg
     * @return array<string, mixed>
     */
    protected function complaintsByWardChart(array $agg): array
    {
        $byWard = [];
        foreach ($agg['by_ward'] ?? [] as $key => $cnt) {
            $byWard[(string) $key] = (int) $cnt;
        }

        $aligned = $this->alignCountsToWardAxis($byWard, appendUnknown: true);

        return [
            'id' => 'swmChartComplaintsByWard',
            'type' => 'bar',
            'title' => __('Complaints by Ward'),
            'labels' => $aligned['labels'],
            'datasets' => [
                ['data' => array_map(static fn ($v) => (int) $v, $aligned['values'])],
            ],
            'options' => $this->staticCategoryChartOptions(
                __('Ward'),
                $this->countChartAxisY(__('Complaints')),
                ['integerYTicks' => true],
            ),
        ];
    }

    /**
     * @param  array<string, mixed>  $agg
     * @return array<string, mixed>
     */
    protected function complaintStatusByWardChart(array $agg): array
    {
        $aligned = $this->alignStackedSeriesToWardAxis(
            $agg['status_by_ward'] ?? [],
            ['resolved', 'pending', 'others'],
            fn (string $key) => match ($key) {
                'resolved' => __('Resolved'),
                'pending' => __('Pending'),
                default => __('Others'),
            },
            appendUnknown: true,
        );

        return [
            'id' => 'swmChartComplaintsStatusByWard',
            'type' => 'stackedBar',
            'title' => __('Complaint Status by Ward'),
            'labels' => $aligned['labels'],
            'datasets' => array_map(
                static fn (array $dataset) => [
                    'label' => $dataset['label'],
                    'data' => array_map(static fn ($v) => (int) $v, $dataset['data']),
                ],
                $aligned['datasets'],
            ),
            'options' => $this->staticCategoryChartOptions(
                __('Ward'),
                $this->countChartAxisY(__('Complaints')),
                ['integerYTicks' => true],
            ),
            'fullWidth' => false,
            'height' => 280,
        ];
    }

    /**
     * @param  array<string, mixed>  $agg
     * @return array<string, mixed>
     */
    protected function complaintTypeByWardChart(array $agg): array
    {
        $wardLabels = $agg['heatmap_wards'] ?? [];
        $rows = $agg['heatmap_rows'] ?? [];

        $datasets = [];
        foreach ($rows as $row) {
            $datasets[] = [
                'label' => (string) ($row['row_label'] ?? ''),
                'data' => array_map(static fn ($v) => (int) $v, $row['values'] ?? []),
            ];
        }

        return [
            'id' => 'swmChartComplaintsTypeByWard',
            'type' => 'stackedBar',
            'title' => __('Complaint Type by Ward'),
            'labels' => $wardLabels,
            'datasets' => $datasets,
            'options' => $this->staticCategoryChartOptions(
                __('Ward'),
                $this->countChartAxisY(__('Complaints')),
                ['integerYTicks' => true],
            ),
            'fullWidth' => false,
            'height' => 280,
        ];
    }

    /**
     * @param  array<string, mixed>  $agg
     * @return array<string, mixed>
     */
    protected function complaintChannelChart(array $agg): array
    {
        $byChannel = [];
        foreach ($agg['by_channel'] ?? [] as $key => $cnt) {
            $byChannel[(string) $key] = (int) $cnt;
        }
        $channelKeys = array_keys(config('swm_complaints.submitted_through', []));
        $aligned = $this->alignCountsToCategoryAxis(
            $byChannel,
            $channelKeys,
            fn (string $key) => $this->metrics->complaintChannelLabel($key),
        );

        return [
            'id' => 'swmChartComplaintsChannel',
            'type' => 'doughnut',
            'title' => __('Complaint Channel'),
            'labels' => $aligned['labels'],
            'datasets' => [
                ['data' => array_map(static fn ($v) => (int) $v, $aligned['values'])],
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
        $byType = [];
        foreach ($agg['avg_resolution_by_type'] ?? [] as $typeKey => $avgDays) {
            $byType[(string) $typeKey] = (int) round((float) $avgDays);
        }
        $typeKeys = array_keys(config('swm_complaints.complaint_types', []));
        $aligned = $this->alignCountsToCategoryAxis(
            $byType,
            $typeKeys,
            fn (string $key) => $this->metrics->complaintTypeLabel($key),
        );

        return [
            'id' => 'swmChartComplaintsResolutionByType',
            'type' => 'horizontalBar',
            'title' => __('Resolution Time by Complaint Type'),
            'labels' => $aligned['labels'],
            'datasets' => [
                ['data' => array_map(static fn ($v) => (int) $v, $aligned['values'])],
            ],
            'options' => [
                'unitX' => __('Days'),
                'integerXTicks' => true,
                'staticCategoryAxis' => true,
                'categoryAxisDimension' => 'y',
            ],
            'height' => max(280, count($aligned['labels']) * 36),
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
            'title' => __('Complaint Trend'),
            'labels' => $labels,
            'datasets' => [
                ['data' => $values],
            ],
            'options' => [
                'unitX' => __('Month'),
                'unitY' => $this->countChartAxisY(__('Complaints')),
                'integerYTicks' => true,
            ],
        ];
    }
}
