<?php

namespace App\Services\Swm\Dashboard\Modules;

use App\Models\BuildingInfo\Household;
use App\Models\LayerInfo\Lic;
use App\Models\Swm\Complaint;
use App\Models\Swm\LandfillLog;
use App\Services\Swm\Dashboard\Concerns\BuildsCountChartAxisLabels;
use App\Services\Swm\Dashboard\Concerns\BuildsCumulativeDateQueries;
use App\Services\Swm\Dashboard\Contracts\SwmDashboardModuleInterface;
use App\Services\Swm\Dashboard\DashboardReportingPeriod;
use App\Services\Swm\Dashboard\SwmDashboardFormatter;
use App\Services\Swm\SwmModuleSettingsService;
use Illuminate\Support\Facades\DB;

class HouseholdDashboardModule implements SwmDashboardModuleInterface
{
    use BuildsCountChartAxisLabels;
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
        $agg = $this->householdAggregates($period);
        $avgFamilySize = (float) $agg->avg_family_size;
        $totalPopulation = $this->settingsService->totalPopulationAsOf($period->periodEnd);
        $dailyGenTon = ($p * $totalPopulation) / 1000;

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

    protected function householdAggregates(DashboardReportingPeriod $period): object
    {
        return DB::table('building_info.households')
            ->whereNull('deleted_at')
            ->where('created_at', '<=', $period->periodEnd)
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
            ->selectRaw("SUM(CASE WHEN is_lic = true AND lic_id IS NOT NULL AND status = ? THEN COALESCE(number_of_family_members, 0) ELSE 0 END) as lic_active_members", [Household::STATUS_ACTIVE])
            ->selectRaw('COUNT(CASE WHEN is_lic = true AND segregation_practiced = true THEN 1 END) as lic_seg_yes')
            ->first();
    }

    protected function municipalitySubmodule(object $agg, float $totalPopulation, float $avgFamilySize): array
    {
        $wardCountsByWard = DB::table('building_info.households')
            ->whereNull('deleted_at')
            ->whereNotNull('ward')
            ->selectRaw('ward, COUNT(*) as total')
            ->groupBy('ward')
            ->orderBy('ward')
            ->pluck('total', 'ward')
            ->all();

        $householdsByWard = $this->alignCountsToWardAxis($wardCountsByWard);

        $binCounts = DB::table('building_info.households')
            ->whereNull('deleted_at')
            ->selectRaw('waste_bin_provided, COUNT(*) as total')
            ->groupBy('waste_bin_provided')
            ->get();

        $byBin = [
            'yes' => 0,
            'no' => 0,
        ];
        foreach ($binCounts as $row) {
            $key = $row->waste_bin_provided ? 'yes' : 'no';
            $byBin[$key] = (int) $row->total;
        }
        $binAligned = $this->alignCountsToCategoryAxis(
            $byBin,
            ['yes', 'no'],
            fn (string $key) => $key === 'yes' ? __('Yes') : __('No'),
        );

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
                            'labels' => $householdsByWard['labels'],
                            'datasets' => [
                                ['label' => __('Count'), 'data' => array_map(static fn ($v) => (int) $v, $householdsByWard['values'])],
                            ],
                            'options' => $this->staticCategoryChartOptions(
                                __('Ward'),
                                $this->countChartAxisY(__('Households')),
                                ['integerYTicks' => true],
                            ),
                            'height' => 320,
                        ],
                        [
                            'id' => 'swmChartWasteBinPresence',
                            'type' => 'doughnut',
                            'title' => __('Buildings by Waste Bin Availability'),
                            'labels' => $binAligned['labels'],
                            'datasets' => [
                                ['label' => __('Count'), 'data' => array_map(static fn ($v) => (int) $v, $binAligned['values'])],
                            ],
                            'options' => [],
                            'height' => 320,
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
        $segregationByWard = $this->segregationRateByWard();

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
                    'subsection' => __('KEY PERFORMANCE INDICATORS'),
                    'items' => [
                        ['name' => __('Total SW Generation'), 'value' => $this->formatter->decimal($dailyGenTon), 'unit' => __('Ton/day'), 'showFrequency' => false],
                        ['name' => __('Per Household SW Generation'), 'value' => $this->formatter->decimal($pTimesAvgFamily), 'unit' => __('Kg/day'), 'showFrequency' => true],
                        ['name' => __('SW Collected by Formal System'), 'value' => $this->formatter->decimal($collectedDailyTon), 'unit' => __('Ton/day'), 'showFrequency' => true],
                        ['name' => __('Percent of SW Collected from Total Generated'), 'value' => $this->formatter->percentValue($pctCollected, 2), 'unit' => '%', 'showFrequency' => true],
                        ['name' => __('SW Disposed at Designated Site'), 'value' => $this->formatter->decimal($disposedDesignatedDaily), 'unit' => __('Ton/day'), 'showFrequency' => true],
                        ['name' => __('SW Disposed at Non-Designated Sites'), 'value' => $this->formatter->decimal($nonDesignated), 'unit' => __('Ton/day'), 'showFrequency' => true],
                        ['name' => __('Uncollected SW'), 'value' => $this->formatter->decimal($uncollected), 'unit' => __('Ton/day'), 'showFrequency' => true],
                        ['name' => __('Households Practicing Waste Segregation'), 'value' => $this->formatter->integer($agg->segregation_yes), 'unit' => __('Number'), 'showFrequency' => true, 'hideUnit' => true],
                        ['name' => __('Percent of Households Practicing Waste Segregation'), 'value' => $this->formatter->percentValue($pctSeg, 2), 'unit' => '%', 'showFrequency' => true],
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
                            'options' => $this->staticCategoryChartOptions(__('Ward'), __('Ton/day'), [
                                'decimalValues' => true,
                            ]),
                        ],
                        [
                            'id' => 'swmChartWasteCollectedByWard',
                            'type' => 'bar',
                            'title' => __('Daily Waste Collected per Ward'),
                            'labels' => $wardCollected['labels'],
                            'datasets' => [['label' => __('Ton/day'), 'data' => $wardCollected['values']]],
                            'options' => $this->staticCategoryChartOptions(__('Ward'), __('Ton/day'), [
                                'decimalValues' => true,
                            ]),
                        ],
                        // [
                        //     'id' => 'swmChartWasteByFunctionalUse',
                        //     'type' => 'doughnut',
                        //     'title' => __('Daily Waste Collected by Functional Use'),
                        //     'labels' => $functionalUse['labels'],
                        //     'datasets' => [['label' => __('Ton/day'), 'data' => $functionalUse['values']]],
                        //     'options' => [
                        //         'unit' => __('Ton/day'),
                        //         'decimalValues' => true,
                        //     ],
                        // ],
                        [
                            'id' => 'swmChartSegregationByWard',
                            'type' => 'bar',
                            'title' => __('Segregation Rate by Ward'),
                            'labels' => $segregationByWard['labels'],
                            'datasets' => [
                                ['label' => __('Segregation Rate'), 'data' => $segregationByWard['values']],
                            ],
                            'options' => $this->staticCategoryChartOptions(__('Ward'), '%', [
                                'percentYAxis' => true,
                                'decimalValues' => true,
                            ]),
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
        $licPopulationCovered = (float) $agg->lic_active_members;
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

        $licsByWard = $this->licsByWard();

        return [
            'key' => 'lic',
            'title' => __('LIC'),
            'blocks' => [
                [
                    'type' => 'tiles',
                    'items' => [
                        ['label' => __('Total LICs'), 'value' => $this->formatter->integer($licTotal), 'icon' => 'fa-building'],
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
                            'id' => 'swmChartLicsByWard',
                            'type' => 'bar',
                            'title' => __('LICs by Ward'),
                            'labels' => $licsByWard['labels'],
                            'datasets' => [
                                ['label' => __('Count'), 'data' => $licsByWard['values']],
                            ],
                            'options' => $this->staticCategoryChartOptions(
                                __('Ward'),
                                $this->countChartAxisY(__('LICs')),
                                ['integerYTicks' => true],
                            ),
                        ],
                        [
                            'id' => 'swmChartLicGender',
                            'type' => 'doughnut',
                            'title' => __('Gender Distribution in LIC'),
                            'labels' => [__('Male'), __('Female'), __('Other')],
                            'datasets' => [
                                ['data' => [
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

        return (float) $query->selectRaw('COALESCE(SUM(COALESCE(quantity_ton, 0)), 0) as total')->value('total');
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

        $byWard = [];
        foreach ($rows as $row) {
            $byWard[$row->ward] = round(($p * (float) $row->members) / 1000, 2);
        }

        return $this->alignCountsToWardAxis($byWard);
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

        $byWard = [];
        foreach ($rows as $row) {
            $byWard[$row->ward] = round((float) $row->kg / 1000, 2);
        }

        return $this->alignCountsToWardAxis($byWard);
    }

    protected function functionalUseCollectedTon(): array
    {
        $rows = DB::table('building_info.households as h')
            ->leftJoin('building_info.buildings as b', function ($join) {
                $join->on('b.bin', '=', 'h.bin')->whereNull('b.deleted_at');
            })
            ->leftJoin('building_info.functional_uses as fu', 'fu.id', '=', 'b.functional_use_id')
            ->whereNull('h.deleted_at')
            ->selectRaw("COALESCE(NULLIF(TRIM(fu.name), ''), 'Unknown') as use_label, SUM(COALESCE(h.daily_waste_volume, 0)) as kg")
            ->groupBy(DB::raw("COALESCE(NULLIF(TRIM(fu.name), ''), 'Unknown')"))
            ->orderByDesc('kg')
            ->get();

        return [
            'labels' => $rows->pluck('use_label')->map(fn ($l) => $l === 'Unknown' ? __('Unknown') : $l)->all(),
            'values' => $rows->map(fn ($r) => round((float) $r->kg / 1000, 2))->all(),
        ];
    }

    protected function segregationRateByWard(): array
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

        $byWard = [];
        foreach ($rows as $row) {
            $byWard[$row->ward] = (int) $row->total > 0
                ? round(((int) $row->yes_count / (int) $row->total) * 100, 2)
                : 0;
        }

        return $this->alignCountsToWardAxis($byWard);
    }

    protected function licsByWard(): array
    {
        $rows = Lic::query()
            ->whereNotNull('ward')
            ->selectRaw('ward')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('ward')
            ->orderBy('ward')
            ->get();

        $byWard = $rows->pluck('total', 'ward')->all();

        return $this->alignCountsToWardAxis($byWard);
    }
}
