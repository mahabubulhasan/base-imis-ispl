<?php

namespace App\Services\Swm\Queries;

use App\Models\Swm\Worker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SwmWorkerKpiQueries
{
    public function groupedCountByWorkTypeQuery(): Builder
    {
        return Worker::query()
            ->leftJoin('swm.work_types as wt', 'wt.id', '=', 'swm.workers.work_type_id')
            ->select(
                DB::raw("COALESCE(wt.name, 'N/A') as work_type"),
                DB::raw('COUNT(swm.workers.id) as total')
            )
            ->whereNull('swm.workers.deleted_at')
            ->groupBy('work_type')
            ->orderBy('work_type');
    }
}
