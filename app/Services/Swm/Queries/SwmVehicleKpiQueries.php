<?php

namespace App\Services\Swm\Queries;

use App\Models\Swm\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SwmVehicleKpiQueries
{
    public function groupedCountByVehicleTypeQuery(): Builder
    {
        return Vehicle::query()
            ->leftJoin('swm.vehicle_types as vt', 'vt.id', '=', 'swm.vehicles.vehicle_type_id')
            ->select(
                DB::raw("COALESCE(vt.name, 'N/A') as vehicle_type"),
                DB::raw('COUNT(swm.vehicles.id) as total')
            )
            ->whereNull('swm.vehicles.deleted_at')
            ->groupBy('vehicle_type')
            ->orderBy('vehicle_type');
    }

    public function activeCountAndFleetCapacity(): VehicleFleetStats
    {
        $row = Vehicle::query()
            ->whereNull('deleted_at')
            ->selectRaw('COUNT(*) AS vehicle_count')
            ->selectRaw("SUM(COALESCE(NULLIF(capacity, ''), '0')::numeric) AS total_capacity")
            ->first();

        return VehicleFleetStats::fromDatabaseRow($row);
    }
}
