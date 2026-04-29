<?php

namespace App\Services\Swm\Queries;

use App\Models\Swm\Complaint;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SwmComplaintKpiQueries
{
    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public function dateBounds(Carbon $from, Carbon $to): array
    {
        return [$from->copy()->startOfMonth(), $to->copy()->endOfMonth()];
    }

    public function baseInRange(Carbon $from, Carbon $to): Builder
    {
        [$start, $end] = $this->dateBounds($from, $to);

        return Complaint::query()
            ->whereNull('deleted_at')
            ->whereBetween('date_time', [$start, $end]);
    }

    public function countsByTypeInRange(Carbon $from, Carbon $to): Builder
    {
        return $this->baseInRange($from, $to)
            ->select('complaint_type', DB::raw('COUNT(*) as total'))
            ->groupBy('complaint_type')
            ->orderByDesc('total');
    }

    public function countsByWardInRange(Carbon $from, Carbon $to): Builder
    {
        [$start, $end] = $this->dateBounds($from, $to);

        return Complaint::query()
            ->leftJoin('building_info.households as h', 'h.household_id', '=', 'swm.complaints.customer_id')
            ->select(
                DB::raw("COALESCE(h.ward::text, 'N/A') as ward_label"),
                DB::raw('COUNT(swm.complaints.id) as total')
            )
            ->whereNull('swm.complaints.deleted_at')
            ->whereBetween('swm.complaints.date_time', [$start, $end])
            ->groupBy('ward_label')
            ->orderBy('ward_label');
    }

    /**
     * Single query for complaint status buckets (PostgreSQL FILTER).
     */
    public function statusCountsInRange(Carbon $from, Carbon $to): ComplaintStatusCounts
    {
        [$start, $end] = $this->dateBounds($from, $to);

        $row = Complaint::query()
            ->whereNull('deleted_at')
            ->whereBetween('date_time', [$start, $end])
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw("COUNT(*) FILTER (WHERE complaint_status = 'resolved') AS resolved")
            ->selectRaw("COUNT(*) FILTER (WHERE complaint_status = 'pending') AS pending")
            ->selectRaw("COUNT(*) FILTER (WHERE complaint_status IN ('in_process', 'processing')) AS in_process")
            ->selectRaw("COUNT(*) FILTER (WHERE complaint_status IN ('closed')) AS closed")
            ->first();

        return ComplaintStatusCounts::fromDatabaseRow($row);
    }
}
