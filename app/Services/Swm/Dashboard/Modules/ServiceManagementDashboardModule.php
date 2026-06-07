<?php

namespace App\Services\Swm\Dashboard\Modules;

use App\Models\Swm\AttendanceLog;
use App\Models\Swm\Landfill;
use App\Models\Swm\LandfillLog;
use App\Models\Swm\StsLog;
use App\Models\Swm\WasteProcessingLog;
use App\Services\Swm\Dashboard\Concerns\BuildsCountChartAxisLabels;
use App\Services\Swm\Dashboard\Concerns\BuildsCumulativeDateQueries;
use App\Services\Swm\Dashboard\Contracts\SwmDashboardModuleInterface;
use App\Services\Swm\Dashboard\DashboardReportingPeriod;
use App\Services\Swm\Dashboard\SwmDashboardFormatter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceManagementDashboardModule implements SwmDashboardModuleInterface
{
    use BuildsCountChartAxisLabels;
    use BuildsCumulativeDateQueries;

    private const TOP_STACKED_SERIES = 10;

    private const WASTE_STREAMS = [
        'organic_waste_composted_ton' => 'Composted',
        'inorganic_waste_recycled_ton' => 'Recycled',
        'waste_incinerated_ton' => 'Incinerated',
        'waste_burned_open_air_ton' => 'Open-burned',
        'residual_waste_landfilled_ton' => 'Residual-landfilled',
    ];

    public function __construct(
        protected SwmDashboardFormatter $formatter,
    ) {
    }

    public function key(): string
    {
        return 'service_management';
    }

    public function label(): string
    {
        return __('Service Management');
    }

    public function permission(): ?string
    {
        return null;
    }

    public function build(DashboardReportingPeriod $period): array
    {
        return [
            'submodules' => [
                // $this->attendanceSubmodule($period),
                $this->stsLoadingSubmodule($period),
                $this->landfillLoadingSubmodule($period),
                $this->wasteProcessingSubmodule($period),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function attendanceSubmodule(DashboardReportingPeriod $period): array
    {
        return [
            'key' => 'attendance',
            'title' => __('Attendance'),
            'blocks' => [
                [
                    'type' => 'tiles',
                    'items' => [
                        [
                            'label' => __('Average Working Hours per Day'),
                            'value' => $this->formatter->decimal($this->averageWorkingHoursPerDay($period)),
                            'icon' => 'fa-clock',
                        ],
                    ],
                ],
                [
                    'type' => 'charts',
                    'subsection' => __('Visualizations'),
                    'items' => [
                        $this->attendanceTrendChart($period),
                        $this->attendanceByOrganizationChart($period),
                        $this->attendanceByDepartmentChart($period),
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function stsLoadingSubmodule(DashboardReportingPeriod $period): array
    {
        return [
            'key' => 'sts_loading',
            'title' => __('STS Loading'),
            'blocks' => [
                [
                    'type' => 'tiles',
                    'items' => [
                        [
                            'label' => __('Total Loading at STS (Ton)'),
                            'value' => $this->formatter->decimal($this->totalStsLoadingTon($period)),
                            'icon' => 'fa-weight-hanging',
                        ],
                        [
                            'label' => __('Average Daily Loading at STS (Ton)'),
                            'value' => $this->formatter->decimal($this->dailyStsReceiptsAverage($period)),
                            'icon' => 'fa-weight-scale',
                        ],
                    ],
                ],
                [
                    'type' => 'charts',
                    'subsection' => __('Visualizations'),
                    'items' => [
                        $this->stsReceiptsTrendChart($period),
                        $this->receiptsByStsChart($period),
                        // $this->sourceWardContributionToStsChart($period),
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function landfillLoadingSubmodule(DashboardReportingPeriod $period): array
    {
        return [
            'key' => 'landfill_loading',
            'title' => __('Landfill Loading'),
            'blocks' => [
                [
                    'type' => 'tiles',
                    'items' => [
                        [
                            'label' => __('Total Loading at Landfill'),
                            'value' => $this->formatter->decimal($this->totalLandfillLoadingTon($period)),
                            'icon' => 'fa-weight-hanging',
                        ],
                        [
                            'label' => __('Average Daily Loading at Landfill (Ton)'),
                            'value' => $this->formatter->decimal($this->dailyLandfillLoadingAverage($period)),
                            'icon' => 'fa-weight-scale',
                        ],
                        [
                            'label' => __('Average Monthly Loading at Landfill (Ton)'),
                            'value' => $this->formatter->decimal($this->monthlyLandfillLoadingAverage($period)),
                            'icon' => 'fa-mountain-city',
                        ],
                    ],
                ],
                [
                    'type' => 'charts',
                    'subsection' => __('Visualizations'),
                    'items' => [
                        $this->landfillReceiptsTrendChart($period),
                        // $this->sourceWardContributionToLandfillsChart($period),
                        // $this->sourceStsContributionToLandfillsChart($period),
                        // $this->landfillCatchmentNetworkChart($period),
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function wasteProcessingSubmodule(DashboardReportingPeriod $period): array
    {
        $received = $this->totalWasteReceivedForProcessingTon($period);
        $streams = $this->wasteStreamTotalsThroughMonth($period);
        $throughMonth = $period->toMonth->format('M Y');

        return [
            'key' => 'waste_processing',
            'title' => __('Waste Processing'),
            'blocks' => [
                [
                    'type' => 'tiles',
                    'items' => [
                        [
                            'label' => __('Average Monthly Waste Received for Processing (Ton)'),
                            'value' => $this->formatter->decimal($this->averageMonthlyWasteReceivedForProcessingTon($period)),
                            'icon' => 'fa-mountain-city',
                        ],
                        [
                            'label' => __('Average Daily Waste Received for Processing (Ton)'),
                            'value' => $this->formatter->decimal($this->averageDailyWasteReceivedForProcessingTon($period)),
                            'icon' => 'fa-weight-scale',
                        ],
                        [
                            'label' => __('Total Waste Received for Processing (Ton)'),
                            'value' => $this->formatter->decimal($received),
                            'icon' => 'fa-weight-hanging',
                        ],
                    ],
                ],
                [
                    'type' => 'kpis',
                    'subsection' => __('KEY PERFORMANCE INDICATORS'),
                    'items' => $this->wasteProcessingRateKpis($received, $streams),
                ],
                [
                    'type' => 'charts',
                    'subsection' => __('Visualizations'),
                    'items' => [
                        $this->wasteProcessingDistributionChart($received, $streams),
                        $this->monthlyWasteProcessingTrendChart($period),
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array<string, float>  $streams
     * @return list<array<string, mixed>>
     */
    protected function wasteProcessingRateKpis(float $received, array $streams): array
    {
        return [
            [
                'name' => __('Composting Rate'),
                'value' => $this->formatter->safePercent($streams['organic_waste_composted_ton'], $received),
                'unit' => '%',
                'showFrequency' => true,
            ],
            [
                'name' => __('Recycling Rate'),
                'value' => $this->formatter->safePercent($streams['inorganic_waste_recycled_ton'], $received),
                'unit' => '%',
                'showFrequency' => true,
            ],
            [
                'name' => __('Incineration Rate'),
                'value' => $this->formatter->safePercent($streams['waste_incinerated_ton'], $received),
                'unit' => '%',
                'showFrequency' => true,
            ],
            [
                'name' => __('Open Burning Rate'),
                'value' => $this->formatter->safePercent($streams['waste_burned_open_air_ton'], $received),
                'unit' => '%',
                'showFrequency' => true,
            ],
            [
                'name' => __('Residual Waste Landfilling Rate'),
                'value' => $this->formatter->safePercent($streams['residual_waste_landfilled_ton'], $received),
                'unit' => '%',
                'showFrequency' => true,
            ],
            [
                'name' => __('Resource Recovery Rate'),
                'value' => $this->formatter->safePercent(
                    $streams['organic_waste_composted_ton'] + $streams['inorganic_waste_recycled_ton'],
                    $received,
                ),
                'unit' => '%',
                'showFrequency' => true,
            ],
        ];
    }

    protected function averageWorkingHoursPerDay(DashboardReportingPeriod $period): float
    {
        $monthStart = $period->toMonth->copy()->startOfMonth();
        $daysInMonth = max(1, $monthStart->daysInMonth);

        $avgHours = (float) $this->attendanceQuery($period)
            ->where('attendance_status', AttendanceLog::STATUS_PRESENT)
            ->whereNotNull('check_in_at')
            ->whereNotNull('check_out_at')
            ->whereDate('entry_at', '>=', $monthStart->toDateString())
            ->whereDate('entry_at', '<=', $period->periodEnd->toDateString())
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (check_out_at - check_in_at)) / 3600.0) as avg_hours')
            ->value('avg_hours');

        return round($avgHours, 2);
    }

    protected function attendanceTrendChart(DashboardReportingPeriod $period): array
    {
        [$start, $end] = $this->last30DaysRange($period);
        $ratesByDate = $this->dailyAttendanceRates($start, $end, $period);
        $labels = [];
        $values = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $key = $date->toDateString();
            $labels[] = $date->format('M j');
            $values[] = round((float) ($ratesByDate[$key] ?? 0), 1);
        }

        return [
            'id' => 'swmChartSmAttendanceTrend',
            'type' => 'line',
            'title' => __('Attendance Trend'),
            'labels' => $labels,
            'datasets' => [
                ['label' => __('Attendance Rate'), 'data' => $values],
            ],
            'options' => [
                'unitX' => __('Date'),
                'unitY' => __('%'),
                'percentYAxis' => true,
                'decimalValues' => true,
            ],
        ];
    }

    protected function attendanceByOrganizationChart(DashboardReportingPeriod $period): array
    {
        [$start, $end] = $this->last30DaysRange($period);
        $rows = $this->averageDailyAttendanceRatesGrouped($start, $end, $period, 'organization');
        $byOrg = [];
        foreach ($rows as $row) {
            $byOrg[$row['label']] = round((float) $row['rate'], 1);
        }
        $aligned = $this->alignCountsToCategoryAxis($byOrg, $this->masterOrganizationCategoryKeys());

        return [
            'id' => 'swmChartSmAttendanceByOrg',
            'type' => 'bar',
            'title' => __('Attendance by Organization'),
            'labels' => $aligned['labels'],
            'datasets' => [
                ['data' => $aligned['values']],
            ],
            'options' => $this->staticCategoryChartOptions(__('Organization'), __('%'), [
                'percentYAxis' => true,
                'decimalValues' => true,
            ]),
        ];
    }

    protected function attendanceByDepartmentChart(DashboardReportingPeriod $period): array
    {
        [$start, $end] = $this->last30DaysRange($period);
        $rows = $this->averageDailyAttendanceRatesGrouped($start, $end, $period, 'department');
        $byDept = [];
        foreach ($rows as $row) {
            $byDept[$row['label']] = round((float) $row['rate'], 1);
        }
        $aligned = $this->alignCountsToCategoryAxis($byDept, $this->masterAttendanceDepartmentCategoryKeys());

        return [
            'id' => 'swmChartSmAttendanceByDept',
            'type' => 'bar',
            'title' => __('Attendance by Department'),
            'labels' => $aligned['labels'],
            'datasets' => [
                ['data' => $aligned['values']],
            ],
            'options' => $this->staticCategoryChartOptions(__('Department'), __('%'), [
                'percentYAxis' => true,
                'decimalValues' => true,
            ]),
        ];
    }

    protected function totalStsLoadingTon(DashboardReportingPeriod $period): float
    {
        $monthStart = $period->toMonth->copy()->startOfMonth();

        return round((float) $this->stsLogQuery($period)
            ->whereDate('operation_date', '>=', $monthStart->toDateString())
            ->whereDate('operation_date', '<=', $period->periodEnd->toDateString())
            ->sum('quantity_ton'), 2);
    }

    protected function dailyStsReceiptsAverage(DashboardReportingPeriod $period): float
    {
        $monthStart = $period->toMonth->copy()->startOfMonth();
        $daysInMonth = max(1, $monthStart->daysInMonth);

        return round($this->totalStsLoadingTon($period) / $daysInMonth, 2);
    }

    protected function stsReceiptsTrendChart(DashboardReportingPeriod $period): array
    {
        [$start, $end] = $this->last30DaysRange($period);
        $totals = $this->stsLogQuery($period)
            ->whereDate('operation_date', '>=', $start->toDateString())
            ->whereDate('operation_date', '<=', $end->toDateString())
            ->selectRaw('operation_date, SUM(COALESCE(quantity_ton, 0))::float as total')
            ->groupBy('operation_date')
            ->orderBy('operation_date')
            ->pluck('total', 'operation_date');

        return $this->dailyTonLineChart(
            'swmChartSmStsReceiptsTrend',
            __('Waste Loading Trend'),
            $start,
            $end,
            $totals,
        );
    }

    protected function receiptsByStsChart(DashboardReportingPeriod $period): array
    {
        $monthStart = $period->toMonth->copy()->startOfMonth();
        $rows = $this->stsLogQuery($period)
            ->whereDate('operation_date', '>=', $monthStart->toDateString())
            ->whereDate('operation_date', '<=', $period->periodEnd->toDateString())
            ->selectRaw("COALESCE(NULLIF(TRIM(sts_name), ''), 'N/A') as label, SUM(COALESCE(quantity_ton, 0))::float as total")
            ->groupByRaw('1')
            ->get();

        $bySts = [];
        foreach ($rows as $row) {
            $bySts[$row->label] = round((float) $row->total, 2);
        }
        $aligned = $this->alignCountsToCategoryAxis($bySts, $this->masterStsCategoryKeys());

        return [
            'id' => 'swmChartSmReceiptsBySts',
            'type' => 'bar',
            'title' => __('Waste Loading at STS'),
            'labels' => $aligned['labels'],
            'datasets' => [
                ['data' => $aligned['values']],
            ],
            'options' => $this->staticCategoryChartOptions(__('STS'), __('Ton'), [
                'decimalValues' => true,
            ]),
        ];
    }

    protected function sourceWardContributionToStsChart(DashboardReportingPeriod $period): array
    {
        $monthStart = $period->toMonth->copy()->startOfMonth();
        $orgScope = $this->vehicleOrgScopeSql('l');

        $rows = collect(DB::select(
            "
            SELECT
                ward_rows.ward,
                COALESCE(NULLIF(TRIM(l.sts_name), ''), 'N/A') AS series_name,
                SUM(COALESCE(l.quantity_ton, 0))::float AS total
            FROM swm.sts_logs l
            INNER JOIN swm.vehicles v ON v.id = l.vehicle_id AND v.deleted_at IS NULL
            CROSS JOIN LATERAL (
                SELECT elem AS ward
                FROM jsonb_array_elements_text(
                    CASE
                        WHEN l.source_wards IS NOT NULL
                            AND jsonb_typeof(l.source_wards::jsonb) = 'array'
                            AND jsonb_array_length(l.source_wards::jsonb) > 0
                        THEN l.source_wards::jsonb
                        ELSE jsonb_build_array('Unknown')
                    END
                ) AS elem
            ) AS ward_rows
            WHERE l.deleted_at IS NULL
                AND l.operation_date >= ?
                AND l.operation_date <= ?
                {$orgScope['sql']}
            GROUP BY ward_rows.ward, series_name
            ORDER BY ward_rows.ward, series_name
            ",
            array_merge(
                [$monthStart->toDateString(), $period->periodEnd->toDateString()],
                $orgScope['bindings'],
            ),
        ));

        return $this->buildStackedBarFromRows($rows, 'swmChartSmWardToSts', __('Source-Ward Contribution to STS'), __('Ward'), true);
    }

    protected function totalLandfillLoadingTon(DashboardReportingPeriod $period): float
    {
        $monthStart = $period->toMonth->copy()->startOfMonth();

        return round((float) $this->landfillLogQuery($period)
            ->whereDate('operation_date', '>=', $monthStart->toDateString())
            ->whereDate('operation_date', '<=', $period->periodEnd->toDateString())
            ->selectRaw('SUM(COALESCE(quantity_ton, 0)) as total')
            ->value('total'), 2);
    }

    protected function dailyLandfillLoadingAverage(DashboardReportingPeriod $period): float
    {
        $monthStart = $period->toMonth->copy()->startOfMonth();
        $daysInMonth = max(1, $monthStart->daysInMonth);

        return round($this->totalLandfillLoadingTon($period) / $daysInMonth, 2);
    }

    protected function monthlyLandfillLoadingAverage(DashboardReportingPeriod $period): float
    {
        return $this->totalLandfillLoadingTon($period);
    }

    protected function landfillReceiptsTrendChart(DashboardReportingPeriod $period): array
    {
        [$start, $end] = $this->last30DaysRange($period);
        $orgScope = $this->vehicleOrgScopeSql('l');

        $rows = collect(DB::select(
            "
            SELECT l.operation_date::text as operation_date,
                SUM({$this->effectiveWeightSql('l')})::float as total
            FROM swm.landfill_logs l
            INNER JOIN swm.vehicles v ON v.id = l.vehicle_id AND v.deleted_at IS NULL
            WHERE l.deleted_at IS NULL
                AND l.operation_date >= ?
                AND l.operation_date <= ?
                {$orgScope['sql']}
            GROUP BY l.operation_date
            ORDER BY l.operation_date
            ",
            array_merge(
                [$start->toDateString(), $end->toDateString()],
                $orgScope['bindings'],
            ),
        ));

        $totals = $rows->pluck('total', 'operation_date');

        return $this->dailyTonLineChart(
            'swmChartSmLandfillReceiptsTrend',
            __('Waste Loading Trend'),
            $start,
            $end,
            $totals,
        );
    }

    protected function sourceWardContributionToLandfillsChart(DashboardReportingPeriod $period): array
    {
        $monthStart = $period->toMonth->copy()->startOfMonth();
        $orgScope = $this->vehicleOrgScopeSql('l');

        $rows = collect(DB::select(
            "
            SELECT
                ward_rows.ward,
                COALESCE(NULLIF(TRIM(l.landfill_name), ''), 'N/A') AS series_name,
                SUM(COALESCE(l.quantity_ton, 0))::float AS total
            FROM swm.landfill_logs l
            INNER JOIN swm.vehicles v ON v.id = l.vehicle_id AND v.deleted_at IS NULL
            CROSS JOIN LATERAL (
                SELECT elem AS ward
                FROM jsonb_array_elements_text(
                    CASE
                        WHEN l.source_wards IS NOT NULL
                            AND jsonb_typeof(l.source_wards::jsonb) = 'array'
                            AND jsonb_array_length(l.source_wards::jsonb) > 0
                        THEN l.source_wards::jsonb
                        ELSE jsonb_build_array('Unknown')
                    END
                ) AS elem
            ) AS ward_rows
            WHERE l.deleted_at IS NULL
                AND l.operation_date >= ?
                AND l.operation_date <= ?
                {$orgScope['sql']}
            GROUP BY ward_rows.ward, series_name
            ORDER BY ward_rows.ward, series_name
            ",
            array_merge(
                [$monthStart->toDateString(), $period->periodEnd->toDateString()],
                $orgScope['bindings'],
            ),
        ));

        return $this->buildStackedBarFromRows($rows, 'swmChartSmWardToLandfill', __('Source-Ward Contribution to Landfills'), __('Ward'), true);
    }

    protected function sourceStsContributionToLandfillsChart(DashboardReportingPeriod $period): array
    {
        $monthStart = $period->toMonth->copy()->startOfMonth();
        $orgScope = $this->vehicleOrgScopeSql('l');

        $rows = collect(DB::select(
            "
            SELECT
                COALESCE(s.name, 'N/A') AS ward,
                COALESCE(NULLIF(TRIM(l.landfill_name), ''), 'N/A') AS series_name,
                SUM(COALESCE(l.quantity_ton, 0))::float AS total
            FROM swm.landfill_logs l
            INNER JOIN swm.vehicles v ON v.id = l.vehicle_id AND v.deleted_at IS NULL
            CROSS JOIN LATERAL (
                SELECT elem AS sts_id
                FROM jsonb_array_elements_text(
                    CASE
                        WHEN l.source_sts_ids IS NOT NULL
                            AND jsonb_typeof(l.source_sts_ids::jsonb) = 'array'
                            AND jsonb_array_length(l.source_sts_ids::jsonb) > 0
                        THEN l.source_sts_ids::jsonb
                        ELSE jsonb_build_array('0')
                    END
                ) AS elem
            ) AS sts_rows
            LEFT JOIN swm.sts s ON s.id = sts_rows.sts_id::bigint AND s.deleted_at IS NULL
            WHERE l.deleted_at IS NULL
                AND l.operation_date >= ?
                AND l.operation_date <= ?
                AND sts_rows.sts_id <> '0'
                {$orgScope['sql']}
            GROUP BY s.name, series_name
            ORDER BY s.name, series_name
            ",
            array_merge(
                [$monthStart->toDateString(), $period->periodEnd->toDateString()],
                $orgScope['bindings'],
            ),
        ));

        return $this->buildStackedBarFromRows($rows, 'swmChartSmStsToLandfill', __('Source-STS Contribution to Landfills'), __('Source STS'), true);
    }

    protected function landfillCatchmentNetworkChart(DashboardReportingPeriod $period): array
    {
        $landfillQuery = Landfill::query()
            ->whereNull('deleted_at')
            ->where('operational_status', 'active');
        $this->whereThroughPeriodEnd($landfillQuery, 'created_at', $period);
        $landfills = $landfillQuery
            ->orderBy('name')
            ->get(['id', 'name', 'source_sts_ids', 'source_wards']);

        $nodes = [];
        $edges = [];
        $nodeIds = [];

        foreach ($landfills as $landfill) {
            $lfId = 'lf_'.$landfill->id;
            if (! isset($nodeIds[$lfId])) {
                $nodes[] = ['id' => $lfId, 'label' => $landfill->name, 'group' => 'landfill'];
                $nodeIds[$lfId] = true;
            }

            foreach ($landfill->source_wards ?? [] as $ward) {
                $this->appendCatchmentWardLink($nodes, $edges, $nodeIds, $ward, $lfId);
            }

            $stsCollection = $landfill->sourceSts();
            foreach ($stsCollection as $sts) {
                $stsKey = 'sts_'.$sts->id;
                if (! isset($nodeIds[$stsKey])) {
                    $nodes[] = ['id' => $stsKey, 'label' => $sts->name, 'group' => 'sts'];
                    $nodeIds[$stsKey] = true;
                }
                $edges[] = ['from' => $stsKey, 'to' => $lfId];

                foreach ($sts->source_wards ?? [] as $ward) {
                    $this->appendCatchmentWardLink($nodes, $edges, $nodeIds, $ward, $stsKey);
                }
            }
        }

        return [
            'id' => 'swmChartSmLandfillCatchment',
            'type' => 'network',
            'title' => __('Landfill Catchment View'),
            'nodes' => $nodes,
            'edges' => $edges,
            'height' => 450,
        ];
    }

    protected function totalWasteReceivedForProcessingTon(DashboardReportingPeriod $period): float
    {
        return round((float) $this->wasteProcessingQuery($period)
            ->whereDate('reporting_month', '<=', $this->reportingMonthDate($period))
            ->sum('waste_received_ton'), 2);
    }

    protected function averageDailyWasteReceivedForProcessingTon(DashboardReportingPeriod $period): float
    {
        $monthStart = $period->toMonth->copy()->startOfMonth();
        $daysInMonth = max(1, $monthStart->daysInMonth);

        return round($this->totalWasteReceivedForProcessingTon($period) / $daysInMonth, 2);
    }

    protected function averageMonthlyWasteReceivedForProcessingTon(DashboardReportingPeriod $period): float
    {
        return $this->totalWasteReceivedForProcessingTon($period);
    }

    /**
     * Stream tonnage totals cumulative through the selected reporting month.
     *
     * @return array<string, float>
     */
    protected function wasteStreamTotalsThroughMonth(DashboardReportingPeriod $period): array
    {
        $row = $this->wasteProcessingQuery($period)
            ->whereDate('reporting_month', '<=', $this->reportingMonthDate($period))
            ->selectRaw('
                COALESCE(SUM(organic_waste_composted_ton), 0)::float as organic_waste_composted_ton,
                COALESCE(SUM(inorganic_waste_recycled_ton), 0)::float as inorganic_waste_recycled_ton,
                COALESCE(SUM(waste_incinerated_ton), 0)::float as waste_incinerated_ton,
                COALESCE(SUM(waste_burned_open_air_ton), 0)::float as waste_burned_open_air_ton,
                COALESCE(SUM(residual_waste_landfilled_ton), 0)::float as residual_waste_landfilled_ton
            ')
            ->first();

        return [
            'organic_waste_composted_ton' => (float) ($row->organic_waste_composted_ton ?? 0),
            'inorganic_waste_recycled_ton' => (float) ($row->inorganic_waste_recycled_ton ?? 0),
            'waste_incinerated_ton' => (float) ($row->waste_incinerated_ton ?? 0),
            'waste_burned_open_air_ton' => (float) ($row->waste_burned_open_air_ton ?? 0),
            'residual_waste_landfilled_ton' => (float) ($row->residual_waste_landfilled_ton ?? 0),
        ];
    }

    /**
     * @param  array<string, float>  $streams
     * @return array<string, mixed>
     */
    protected function wasteProcessingDistributionChart(float $received, array $streams): array
    {
        $labels = [];
        $values = [];

        foreach (self::WASTE_STREAMS as $column => $labelKey) {
            $ton = $streams[$column] ?? 0;
            if ($received <= 0 || $ton <= 0) {
                continue;
            }
            $labels[] = __($labelKey);
            $values[] = round(($ton / $received) * 100, 1);
        }

        return [
            'id' => 'swmChartSmWasteDistribution',
            'type' => 'doughnut',
            'title' => __('Waste Processing Distribution'),
            'labels' => $labels,
            'datasets' => [
                ['data' => $values],
            ],
            'options' => [
                'unit' => '%',
                'percentValues' => true,
            ],
            'height' => 280,
        ];
    }

    protected function monthlyWasteProcessingTrendChart(DashboardReportingPeriod $period): array
    {
        $endMonth = $period->toMonth->copy()->startOfMonth();
        $startMonth = $endMonth->copy()->subMonths(11);
        $monthKeys = [];
        $labels = [];

        for ($m = $startMonth->copy(); $m->lte($endMonth); $m->addMonth()) {
            $key = $m->format('Y-m-01');
            $monthKeys[] = $key;
            $labels[] = $m->format('M Y');
        }

        $rows = $this->wasteProcessingQuery($period)
            ->whereDate('reporting_month', '>=', $startMonth->toDateString())
            ->whereDate('reporting_month', '<=', $endMonth->toDateString())
            ->selectRaw('
                reporting_month,
                COALESCE(SUM(organic_waste_composted_ton), 0)::float as organic_waste_composted_ton,
                COALESCE(SUM(inorganic_waste_recycled_ton), 0)::float as inorganic_waste_recycled_ton,
                COALESCE(SUM(waste_incinerated_ton), 0)::float as waste_incinerated_ton,
                COALESCE(SUM(waste_burned_open_air_ton), 0)::float as waste_burned_open_air_ton,
                COALESCE(SUM(residual_waste_landfilled_ton), 0)::float as residual_waste_landfilled_ton
            ')
            ->groupBy('reporting_month')
            ->orderBy('reporting_month')
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->reporting_month)->format('Y-m-01'));

        $datasets = [];
        foreach (self::WASTE_STREAMS as $column => $labelKey) {
            $data = [];
            foreach ($monthKeys as $key) {
                $data[] = round((float) ($rows[$key]->{$column} ?? 0), 2);
            }
            $datasets[] = [
                'label' => __($labelKey),
                'data' => $data,
            ];
        }

        return [
            'id' => 'swmChartSmWasteTrend',
            'type' => 'stackedBar',
            'title' => __('Monthly Waste Processing Trend'),
            'labels' => $labels,
            'datasets' => $datasets,
            'options' => [
                'unitX' => __('Reporting Month'),
                'unitY' => __('Ton'),
                'decimalValues' => true,
                'stacked' => true,
            ],
            'height' => 400,
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function last30DaysRange(DashboardReportingPeriod $period): array
    {
        $end = $period->periodEnd->copy()->startOfDay();
        $start = $end->copy()->subDays(29);

        return [$start, $end];
    }

    protected function reportingMonthDate(DashboardReportingPeriod $period): string
    {
        return $period->toMonth->copy()->startOfMonth()->toDateString();
    }

    /**
     * @return array<string, float>
     */
    protected function dailyAttendanceRates(Carbon $start, Carbon $end, DashboardReportingPeriod $period): array
    {
        $rows = $this->attendanceQuery($period)
            ->whereDate('entry_at', '>=', $start->toDateString())
            ->whereDate('entry_at', '<=', $end->toDateString())
            ->selectRaw("
                DATE(entry_at) as log_date,
                COUNT(*) FILTER (WHERE attendance_status = ?) * 100.0 / NULLIF(COUNT(*), 0) as rate
            ", [AttendanceLog::STATUS_PRESENT])
            ->groupByRaw('DATE(entry_at)')
            ->orderByRaw('DATE(entry_at)')
            ->get();

        $rates = [];
        foreach ($rows as $row) {
            $rates[Carbon::parse($row->log_date)->toDateString()] = (float) $row->rate;
        }

        return $rates;
    }

    /**
     * @return list<array{label: string, rate: float}>
     */
    protected function averageDailyAttendanceRatesGrouped(
        Carbon $start,
        Carbon $end,
        DashboardReportingPeriod $period,
        string $groupBy,
    ): array {
        $orgScope = $this->attendanceOrgScopeSql('al');
        $groupColumn = $groupBy === 'organization'
            ? 'o.name'
            : "COALESCE(NULLIF(TRIM(al.department), ''), 'N/A')";

        $rows = DB::select(
            "
            WITH daily_rates AS (
                SELECT
                    {$groupColumn} AS group_label,
                    DATE(al.entry_at) AS log_date,
                    COUNT(*) FILTER (WHERE al.attendance_status = ?) * 100.0 / NULLIF(COUNT(*), 0) AS rate
                FROM swm.attendance_logs al
                INNER JOIN swm.organizations o ON o.id = al.organization_id AND o.deleted_at IS NULL
                WHERE al.deleted_at IS NULL
                    AND DATE(al.entry_at) >= ?
                    AND DATE(al.entry_at) <= ?
                    {$orgScope['sql']}
                GROUP BY group_label, DATE(al.entry_at)
            )
            SELECT group_label AS label, AVG(rate)::float AS rate
            FROM daily_rates
            GROUP BY group_label
            ORDER BY rate DESC
            ",
            array_merge(
                [AttendanceLog::STATUS_PRESENT, $start->toDateString(), $end->toDateString()],
                $orgScope['bindings'],
            ),
        );

        return array_map(fn ($row) => [
            'label' => (string) $row->label,
            'rate' => (float) $row->rate,
        ], $rows);
    }

    /**
     * @param  Collection<int, object{ward?: string, series_name: string, total: float}>  $rows
     * @return array<string, mixed>
     */
    protected function buildStackedBarFromRows(
        Collection $rows,
        string $chartId,
        string $title,
        string $unitX,
        bool $decimalValues,
    ): array {
        if ($rows->isEmpty()) {
            return [
                'id' => $chartId,
                'type' => 'stackedBar',
                'title' => $title,
                'labels' => [],
                'datasets' => [],
                'options' => ['stacked' => true, 'unitX' => $unitX, 'unitY' => __('Ton'), 'decimalValues' => $decimalValues],
                'height' => 400,
            ];
        }

        $seriesTotals = $rows->groupBy('series_name')->map(
            fn (Collection $group) => (float) $group->sum('total'),
        )->sortDesc();

        $topSeries = $seriesTotals->keys()->take(self::TOP_STACKED_SERIES)->all();
        $topSet = array_flip($topSeries);
        $hasOthers = $seriesTotals->count() > count($topSeries);

        $matrix = [];
        foreach ($rows as $row) {
            $ward = (string) ($row->ward ?? 'Unknown');
            $seriesKey = isset($topSet[$row->series_name]) ? $row->series_name : 'others';
            $matrix[$ward][$seriesKey] = ($matrix[$ward][$seriesKey] ?? 0) + (float) $row->total;
        }

        $datasetKeys = $topSeries;
        if ($hasOthers) {
            $datasetKeys[] = 'others';
        }

        $masterCategoryKeys = $unitX === __('Ward')
            ? $this->wardAxisKeys()
            : $this->categoryAxisMasterKeysForUnit($unitX);

        if ($masterCategoryKeys !== null) {
            $alignStacked = $unitX === __('Ward')
                ? $this->alignStackedSeriesToWardAxis(
                    $matrix,
                    $datasetKeys,
                    fn (string $key) => $key === 'others' ? __('Others') : (string) $key,
                )
                : $this->alignStackedSeriesToCategoryAxis(
                    $matrix,
                    $datasetKeys,
                    fn (string $key) => $key === 'others' ? __('Others') : (string) $key,
                    $masterCategoryKeys,
                );
            $labelKeys = $alignStacked['labels'];
            $datasets = array_map(
                static fn (array $dataset) => [
                    'label' => $dataset['label'],
                    'data' => array_map(static fn ($v) => round((float) $v, 2), $dataset['data']),
                ],
                $alignStacked['datasets'],
            );
        } else {
            $labelKeys = $rows->pluck('ward')->unique()->sort()->values()->all();
            $datasets = [];
            foreach ($datasetKeys as $key) {
                $label = $key === 'others' ? __('Others') : (string) $key;
                $data = [];
                foreach ($labelKeys as $ward) {
                    $data[] = round((float) ($matrix[$ward][$key] ?? 0), 2);
                }
                $datasets[] = ['label' => $label, 'data' => $data];
            }
        }

        $chartOptions = [
            'stacked' => true,
            'unitX' => $unitX,
            'unitY' => __('Ton'),
            'decimalValues' => $decimalValues,
        ];
        if ($masterCategoryKeys !== null) {
            $chartOptions['staticCategoryAxis'] = true;
        }

        return [
            'id' => $chartId,
            'type' => 'stackedBar',
            'title' => $title,
            'labels' => $labelKeys,
            'datasets' => $datasets,
            'options' => $chartOptions,
            'height' => 400,
        ];
    }

    /**
     * @param  Collection<string, mixed>|array<string, mixed>  $totals
     * @return array<string, mixed>
     */
    protected function dailyTonLineChart(
        string $id,
        string $title,
        Carbon $start,
        Carbon $end,
        Collection|array $totals,
    ): array {
        if ($totals instanceof Collection) {
            $totals = $totals->all();
        }

        $labels = [];
        $values = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $key = $date->toDateString();
            $labels[] = $date->format('M j');
            $values[] = round((float) ($totals[$key] ?? 0), 2);
        }

        return [
            'id' => $id,
            'type' => 'line',
            'title' => $title,
            'labels' => $labels,
            'datasets' => [
                ['label' => __('Ton'), 'data' => $values],
            ],
            'options' => [
                'unitX' => __('Date'),
                'unitY' => __('Ton'),
                'decimalValues' => true,
            ],
            'fullWidth' => true,
        ];
    }

    protected function effectiveWeightSql(string $alias = 'l'): string
    {
        return "COALESCE({$alias}.quantity_ton, 0)";
    }

    protected function attendanceQuery(DashboardReportingPeriod $period): Builder
    {
        $query = AttendanceLog::query()->whereNull('deleted_at');
        $this->applyAttendanceOrganizationScope($query);

        return $query;
    }

    protected function stsLogQuery(DashboardReportingPeriod $period): Builder
    {
        $query = StsLog::query()
            ->whereNull('deleted_at');

        $this->applyVehicleOrganizationScope($query);

        return $query;
    }

    protected function landfillLogQuery(DashboardReportingPeriod $period): Builder
    {
        $query = LandfillLog::query()
            ->whereNull('deleted_at');

        $this->applyVehicleOrganizationScope($query);

        return $query;
    }

    protected function wasteProcessingQuery(DashboardReportingPeriod $period): Builder
    {
        return WasteProcessingLog::query()->whereNull('deleted_at');
    }

    protected function applyAttendanceOrganizationScope(Builder $query, string $column = 'organization_id'): void
    {
        $orgId = Auth::user()?->swm_organization_id;
        if ($orgId) {
            $query->where($column, $orgId);
        }
    }

    protected function applyVehicleOrganizationScope(Builder $query): void
    {
        $orgId = Auth::user()?->swm_organization_id;
        if (! $orgId) {
            return;
        }

        $query->whereHas('vehicle', function (Builder $vehicleQuery) use ($orgId) {
            $vehicleQuery->whereNull('deleted_at')->where('organization_id', $orgId);
        });
    }

    /**
     * @return array{sql: string, bindings: list<int>}
     */
    protected function attendanceOrgScopeSql(string $alias): array
    {
        $orgId = Auth::user()?->swm_organization_id;
        if (! $orgId) {
            return ['sql' => '', 'bindings' => []];
        }

        return [
            'sql' => " AND {$alias}.organization_id = ?",
            'bindings' => [(int) $orgId],
        ];
    }

    /**
     * @return array{sql: string, bindings: list<int>}
     */
    protected function vehicleOrgScopeSql(string $alias): array
    {
        $orgId = Auth::user()?->swm_organization_id;
        if (! $orgId) {
            return ['sql' => '', 'bindings' => []];
        }

        return [
            'sql' => ' AND v.organization_id = ?',
            'bindings' => [(int) $orgId],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $nodes
     * @param  list<array{from: string, to: string}>  $edges
     * @param  array<string, true>  $nodeIds
     */
    protected function appendCatchmentWardLink(
        array &$nodes,
        array &$edges,
        array &$nodeIds,
        mixed $ward,
        string $toNodeId,
    ): void {
        $wardLabel = trim((string) $ward);
        if ($wardLabel === '') {
            return;
        }

        $wardKey = 'ward_'.$wardLabel;
        if (! isset($nodeIds[$wardKey])) {
            $nodes[] = [
                'id' => $wardKey,
                'label' => __('Ward :ward', ['ward' => $wardLabel]),
                'group' => 'ward',
            ];
            $nodeIds[$wardKey] = true;
        }

        $edges[] = ['from' => $wardKey, 'to' => $toNodeId];
    }
}
