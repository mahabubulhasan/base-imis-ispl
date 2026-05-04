<?php

namespace App\Services\Swm\Queries;

use App\Models\BuildingInfo\Household;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SwmHouseholdKpiQueries
{
    /** Active households for SWM KPIs (not soft-deleted, status active). */
    public function base(): Builder
    {
        return Household::query()->whereNull('deleted_at')->activeStatus();
    }

    /**
     * One round-trip for scalars shared by existing KPIs, coverage, and city statistics (PostgreSQL).
     */
    public function coreAggregate(): HouseholdKpiCoreAggregate
    {
        $table = (new Household)->getTable();

        $row = DB::table($table)
            ->whereNull('deleted_at')
            ->where('status', Household::STATUS_ACTIVE)
            ->selectRaw('COUNT(*) AS household_count')
            ->selectRaw('COALESCE(SUM(daily_waste_volume), 0) AS total_daily_waste_volume_kg')
            ->selectRaw('COALESCE(SUM(daily_waste_volume) FILTER (WHERE van_puller_id IS NOT NULL), 0) AS formal_collected_kg')
            ->selectRaw('COUNT(*) FILTER (WHERE van_puller_id IS NOT NULL) AS covered_household_count')
            ->selectRaw('COUNT(*) FILTER (WHERE segregation_practiced IS TRUE) AS segregated_count')
            ->selectRaw('COUNT(*) FILTER (WHERE segregation_practiced IS NOT TRUE) AS segregation_not_yes_count')
            ->selectRaw('COALESCE(SUM(number_of_family_members), 0) AS total_family_members')
            ->selectRaw('COUNT(DISTINCT holding_number) AS distinct_holdings_count')
            ->selectRaw('COALESCE(AVG(daily_waste_volume) FILTER (WHERE daily_waste_volume IS NOT NULL), 0) AS avg_daily_waste_volume')
            ->first();

        return HouseholdKpiCoreAggregate::fromDatabaseRow($row);
    }

    public function activeForBilling(): Collection
    {
        return $this->base()->get();
    }

    public function averageDailyWasteByWard(): Collection
    {
        return $this->base()
            ->select('ward', DB::raw('AVG(daily_waste_volume) as avg_waste'))
            ->whereNotNull('ward')
            ->groupBy('ward')
            ->orderBy('ward')
            ->get();
    }

    public function workersByWardOrganizationSummary(int $limit = 15): Collection
    {
        return Household::query()
            ->leftJoin('swm.workers as w', 'w.id', '=', 'building_info.households.van_puller_id')
            ->leftJoin('swm.organizations as o', 'o.id', '=', 'w.organization_id')
            ->select(
                DB::raw("COALESCE(building_info.households.ward::text, 'N/A') as ward_label"),
                DB::raw("COALESCE(o.name, 'N/A') as org_name"),
                DB::raw('COUNT(building_info.households.id) as hh_count')
            )
            ->whereNull('building_info.households.deleted_at')
            ->where('building_info.households.status', Household::STATUS_ACTIVE)
            ->groupBy('ward_label', 'org_name')
            ->orderBy('ward_label')
            ->limit($limit)
            ->get();
    }

    public function wardStatisticsRows(): Collection
    {
        return $this->base()
            ->select('ward')
            ->selectRaw('COUNT(*) as total_households')
            ->selectRaw('COUNT(CASE WHEN van_puller_id IS NOT NULL THEN 1 END) as covered_households')
            ->whereNotNull('ward')
            ->groupBy('ward')
            ->orderBy('ward')
            ->get();
    }

    /**
     * Per ward: household count and distinct van pullers assigned to households in that ward.
     */
    public function householdsAndVanPullersByWardRows(): Collection
    {
        return $this->base()
            ->select('ward')
            ->selectRaw('COUNT(*) as total_households')
            ->selectRaw('COUNT(DISTINCT CASE WHEN van_puller_id IS NOT NULL THEN van_puller_id END) as van_puller_count')
            ->whereNotNull('ward')
            ->groupBy('ward')
            ->orderBy('ward')
            ->get();
    }
}
