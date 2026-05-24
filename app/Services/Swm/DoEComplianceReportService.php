<?php

namespace App\Services\Swm;

use App\Models\BuildingInfo\Household;
use App\Models\LayerInfo\Lic;
use App\Models\Swm\Landfill;
use App\Models\Swm\LandfillLog;
use App\Models\Swm\LandfillType;
use App\Models\Swm\Organization;
use App\Models\Swm\OrganizationType;
use App\Models\Swm\StsLog;
use App\Models\Swm\Vehicle;
use App\Models\Swm\VehicleType;
use App\Models\Swm\WasteBin;
use App\Models\Swm\WasteBinType;
use App\Models\Swm\WasteProcessingLog;
use App\Services\Swm\Dashboard\AnnualReportingPeriod;
use App\Services\Swm\Dashboard\SwmDashboardFormatter;
use Illuminate\Support\Facades\DB;

class DoEComplianceReportService
{
    public function __construct(
        protected SwmModuleSettingsService $settingsService,
        protected SwmDashboardFormatter $formatter,
    ) {
    }

    public function municipalityDisplayName(): string
    {
        $bn = trim((string) config('app.city_bn', ''));
        if ($bn !== '') {
            $suffixBn = trim((string) config('app.city_suffix_bn', 'পৌরসভা'));
            if ($suffixBn !== '' && ! str_ends_with($bn, $suffixBn)) {
                return $bn.' '.$suffixBn;
            }

            return $bn;
        }

        $city = trim((string) config('app.city', ''));
        if ($city === '') {
            return '';
        }

        $suffix = trim((string) config('app.city_suffix', ''));

        return $suffix !== '' ? $city.' '.$suffix : $city;
    }

    public function resolveYear(?int $year): int
    {
        $earliest = (int) config('doe_compliance_report.earliest_year', 2021);
        $latest = (int) now()->year;
        $year = $year ?? $latest;

        return max($earliest, min($latest, $year));
    }

    /**
     * @return array<int, int>
     */
    public function availableYears(): array
    {
        $earliest = (int) config('doe_compliance_report.earliest_year', 2021);
        $latest = (int) now()->year;

        return range($latest, $earliest);
    }

    /**
     * @return array<string, mixed>
     */
    public function build(int $year): array
    {
        $period = AnnualReportingPeriod::forYear($this->resolveYear($year));
        $agg = $this->householdAggregates($period);
        $p = $this->settingsService->perCapitaKgPerDay();

        $activeMembers = (float) $agg->active_members;
        $totalPopulation = $this->settingsService->totalPopulationAsOf($period->yearEnd);

        $dailyGenTon = ($p * $activeMembers) / 1000;
        $collectedDailyKg = (float) $agg->active_collected_daily_kg;
        $collectedDailyTon = $collectedDailyKg / 1000;

        $landfillDisposedAnnual = $this->landfillDisposedTonForYear($period);

        $collectedAnnual = $collectedDailyTon * $period->daysInYear;

        $stockpiledAnnual = $landfillDisposedAnnual > 0
            ? $landfillDisposedAnnual
            : $this->designatedDisposedDailyTon($period, $collectedDailyTon) * $period->daysInYear;

        $uncollectedAnnual = max(0, $dailyGenTon - $collectedDailyTon) * $period->daysInYear;

        $annualTotal = $dailyGenTon * $period->daysInYear;

        $wasteProcessing = $this->wasteProcessingTotals($period);
        $storage = $this->storageMetrics($period);

        $orgName = $this->municipalityDisplayName();

        return [
            'year' => $period->year,
            'days_in_year' => $period->daysInYear,
            'institution' => [
                'org_name' => $orgName,
                'population' => (string) (int) round($totalPopulation),
            ],
            'waste_quantity' => [
                'daily_avg' => $this->roundTon($dailyGenTon),
                'annual_total' => $this->roundTon($annualTotal),
                'collected_formal' => $this->roundTon($collectedAnnual),
                'stockpiled' => $this->roundTon($stockpiledAnnual),
                'uncollected' => $this->roundTon($uncollectedAnnual),
            ],
            'waste_processing' => [
                'proc_organic' => $this->roundTon($wasteProcessing['organic_waste_composted_ton']),
                'proc_recycle' => $this->roundTon($wasteProcessing['inorganic_waste_recycled_ton']),
                'proc_incineration' => $this->roundTon($wasteProcessing['waste_incinerated_ton']),
                'proc_openburn' => $this->roundTon($wasteProcessing['waste_burned_open_air_ton']),
                'proc_landfill' => $this->roundTon($wasteProcessing['residual_waste_landfilled_ton']),
            ],
            'landfill_types' => $this->landfillTypeRows($period),
            'landfill_catalog' => $this->landfillCatalogRows($period),
            'landfill_info' => [
                'num_landfill_sites' => (string) (int) $this->landfillSnapshotQuery($period)->count(),
                'sites' => $this->landfillInfoRows($period),
            ],
            'storage' => $storage,
            'private_organizations' => $this->privateOrganizations($period),
            'bins' => $this->wasteBinCatalogRows($period),
            'waste_bin_catalog' => $this->wasteBinCatalogRows($period),
            'waste_bin_type_options' => $this->wasteBinTypeOptions(),
            'transport' => $this->vehicleCatalogRows($period),
            'vehicle_catalog' => $this->vehicleCatalogRows($period),
            'vehicle_type_options' => $this->vehicleTypeOptions(),
            'lic' => $this->licMetrics(),
            'landfill_type_options' => $this->landfillTypeOptions(),
        ];
    }

    /**
     * @return list<string>
     */
    public function landfillTypeOptions(): array
    {
        return LandfillType::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    /**
     * @return list<string>
     */
    public function wasteBinTypeOptions(): array
    {
        return WasteBinType::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    /**
     * @return list<string>
     */
    public function vehicleTypeOptions(): array
    {
        return VehicleType::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    protected function roundTon(float $value): string
    {
        return $this->formatter->decimal($value, 2);
    }

    protected function householdAggregates(AnnualReportingPeriod $period): object
    {
        return DB::table('building_info.households')
            ->whereNull('deleted_at')
            ->where('created_at', '<=', $period->yearEnd)
            ->selectRaw('COUNT(*) as total_households')
            ->selectRaw("SUM(CASE WHEN status = ? THEN COALESCE(number_of_family_members, 0) ELSE 0 END) as active_members", [Household::STATUS_ACTIVE])
            ->selectRaw('SUM(CASE WHEN status = ? THEN COALESCE(daily_waste_volume, 0) ELSE 0 END) as active_collected_daily_kg', [Household::STATUS_ACTIVE])
            ->first();
    }

    protected function landfillDisposedTonForYear(AnnualReportingPeriod $period): float
    {
        return (float) LandfillLog::query()
            ->whereNull('deleted_at')
            ->where('operation_status', LandfillLog::STATUS_COMPLETED)
            ->whereDate('operation_date', '>=', $period->yearStartDateString())
            ->whereDate('operation_date', '<=', $period->yearEndDateString())
            ->selectRaw('COALESCE(SUM(COALESCE(weighbridge_weight_ton, quantity_ton)), 0) as total')
            ->value('total');
    }

    protected function stsCollectedTonForYear(AnnualReportingPeriod $period): float
    {
        return (float) StsLog::query()
            ->whereNull('deleted_at')
            ->where('operation_status', StsLog::STATUS_COMPLETED)
            ->whereDate('operation_date', '>=', $period->yearStartDateString())
            ->whereDate('operation_date', '<=', $period->yearEndDateString())
            ->sum('quantity_ton');
    }

    protected function designatedDisposedDailyTon(AnnualReportingPeriod $period, float $collectedDailyTon): float
    {
        $windowDays = max(1, $period->daysInYear);
        $total = $this->landfillDisposedTonForYear($period);

        if ($total > 0) {
            return $total / $windowDays;
        }

        return max(0, $collectedDailyTon);
    }

    /**
     * @return array<string, float>
     */
    protected function wasteProcessingTotals(AnnualReportingPeriod $period): array
    {
        $row = WasteProcessingLog::query()
            ->whereNull('deleted_at')
            ->whereDate('reporting_month', '>=', $period->yearStartDateString())
            ->whereDate('reporting_month', '<=', $period->yearEndDateString())
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
     * @return list<array<string, mixed>>
     */
    protected function landfillSiteCatalogRows(AnnualReportingPeriod $period): array
    {
        $capacityUnit = config('doe_compliance_report.default_landfill_unit', 'টন');
        $areaUnit = config('doe_compliance_report.default_landfill_area_unit', 'একর');
        $rows = [];

        $landfills = $this->landfillSnapshotQuery($period)
            ->leftJoin('swm.landfill_types as lt', function ($join): void {
                $join->on('swm.landfills.landfill_type_id', '=', 'lt.id')
                    ->whereNull('lt.deleted_at');
            })
            ->select([
                'swm.landfills.id',
                'swm.landfills.name',
                'swm.landfills.capacity',
                'swm.landfills.area',
                'swm.landfills.weighbridge_facility_available',
                'swm.landfills.boundary_wall_available',
                'swm.landfills.lighting_arrangement_available',
                'swm.landfills.manpower_deployed',
                'swm.landfills.adequate_covering_arrangement_available',
                'swm.landfills.gas_control_system_available',
                'swm.landfills.leachate_collection_system_available',
                DB::raw('COALESCE(lt.name, \'\') as type_name'),
            ])
            ->orderBy('swm.landfills.name')
            ->get();

        foreach ($landfills as $lf) {
            $capacityTon = $this->parseNumericCapacity($lf->capacity);
            $areaValue = $lf->area !== null ? (float) $lf->area : null;
            $rows[] = [
                'landfill_id' => (int) $lf->id,
                'name' => (string) $lf->name,
                'type' => (string) $lf->type_name,
                'capacity' => $this->formatLandfillCapacityForInput($lf->capacity, $capacityTon),
                'unit' => $capacityUnit,
                'area' => $this->formatLandfillCapacityForInput($lf->area, $areaValue),
                'area_unit' => $areaUnit,
                'wb' => $this->boolToYn($lf->weighbridge_facility_available),
                'bw' => $this->boolToYn($lf->boundary_wall_available),
                'lt' => $this->boolToYn($lf->lighting_arrangement_available),
                'pax' => $lf->manpower_deployed !== null ? (string) $lf->manpower_deployed : '',
                'cv' => $this->boolToYn($lf->adequate_covering_arrangement_available),
                'gas' => $this->boolToYn($lf->gas_control_system_available),
                'lc' => $this->boolToYn($lf->leachate_collection_system_available),
            ];
        }

        return $rows;
    }

    /**
     * @return list<array{landfill_id: int, name: string, type: string, capacity: string, unit: string}>
     */
    protected function landfillCatalogRows(AnnualReportingPeriod $period): array
    {
        return $this->landfillSiteCatalogRows($period);
    }

    /**
     * @return list<array{landfill_id: int, name: string, type: string, capacity: string, unit: string}>
     */
    protected function landfillTypeRows(AnnualReportingPeriod $period): array
    {
        return $this->landfillSiteCatalogRows($period);
    }

    protected function parseNumericCapacity(mixed $capacity): ?float
    {
        if ($capacity === null || $capacity === '') {
            return null;
        }

        $numeric = preg_replace('/[^0-9.]/', '', (string) $capacity);

        return $numeric !== '' ? (float) $numeric : null;
    }

    /**
     * Plain number string for number inputs (no thousands separators).
     * Omits decimals when the stored landfill capacity is a whole number.
     */
    protected function formatLandfillCapacityForInput(mixed $raw, ?float $parsed): string
    {
        if ($parsed === null) {
            return '';
        }

        if (abs($parsed - round($parsed)) < 0.00001) {
            return (string) (int) round($parsed);
        }

        return rtrim(rtrim(number_format($parsed, 2, '.', ''), '0'), '.');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Landfill>
     */
    protected function landfillSnapshotQuery(AnnualReportingPeriod $period)
    {
        $table = (new Landfill)->getTable();

        return Landfill::query()
            ->whereNull($table.'.deleted_at')
            ->where($table.'.created_at', '<=', $period->yearEnd);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function landfillInfoRows(AnnualReportingPeriod $period): array
    {
        return array_map(static function (array $row) {
            return [
                'landfill_id' => $row['landfill_id'],
                'name' => $row['name'],
                'area' => $row['area'],
                'unit' => $row['area_unit'],
                'wb' => $row['wb'],
                'bw' => $row['bw'],
                'lt' => $row['lt'],
                'pax' => $row['pax'],
                'cv' => $row['cv'],
                'gas' => $row['gas'],
                'lc' => $row['lc'],
            ];
        }, $this->landfillSiteCatalogRows($period));
    }

    protected function boolToYn(?bool $value): string
    {
        if ($value === null) {
            return '';
        }

        return $value ? 'yes' : 'no';
    }

    /**
     * @return array{collection_volume: string, num_houses: string}
     */
    protected function storageMetrics(AnnualReportingPeriod $period): array
    {
        $binTable = (new WasteBin)->getTable();
        $binQuery = WasteBin::query()
            ->whereNull($binTable.'.deleted_at')
            ->where($binTable.'.created_at', '<=', $period->yearEnd);

        $capacityKg = (float) (clone $binQuery)->sum($binTable.'.total_capacity_kg');
        $atBuildings = (int) (clone $binQuery)->where($binTable.'.placed_at_buildings', true)->count();

        return [
            'collection_volume' => $this->roundTon($capacityKg / 1000),
            'num_houses' => (string) $atBuildings,
        ];
    }

    /**
     * @return list<array{name: string, description: string}>
     */
    protected function privateOrganizations(AnnualReportingPeriod $period): array
    {
        return Organization::query()
            ->whereNull('deleted_at')
            ->where('status', true)
            ->whereHas('organizationType', function ($q) {
                $q->whereNull('deleted_at')
                    ->where('id', '!=', OrganizationType::ID_GOVERNMENT);
            })
            ->where('created_at', '<=', $period->yearEnd)
            ->orderBy('name')
            ->get(['name', 'remarks'])
            ->map(fn ($org) => [
                'name' => $org->name,
                'description' => (string) ($org->remarks ?? ''),
            ])
            ->all();
    }

    /**
     * @return list<array{type: string, size: string, unit: string, count: string}>
     */
    protected function wasteBinCatalogRows(AnnualReportingPeriod $period): array
    {
        $unit = config('doe_compliance_report.default_waste_bin_unit', 'কেজি');
        $binTable = (new WasteBin)->getTable();

        $statsByTypeId = WasteBin::query()
            ->whereNull($binTable.'.deleted_at')
            ->where($binTable.'.created_at', '<=', $period->yearEnd)
            ->whereNotNull($binTable.'.waste_bin_type_id')
            ->selectRaw($binTable.'.waste_bin_type_id as type_id')
            ->selectRaw('COUNT(*)::int as total')
            ->selectRaw('AVG('.$binTable.'.total_capacity_kg) as avg_capacity')
            ->groupBy($binTable.'.waste_bin_type_id')
            ->get()
            ->keyBy('type_id');

        $rows = [];

        $types = WasteBinType::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name']);

        foreach ($types as $type) {
            $stat = $statsByTypeId->get($type->id);
            $count = $stat ? (int) $stat->total : 0;
            $avgKg = $stat && $stat->avg_capacity !== null ? (float) $stat->avg_capacity : null;
            $hasCount = $count > 0;
            $hasAvg = $avgKg !== null;

            $rows[] = [
                'type' => (string) $type->name,
                'size' => ($hasCount && $hasAvg) ? $this->formatLandfillCapacityForInput(null, $avgKg) : '',
                'unit' => ($hasCount && $hasAvg) ? $unit : '',
                'count' => $hasCount ? (string) $count : '',
            ];
        }

        $uncategorized = WasteBin::query()
            ->whereNull($binTable.'.deleted_at')
            ->where($binTable.'.created_at', '<=', $period->yearEnd)
            ->whereNull($binTable.'.waste_bin_type_id')
            ->selectRaw('COUNT(*)::int as total')
            ->selectRaw('AVG('.$binTable.'.total_capacity_kg) as avg_capacity')
            ->first();

        if ($uncategorized && (int) $uncategorized->total > 0) {
            $count = (int) $uncategorized->total;
            $avgKg = $uncategorized->avg_capacity !== null ? (float) $uncategorized->avg_capacity : null;
            $hasAvg = $avgKg !== null;

            $rows[] = [
                'type' => __('Uncategorized'),
                'size' => $hasAvg ? $this->formatLandfillCapacityForInput(null, $avgKg) : '',
                'unit' => $hasAvg ? $unit : '',
                'count' => (string) $count,
            ];
        }

        return $rows;
    }

    /**
     * @return list<array{type: string, existing: string, required: string}>
     */
    protected function vehicleCatalogRows(AnnualReportingPeriod $period): array
    {
        $vehicleTable = (new Vehicle)->getTable();

        $statsByTypeId = Vehicle::query()
            ->whereNull($vehicleTable.'.deleted_at')
            ->where($vehicleTable.'.created_at', '<=', $period->yearEnd)
            ->whereNotNull($vehicleTable.'.vehicle_type_id')
            ->selectRaw($vehicleTable.'.vehicle_type_id as type_id')
            ->selectRaw('COUNT(*)::int as total')
            ->groupBy($vehicleTable.'.vehicle_type_id')
            ->get()
            ->keyBy('type_id');

        $rows = [];

        $types = VehicleType::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name']);

        foreach ($types as $type) {
            $stat = $statsByTypeId->get($type->id);
            $count = $stat ? (int) $stat->total : 0;

            $rows[] = [
                'type' => (string) $type->name,
                'existing' => $count > 0 ? (string) $count : '',
                'required' => '',
            ];
        }

        $uncategorized = Vehicle::query()
            ->whereNull($vehicleTable.'.deleted_at')
            ->where($vehicleTable.'.created_at', '<=', $period->yearEnd)
            ->whereNull($vehicleTable.'.vehicle_type_id')
            ->count();

        if ($uncategorized > 0) {
            $rows[] = [
                'type' => __('Uncategorized'),
                'existing' => (string) $uncategorized,
                'required' => '',
            ];
        }

        return $rows;
    }

    /**
     * @return array{total_slums: string, slums_sanitation: string}
     */
    protected function licMetrics(): array
    {
        $total = Lic::query()->count();
        $sanitation = Lic::query()->where('sanitation_status', true)->count();

        return [
            'total_slums' => (string) $total,
            'slums_sanitation' => (string) $sanitation,
        ];
    }
}
