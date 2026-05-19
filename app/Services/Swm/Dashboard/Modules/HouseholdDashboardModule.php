<?php

namespace App\Services\Swm\Dashboard\Modules;

use App\Models\BuildingInfo\Household;
use App\Models\LayerInfo\Lic;
use App\Models\Swm\Complaint;
use App\Models\Swm\LandfillLog;
use App\Services\Swm\Dashboard\Concerns\BuildsCumulativeDateQueries;
use App\Services\Swm\Dashboard\Contracts\SwmDashboardModuleInterface;
use App\Services\Swm\Dashboard\DashboardReportingPeriod;
use App\Services\Swm\Dashboard\SwmDashboardFormatter;
use App\Services\Swm\SwmModuleSettingsService;
use Illuminate\Support\Facades\DB;

class HouseholdDashboardModule implements SwmDashboardModuleInterface
{
    use BuildsCumulativeDateQueries;

    public function __construct(
        protected SwmModuleSettingsService $settingsService,
        protected SwmDashboardFormatter $formatter,
    ) {
    }

    public function key(): string
    {
        return 'households';
    }

    public function label(): string
    {
        return __('Households & LIC');
    }

    public function permission(): ?string
    {
        return null;
    }

    public function build(DashboardReportingPeriod $period): array
    {
        $p = $this->settingsService->perCapitaKgPerDay();
        $agg = $this->householdAggregates();
        $activeMembers = (float) $agg->active_members;
        $nonLicActiveHh = (int) $agg->non_lic_active_hh;
        $avgFamilySize = (float) $agg->avg_family_size;
        $dailyGenTon = ($p * $activeMembers) / 1000;
        $licPopulationTotal = (float) Lic::query()->sum('population_total');
        $totalPopulation = $licPopulationTotal + ($nonLicActiveHh * $avgFamilySize);

        $collectedDailyKg = (float) $agg->active_collected_daily_kg;
        $disposedDesignated = $this->landfillDisposedTon($period);
        $avgActiveFamily = (float) $agg->avg_active_family_size;

        return [
            'submodules' => [
                $this->municipalitySubmodule($agg, $totalPopulation, $avgFamilySize),
                $this->wasteGenerationSubmodule(
                    $period,
                    $p,
                    $agg,
                    $dailyGenTon,
                    $collectedDailyKg,
                    $disposedDesignated,
                    $avgActiveFamily,
                ),
                $this->licSubmodule($period, $agg),
            ],
        ];
    }

    protected function householdAggregates(): object
    {
        return DB::table('building_info.households')
            ->whereNull('deleted_at')
            ->selectRaw('COUNT(*) as total_households')
            ->selectRaw('COUNT(DISTINCT holding_number) as total_holdings')
            ->selectRaw('COUNT(DISTINCT ward) as total_wards')
            ->selectRaw('AVG(COALESCE(number_of_family_members, 0)) as avg_family_size')
            ->selectRaw("SUM(CASE WHEN status = ? THEN COALESCE(number_of_family_members, 0) ELSE 0 END) as active_members", [Household::STATUS_ACTIVE])
            ->selectRaw("COUNT(CASE WHEN status = ? AND (is_lic = false OR is_lic IS NULL) THEN 1 END) as non_lic_active_hh", [Household::STATUS_ACTIVE])
            ->selectRaw("AVG(CASE WHEN status = ? THEN COALESCE(number_of_family_members, 0) END) as avg_active_family_size", [Household::STATUS_ACTIVE])
            ->selectRaw('SUM(CASE WHEN status = ? THEN COALESCE(daily_waste_volume, 0) ELSE 0 END) as active_collected_daily_kg', [Household::STATUS_ACTIVE])
            ->selectRaw('AVG(CASE WHEN status = ? THEN COALESCE(daily_waste_volume, 0) END) as avg_active_collected_kg', [Household::STATUS_ACTIVE])
            ->selectRaw('COUNT(CASE WHEN segregation_practiced = true THEN 1 END) as segregation_yes')
            ->selectRaw('COUNT(CASE WHEN segregation_practiced = false OR segregation_practiced IS NULL THEN 1 END) as segregation_no')
            ->selectRaw('COUNT(CASE WHEN is_lic = true THEN 1 END) as lic_hh_count')
            ->selectRaw("SUM(CASE WHEN is_lic = true AND status = ? THEN COALESCE(number_of_family_members, 0) ELSE 0 END) as lic_active_members", [Household::STATUS_ACTIVE])
            ->selectRaw('COUNT(CASE WHEN is_lic = true AND segregation_practiced = true THEN 1 END) as lic_seg_yes')
            ->first();
    }

    protected function municipalitySubmodule(object $agg, float $totalPopulation, float $avgFamilySize): array
    {
        $wardCounts = DB::table('building_info.households')
            ->whereNull('deleted_at')
            ->whereNotNull('ward')
            ->selectRaw('ward, COUNT(*) as total')
            ->groupBy('ward')
            ->orderBy('ward')
            ->get();

        $binCounts = DB::table('building_info.households')
            ->whereNull('deleted_at')
            ->selectRaw('waste_bin_provided, COUNT(*) as total')
            ->groupBy('waste_bin_provided')
            ->get();

        $binLabels = [];
        $binValues = [];
        foreach ($binCounts as $row) {
            $binLabels[] = $row->waste_bin_provided ? __('Yes') : __('No');
            $binValues[] = (int) $row->total;
        }

        return [
            'key' => 'municipality',
            'title' => __('Municipality Overview'),
            'blocks' => [
                [
                    'type' => 'tiles',
                    'items' => [
                        ['label' => __('Total Population'), 'value' => $this->formatter->integer($totalPopulation), 'icon' => 'fa-users'],
                        ['label' => __('Total Households'), 'value' => $this->formatter->integer($agg->total_households), 'icon' => 'fa-house-chimney'],
                        ['label' => __('Total Holdings'), 'value' => $this->formatter->integer($agg->total_holdings), 'icon' => 'fa-id-card'],
                        ['label' => __('Total Wards'), 'value' => $this->formatter->integer($agg->total_wards), 'icon' => 'fa-map'],
                        ['label' => __('Average Family Size'), 'value' => $this->formatter->decimal($avgFamilySize, 1), 'icon' => 'fa-people-roof'],
                    ],
                ],
                [
                    'type' => 'charts',
                    'subsection' => __('Visualizations'),
                    'items' => [
                        [
                            'id' => 'swmChartHouseholdsByWard',
                            'type' => 'bar',
                            'title' => __('Households by Ward'),
                            'labels' => $wardCounts->pluck('ward')->all(),
                            'datasets' => [
                                ['label' => __('Count'), 'data' => $wardCounts->pluck('total')->map(fn ($v) => (int) $v)->all()],
                            ],
                            'options' => ['unitX' => __('Ward'), 'integerYTicks' => true],
                        ],
                        [
                            'id' => 'swmChartWasteBinPresence',
                            'type' => 'doughnut',
                            'title' => __('Buildings by Waste Bin Availability'),
                            'labels' => $binLabels,
                            'datasets' => [
                                ['label' => __('Count'), 'data' => $binValues],
                            ],
                            'options' => [],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function wasteGenerationSubmodule(
        DashboardReportingPeriod $period,
        float $p,
        object $agg,
        float $dailyGenTon,
        float $collectedDailyKg,
        float $disposedDesignated,
        float $avgActiveFamily,
    ): array {
        $pTimesAvgFamily = $p * $avgActiveFamily;
        $collectedDailyTon = $collectedDailyKg / 1000;
        $landfillDays = $period->window(DashboardReportingPeriod::SOURCE_LANDFILL)->days;
        $disposedDesignatedDaily = $landfillDays > 0
            ? $disposedDesignated / $landfillDays
            : 0;
        $nonDesignated = max(0, $collectedDailyTon - $disposedDesignatedDaily);
        $uncollected = max(0, $dailyGenTon - $collectedDailyTon);
        $pctCollected = $dailyGenTon > 0 ? ($collectedDailyTon / $dailyGenTon) * 100 : 0;
        $pctSeg = (int) $agg->total_households > 0
            ? ((int) $agg->segregation_yes / (int) $agg->total_households) * 100
            : 0;

        $wardGen = $this->wardGenerationTon($p);
        $wardCollected = $this->wardCollectedTon();
        $functionalUse = $this->functionalUseCollectedTon();
        $heatmap = $this->segregationHeatmap();

        return [
            'key' => 'waste_generation',
            'title' => __('Waste Generation & Collection'),
            'blocks' => [
                [
                    'type' => 'tiles',
                    'items' => [
                        ['label' => __('Daily SW Generation (Ton)'), 'value' => $this->formatter->decimal($dailyGenTon), 'icon' => 'fa-trash'],
                        ['label' => __('Monthly SW Generation (Ton)'), 'value' => $this->formatter->decimal($dailyGenTon * 30), 'icon' => 'fa-calendar-days'],
                        ['label' => __('Per-Capita SW Generation (Kg/day)'), 'value' => $this->formatter->decimal($p), 'icon' => 'fa-weight-scale'],
                        ['label' => __('Average Daily SW Collected per Household (Kg/day)'), 'value' => $this->formatter->decimal($agg->avg_active_collected_kg), 'icon' => 'fa-house'],
                        ['label' => __('Source-Segregated Households'), 'value' => $this->formatter->integer($agg->segregation_yes), 'icon' => 'fa-recycle'],
                        ['label' => __('Households Not Practicing Segregation'), 'value' => $this->formatter->integer($agg->segregation_no), 'icon' => 'fa-circle-xmark'],
                    ],
                ],
                [
                    'type' => 'kpis',
                    'subsection' => __('Key Performance Indicators'),
                    'items' => [
                        ['name' => __('Total SW Generation'), 'value' => $this->formatter->decimal($dailyGenTon), 'unit' => __('Ton/day'), 'showFrequency' => false],
                        ['name' => __('Per Household SW Generation'), 'value' => $this->formatter->decimal($pTimesAvgFamily), 'unit' => __('Kg/day'), 'showFrequency' => true],
                        ['name' => __('SW Collected by Formal System'), 'value' => $this->formatter->decimal($collectedDailyTon), 'unit' => __('Ton/day'), 'showFrequency' => true],
                        ['name' => __('Percent of SW Collected from Total Generated'), 'value' => $this->formatter->percentValue($pctCollected), 'unit' => '%', 'showFrequency' => true],
                        ['name' => __('SW Disposed at Designated Site'), 'value' => $this->formatter->decimal($disposedDesignatedDaily), 'unit' => __('Ton/day'), 'showFrequency' => true],
                        ['name' => __('SW Disposed at Non-Designated Sites'), 'value' => $this->formatter->decimal($nonDesignated), 'unit' => __('Ton/day'), 'showFrequency' => true],
                        ['name' => __('Uncollected SW'), 'value' => $this->formatter->decimal($uncollected), 'unit' => __('Ton/day'), 'showFrequency' => true],
                        ['name' => __('Households Practicing Waste Segregation'), 'value' => $this->formatter->integer($agg->segregation_yes), 'unit' => __('Number'), 'showFrequency' => true, 'hideUnit' => true],
                        ['name' => __('Percent of Households Practicing Waste Segregation'), 'value' => $this->formatter->percentValue($pctSeg), 'unit' => '%', 'showFrequency' => true],
                    ],
                ],
                [
                    'type' => 'charts',
                    'subsection' => __('Visualizations'),
                    'items' => [
                        [
                            'id' => 'swmChartWasteGenByWard',
                            'type' => 'bar',
                            'title' => __('Daily Waste Generation by Ward'),
                            'labels' => $wardGen['labels'],
                            'datasets' => [['label' => __('Ton/day'), 'data' => $wardGen['values']]],
                            'options' => ['unitX' => __('Ward'), 'unitY' => __('Ton/day')],
                        ],
                        [
                            'id' => 'swmChartWasteCollectedByWard',
                            'type' => 'bar',
                            'title' => __('Daily Waste Collected per Ward'),
                            'labels' => $wardCollected['labels'],
                            'datasets' => [['label' => __('Ton/day'), 'data' => $wardCollected['values']]],
                            'options' => ['unitX' => __('Ward'), 'unitY' => __('Ton/day')],
                        ],
                        [
                            'id' => 'swmChartWasteByFunctionalUse',
                            'type' => 'doughnut',
                            'title' => __('Daily Waste Collected by Functional Use'),
                            'labels' => $functionalUse['labels'],
                            'datasets' => [['label' => __('Ton/day'), 'data' => $functionalUse['values']]],
                            'options' => [],
                        ],
                        [
                            'id' => 'swmSegregationHeatmap',
                            'type' => 'heatmap',
                            'title' => __('Segregation Rate by Ward (%)'),
                            'rowLabel' => __('Segregation Rate'),
                            'wards' => $heatmap['wards'],
                            'values' => $heatmap['values'],
                            'options' => ['unit' => '%'],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function licSubmodule(DashboardReportingPeriod $period, object $agg): array
    {
        $licTotal = Lic::query()->count();
        $waterCount = Lic::query()->where('water_connection_status', true)->count();
        $sanitationCount = Lic::query()->where('sanitation_status', true)->count();
        $licPopulationCovered = (float) $agg->lic_active_members + (float) Lic::query()->sum('population_total');
        $licSegRate = (int) $agg->lic_hh_count > 0
            ? ((int) $agg->lic_seg_yes / (int) $agg->lic_hh_count) * 100
            : 0;

        $complaintCount = $this->licComplaintCount($period);
        $licHhCount = max(1, (int) $agg->lic_hh_count);
        $complaintDensity = ($complaintCount / $licHhCount) * 1000;

        $gender = Lic::query()
            ->selectRaw('COALESCE(SUM(population_male), 0) as male')
            ->selectRaw('COALESCE(SUM(population_female), 0) as female')
            ->selectRaw('COALESCE(SUM(population_others), 0) as others')
            ->first();

        return [
            'key' => 'lic',
            'title' => __('LIC'),
            'blocks' => [
                [
                    'type' => 'tiles',
                    'items' => [
                        ['label' => __('LIC Population Covered'), 'value' => $this->formatter->integer($licPopulationCovered), 'icon' => 'fa-people-group'],
                        ['label' => __('LICs with Water Connection'), 'value' => $this->formatter->integer($waterCount), 'icon' => 'fa-faucet'],
                        ['label' => __('LICs with Sanitation Facility'), 'value' => $this->formatter->integer($sanitationCount), 'icon' => 'fa-toilet'],
                        ['label' => __('LIC Waste Segregation Rate'), 'value' => $this->formatter->percent($licSegRate), 'icon' => 'fa-recycle'],
                        // ['label' => __('LIC Complaint Density (Per 1,000 HH)'), 'value' => $this->formatter->decimal($complaintDensity, 1), 'icon' => 'fa-comment-dots'],
                    ],
                ],
                [
                    'type' => 'charts',
                    'subsection' => __('Visualizations'),
                    'items' => [
                        [
                            'id' => 'swmChartLicGender',
                            'type' => 'doughnut',
                            'title' => __('LIC Gender Distribution'),
                            'labels' => [__('Male'), __('Female'), __('Other')],
                            'datasets' => [
                                ['label' => __('Population'), 'data' => [
                                    (int) $gender->male,
                                    (int) $gender->female,
                                    (int) $gender->others,
                                ]],
                            ],
                            'options' => [],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function landfillDisposedTon(DashboardReportingPeriod $period): float
    {
        $window = $period->window(DashboardReportingPeriod::SOURCE_LANDFILL);
        if (! $window->hasData()) {
            return 0.0;
        }

        $query = LandfillLog::query();
        $this->whereWithinWindow($query, 'operation_date', $window);

        return (float) $query->selectRaw('COALESCE(SUM(COALESCE(weighbridge_weight_ton, quantity_ton)), 0) as total')->value('total');
    }

    protected function licComplaintCount(DashboardReportingPeriod $period): int
    {
        $window = $period->window(DashboardReportingPeriod::SOURCE_COMPLAINT);
        if (! $window->hasData()) {
            return 0;
        }

        return (int) DB::table('swm.complaints as c')
            ->whereNull('c.deleted_at')
            ->where('c.date_time', '>=', $window->epoch)
            ->where('c.date_time', '<=', $window->periodEnd)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('building_info.households as h')
                    ->whereColumn('h.holding_number', 'c.holding_number')
                    ->where('h.is_lic', true)
                    ->whereNull('h.deleted_at');
            })
            ->count();
    }

    protected function wardGenerationTon(float $p): array
    {
        $rows = DB::table('building_info.households')
            ->whereNull('deleted_at')
            ->where('status', Household::STATUS_ACTIVE)
            ->whereNotNull('ward')
            ->selectRaw('ward, SUM(COALESCE(number_of_family_members, 0)) as members')
            ->groupBy('ward')
            ->orderBy('ward')
            ->get();

        return [
            'labels' => $rows->pluck('ward')->all(),
            'values' => $rows->map(fn ($r) => round(($p * (float) $r->members) / 1000, 2))->all(),
        ];
    }

    protected function wardCollectedTon(): array
    {
        $rows = DB::table('building_info.households')
            ->whereNull('deleted_at')
            ->whereNotNull('ward')
            ->selectRaw('ward, SUM(COALESCE(daily_waste_volume, 0)) as kg')
            ->groupBy('ward')
            ->orderBy('ward')
            ->get();

        return [
            'labels' => $rows->pluck('ward')->all(),
            'values' => $rows->map(fn ($r) => round((float) $r->kg / 1000, 2))->all(),
        ];
    }

    protected function functionalUseCollectedTon(): array
    {
        $rows = DB::table('building_info.households')
            ->whereNull('deleted_at')
            ->selectRaw("COALESCE(NULLIF(TRIM(functional_use), ''), 'Unknown') as use_label, SUM(COALESCE(daily_waste_volume, 0)) as kg")
            ->groupBy(DB::raw("COALESCE(NULLIF(TRIM(functional_use), ''), 'Unknown')"))
            ->orderByDesc('kg')
            ->get();

        return [
            'labels' => $rows->pluck('use_label')->map(fn ($l) => $l === 'Unknown' ? __('Unknown') : $l)->all(),
            'values' => $rows->map(fn ($r) => round((float) $r->kg / 1000, 2))->all(),
        ];
    }

    protected function segregationHeatmap(): array
    {
        $rows = DB::table('building_info.households')
            ->whereNull('deleted_at')
            ->whereNotNull('ward')
            ->selectRaw('ward')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COUNT(CASE WHEN segregation_practiced = true THEN 1 END) as yes_count')
            ->groupBy('ward')
            ->orderBy('ward')
            ->get();

        return [
            'wards' => $rows->pluck('ward')->all(),
            'values' => $rows->map(fn ($r) => (int) $r->total > 0
                ? round(((int) $r->yes_count / (int) $r->total) * 100, 1)
                : 0)->all(),
        ];
    }
}
