<?php

namespace App\Services\Swm\Dashboard\Modules;

use App\Models\Swm\Landfill;
use App\Models\Swm\Sts;
use App\Models\Swm\Vehicle;
use App\Models\Swm\WasteBin;
use App\Services\Swm\Dashboard\Concerns\BuildsCountChartAxisLabels;
use App\Services\Swm\Dashboard\Concerns\BuildsCumulativeDateQueries;
use App\Services\Swm\Dashboard\Contracts\SwmDashboardModuleInterface;
use App\Services\Swm\Dashboard\DashboardReportingPeriod;
use App\Services\Swm\Dashboard\SwmDashboardFormatter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceFacilitiesDashboardModule implements SwmDashboardModuleInterface
{
    use BuildsCountChartAxisLabels;
    use BuildsCumulativeDateQueries;

    private const LANDFILL_COMPLIANCE_FIELDS = [
        'weighbridge_facility_available',
        'boundary_wall_available',
        'lighting_arrangement_available',
        'adequate_covering_arrangement_available',
        'gas_control_system_available',
        'leachate_collection_system_available',
    ];

    public function __construct(
        protected SwmDashboardFormatter $formatter,
    ) {
    }

    public function key(): string
    {
        return 'service_facilities';
    }

    public function label(): string
    {
        return __('Service Facilities');
    }

    public function permission(): ?string
    {
        return null;
    }

    public function build(DashboardReportingPeriod $period): array
    {
        $binCount = (int) $this->wasteBinQuery($period)->count();
        $binCapacityKg = (float) $this->wasteBinQuery($period)->sum('total_capacity_kg');
        $vehicleCount = (int) $this->vehicleQuery($period)->count();
        $fleetCapacityTon = $this->sumVehicleCapacity($period);
        $maintainedRatio = $this->vehiclesMaintainedRatio($period);
        $totalStsCount = (int) $this->stsQuery($period)->count();
        $totalLandfillCount = (int) $this->landfillQuery($period)->count();
        $manpowerDeployed = (int) $this->landfillQuery($period)->sum('manpower_deployed');
        $activeStsCount = (int) $this->stsQuery($period)->where('operational_status', 'active')->count();
        $activeLandfillCount = (int) $this->landfillQuery($period)->where('operational_status', 'active')->count();
        $complianceScore = $this->landfillComplianceScore($period);

        return [
            'submodules' => [
                [
                    'key' => 'overview',
                    'title' => __('Service Facilities'),
                    'blocks' => [
                        [
                            'type' => 'tiles',
                            'items' => [
                                [
                                    'label' => __('Total Waste Bins'),
                                    'value' => $this->formatter->integer($binCount),
                                    'icon' => 'fa-trash-can',
                                ],
                                [
                                    'label' => __('Total Bin Capacity (Kg)'),
                                    'value' => $this->formatter->integer($binCapacityKg),
                                    'icon' => 'fa-weight-hanging',
                                ],
                                [
                                    'label' => __('Total Vehicles'),
                                    'value' => $this->formatter->integer($vehicleCount),
                                    'icon' => 'fa-truck',
                                ],
                                [
                                    'label' => __('Total Waste Transport Capacity (Ton)'),
                                    'value' => $this->formatter->decimal($fleetCapacityTon),
                                    'icon' => 'fa-truck-ramp-box',
                                ],
                                [
                                    'label' => __('Vehicles Maintained in Last 12 Months'),
                                    'value' => $this->formatter->percent($maintainedRatio),
                                    'icon' => 'fa-wrench',
                                ],
                                [
                                    'label' => __('Total STSs'),
                                    'value' => $this->formatter->integer($totalStsCount),
                                    'icon' => 'fa-warehouse',
                                ],
                                [
                                    'label' => __('Total Landfills'),
                                    'value' => $this->formatter->integer($totalLandfillCount),
                                    'icon' => 'fa-mountain-city',
                                ],
                                [
                                    'label' => __('Manpower Deployed at Landfills'),
                                    'value' => $this->formatter->integer($manpowerDeployed),
                                    'icon' => 'fa-users',
                                ],
                            ],
                        ],
                        [
                            'type' => 'kpis',
                            'subsection' => __('KEY PERFORMANCE INDICATORS'),
                            'items' => [
                                [
                                    'name' => __('Functional STSs'),
                                    'value' => $this->formatter->integer($activeStsCount),
                                    'unit' => __('Number'),
                                    'showFrequency' => true,
                                    'hideUnit' => true,
                                ],
                                [
                                    'name' => __('Functional Landfills'),
                                    'value' => $this->formatter->integer($activeLandfillCount),
                                    'unit' => __('Number'),
                                    'showFrequency' => true,
                                    'hideUnit' => true,
                                ]
                            ],
                        ],
                        [
                            'type' => 'charts',
                            'subsection' => __('Visualizations'),
                            'items' => [
                                $this->wasteBinsByTypeChart($period),
                                $this->buildingsToWasteBinRatioByWardChart($period),
                                $this->wasteBinsPlacementChart($period),
                                $this->vehiclesByTypeChart($period),
                                // $this->fleetCapacityByServiceWardsChart($period),
                                // $this->fuelTypeDistributionChart($period),
                                $this->stsByWardChart($period),
                                // $this->stsWasteTypeDistributionChart($period),
                                // $this->landfillWasteTypeDistributionChart($period),
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function wasteBinQuery(DashboardReportingPeriod $period): Builder
    {
        $query = WasteBin::query();
        $this->whereThroughPeriodEnd($query, $this->qualifiedColumn(WasteBin::class, 'created_at'), $period);

        return $query;
    }

    protected function stsQuery(DashboardReportingPeriod $period): Builder
    {
        $query = Sts::query();
        $this->whereThroughPeriodEnd($query, $this->qualifiedColumn(Sts::class, 'created_at'), $period);

        return $query;
    }

    protected function landfillQuery(DashboardReportingPeriod $period): Builder
    {
        $query = Landfill::query();
        $this->whereThroughPeriodEnd($query, $this->qualifiedColumn(Landfill::class, 'created_at'), $period);

        return $query;
    }

    protected function vehicleQuery(DashboardReportingPeriod $period): Builder
    {
        $query = Vehicle::query();
        $this->whereThroughPeriodEnd($query, $this->qualifiedColumn(Vehicle::class, 'created_at'), $period);
        $this->applyOrganizationScope($query, $this->qualifiedColumn(Vehicle::class, 'organization_id'));

        return $query;
    }

    protected function qualifiedColumn(string $modelClass, string $column): string
    {
        return (new $modelClass)->getTable().'.'.$column;
    }

    protected function applyOrganizationScope(Builder $query, string $column = 'organization_id'): void
    {
        $orgId = Auth::user()?->swm_organization_id;
        if ($orgId) {
            $query->where($column, $orgId);
        }
    }

    protected function sumVehicleCapacity(DashboardReportingPeriod $period): float
    {
        return (float) $this->vehicleQuery($period)
            ->sum(DB::raw("NULLIF(TRIM(capacity), '')::numeric"));
    }

    protected function vehiclesMaintainedRatio(DashboardReportingPeriod $period): float
    {
        $total = (int) $this->vehicleQuery($period)->count();
        if ($total === 0) {
            return 0;
        }

        $minYear = (int) $period->periodEnd->year - 1;
        $maintained = (int) $this->vehicleQuery($period)
            ->whereNotNull('last_maintenance_year')
            ->where('last_maintenance_year', '>=', $minYear)
            ->count();

        return ($maintained / $total) * 100;
    }

    protected function functionalRatio(Builder $query, string $column, string $activeValue): float
    {
        $total = (int) (clone $query)->count();
        if ($total === 0) {
            return 0;
        }

        $active = (int) (clone $query)->where($column, $activeValue)->count();

        return ($active / $total) * 100;
    }

    protected function landfillComplianceScore(DashboardReportingPeriod $period): float
    {
        $landfills = $this->landfillQuery($period)->get(['id', ...self::LANDFILL_COMPLIANCE_FIELDS]);
        if ($landfills->isEmpty()) {
            return 0;
        }

        $totalScore = 0.0;
        foreach ($landfills as $landfill) {
            $yesCount = 0;
            foreach (self::LANDFILL_COMPLIANCE_FIELDS as $field) {
                if ($landfill->{$field}) {
                    $yesCount++;
                }
            }
            $totalScore += ($yesCount / count(self::LANDFILL_COMPLIANCE_FIELDS)) * 100;
        }

        return $totalScore / $landfills->count();
    }

    protected function wasteBinsByTypeChart(DashboardReportingPeriod $period): array
    {
        $rows = $this->wasteBinQuery($period)
            ->leftJoin('swm.waste_bin_types as wbt', function ($join): void {
                $join->on('swm.waste_bins.waste_bin_type_id', '=', 'wbt.id')
                    ->whereNull('wbt.deleted_at');
            })
            ->selectRaw('COALESCE(wbt.name, ?) as label, COUNT(*) as total', [__('N/A')])
            ->groupBy('wbt.id', 'wbt.name')
            ->orderByDesc('total')
            ->get();

        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $labels[] = $row->label;
            $values[] = (int) $row->total;
        }

        return [
            'id' => 'swmChartSfBinsByType',
            'type' => 'bar',
            'title' => __('Waste Bins by Type'),
            'labels' => $labels,
            'datasets' => [
                ['data' => $values],
            ],
            'options' => [
                'unitX' => __('Type of Waste Bin'),
                'unitY' => $this->countChartAxisY(__('Waste Bins')),
                'integerYTicks' => true,
            ],
        ];
    }

    protected function buildingsToWasteBinRatioByWardChart(DashboardReportingPeriod $period): array
    {
        $buildingsByWard = DB::table('building_info.buildings')
            ->whereNull('deleted_at')
            ->whereNotNull('ward')
            ->selectRaw('ward::text as ward, COUNT(*)::int as total')
            ->groupBy('ward')
            ->pluck('total', 'ward');

        $binsByWard = $this->wasteBinQuery($period)
            ->whereNotNull('ward_no')
            ->selectRaw('ward_no::text as ward, COUNT(*)::int as total')
            ->groupBy('ward_no')
            ->pluck('total', 'ward');

        $wards = $buildingsByWard->keys()
            ->merge($binsByWard->keys())
            ->unique()
            ->sortBy(fn (string $ward) => (int) $ward)
            ->values();

        $labels = [];
        $values = [];
        foreach ($wards as $ward) {
            $buildingCount = (int) ($buildingsByWard[$ward] ?? 0);
            $binCount = (int) ($binsByWard[$ward] ?? 0);
            $labels[] = (string) $ward;
            $values[] = $binCount > 0 ? round($buildingCount / $binCount, 2) : 0;
        }

        return [
            'id' => 'swmChartSfHhBinRatio',
            'type' => 'bar',
            'title' => __('Buildings-to-Waste Bin Ratio by Ward'),
            'labels' => $labels,
            'datasets' => [
                ['label' => __('Ratio'), 'data' => $values],
            ],
            'options' => ['unitX' => __('Ward'), 'unitY' => __('Ratio')],
        ];
    }

    protected function wasteBinsPlacementChart(DashboardReportingPeriod $period): array
    {
        $rows = $this->wasteBinQuery($period)
            ->selectRaw('placed_at_buildings, COUNT(*) as total')
            ->groupBy('placed_at_buildings')
            ->get();

        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $labels[] = $row->placed_at_buildings ? __('At Buildings') : __('Other Places');
            $values[] = (int) $row->total;
        }

        return [
            'id' => 'swmChartSfBinPlacement',
            'type' => 'doughnut',
            'title' => __('Waste Bins Placement Distribution'),
            'labels' => $labels,
            'datasets' => [
                ['data' => $values],
            ],
            'options' => [],
        ];
    }

    protected function vehiclesByTypeChart(DashboardReportingPeriod $period): array
    {
        $rows = $this->vehicleQuery($period)
            ->join('swm.vehicle_types as vt', 'swm.vehicles.vehicle_type_id', '=', 'vt.id')
            ->whereNull('vt.deleted_at')
            ->selectRaw('vt.name as label, COUNT(*) as total')
            ->groupBy('vt.id', 'vt.name')
            ->orderByDesc('total')
            ->get();

        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $labels[] = $row->label;
            $values[] = (int) $row->total;
        }

        return [
            'id' => 'swmChartSfVehiclesByType',
            'type' => 'bar',
            'title' => __('Vehicles by Type'),
            'labels' => $labels,
            'datasets' => [
                ['data' => $values],
            ],
            'options' => [
                'unitX' => __('Vehicle Type'),
                'unitY' => $this->countChartAxisY(__('Vehicles')),
                'integerYTicks' => true,
            ],
        ];
    }

    protected function fleetCapacityByServiceWardsChart(DashboardReportingPeriod $period): array
    {
        $orgScopeSql = '';
        $bindings = [$period->periodEnd];

        $orgId = Auth::user()?->swm_organization_id;
        if ($orgId) {
            $orgScopeSql = ' AND v.organization_id = ?';
            $bindings[] = $orgId;
        }

        $rows = DB::select(
            "
            SELECT
                ward_rows.ward,
                COALESCE(SUM(NULLIF(TRIM(v.capacity), '')::numeric), 0)::float AS total_capacity
            FROM swm.vehicles v
            CROSS JOIN LATERAL (
                SELECT elem AS ward
                FROM jsonb_array_elements_text(
                    CASE
                        WHEN v.service_wards IS NOT NULL
                            AND jsonb_typeof(v.service_wards::jsonb) = 'array'
                            AND jsonb_array_length(v.service_wards::jsonb) > 0
                        THEN v.service_wards::jsonb
                        WHEN v.service_area IS NOT NULL AND v.service_area ~ '^[0-9]+$'
                        THEN jsonb_build_array(v.service_area)
                        ELSE '[]'::jsonb
                    END
                ) AS elem
            ) AS ward_rows
            WHERE v.deleted_at IS NULL
                AND v.created_at <= ?
                {$orgScopeSql}
            GROUP BY ward_rows.ward
            ORDER BY ward_rows.ward::int
            ",
            $bindings,
        );

        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $labels[] = (string) $row->ward;
            $values[] = round((float) $row->total_capacity, 2);
        }

        return [
            'id' => 'swmChartSfFleetByWard',
            'type' => 'bar',
            'title' => __('Fleet Capacity by Service Wards'),
            'labels' => $labels,
            'datasets' => [
                ['label' => __('Capacity'), 'data' => $values],
            ],
            'options' => ['unitX' => __('Service Wards'), 'unitY' => __('Ton')],
        ];
    }

    protected function fuelTypeDistributionChart(DashboardReportingPeriod $period): array
    {
        $naLabel = __('N/A');
        $fuelTypeColumn = $this->qualifiedColumn(Vehicle::class, 'fuel_type');
        $rows = $this->vehicleQuery($period)
            ->selectRaw("COALESCE(NULLIF(TRIM({$fuelTypeColumn}), ''), ?) as label, COUNT(*) as total", [$naLabel])
            ->groupByRaw('1')
            ->orderByDesc('total')
            ->get();

        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $labels[] = $row->label;
            $values[] = (int) $row->total;
        }

        return [
            'id' => 'swmChartSfFuelType',
            'type' => 'doughnut',
            'title' => __('Fuel Type Distribution'),
            'labels' => $labels,
            'datasets' => [
                ['data' => $values],
            ],
            'options' => [],
        ];
    }

    protected function stsByWardChart(DashboardReportingPeriod $period): array
    {
        $rows = $this->stsQuery($period)
            ->whereNotNull('ward_no')
            ->selectRaw('ward_no::text as ward, COUNT(*)::int as total')
            ->groupBy('ward_no')
            ->orderBy('ward_no')
            ->get();

        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $labels[] = $row->ward;
            $values[] = (int) $row->total;
        }

        return [
            'id' => 'swmChartSfStsCapacity',
            'type' => 'bar',
            'title' => __('STS by Ward'),
            'labels' => $labels,
            'datasets' => [
                ['label' => __('Count'), 'data' => $values],
            ],
            'options' => [
                'unitX' => __('Ward'),
                'unitY' => $this->countChartAxisY(__('STS')),
                'integerYTicks' => true,
            ],
        ];
    }

    protected function stsWasteTypeDistributionChart(DashboardReportingPeriod $period): array
    {
        return $this->wasteTypeDistributionChart(
            'swmChartSfStsWasteType',
            __('STS Waste-Type Distribution'),
            'swm.sts',
            's',
            $period,
        );
    }

    protected function landfillWasteTypeDistributionChart(DashboardReportingPeriod $period): array
    {
        return $this->wasteTypeDistributionChart(
            'swmChartSfLandfillWasteType',
            __('Landfill Waste-Type Distribution'),
            'swm.landfills',
            'l',
            $period,
        );
    }

    protected function wasteTypeDistributionChart(
        string $id,
        string $title,
        string $table,
        string $alias,
        DashboardReportingPeriod $period,
    ): array {
        $rows = DB::select(
            "
            SELECT wt.name AS label, COUNT(DISTINCT {$alias}.id)::int AS total
            FROM {$table} {$alias}
            CROSS JOIN LATERAL jsonb_array_elements_text(
                CASE
                    WHEN {$alias}.waste_type_ids IS NOT NULL
                        AND jsonb_typeof({$alias}.waste_type_ids::jsonb) = 'array'
                        AND jsonb_array_length({$alias}.waste_type_ids::jsonb) > 0
                    THEN {$alias}.waste_type_ids::jsonb
                    ELSE '[]'::jsonb
                END
            ) AS wt_id
            INNER JOIN swm.waste_types wt ON wt.id = wt_id::bigint AND wt.deleted_at IS NULL
            WHERE {$alias}.deleted_at IS NULL
                AND {$alias}.created_at <= ?
            GROUP BY wt.id, wt.name
            ORDER BY total DESC
            ",
            [$period->periodEnd],
        );

        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $labels[] = $row->label;
            $values[] = (int) $row->total;
        }

        return [
            'id' => $id,
            'type' => 'doughnut',
            'title' => $title,
            'labels' => $labels,
            'datasets' => [
                ['data' => $values],
            ],
            'options' => [],
        ];
    }
}
