<?php

namespace App\Services\Swm;

use App\Models\Swm\WasteBin;
use Yajra\DataTables\DataTables;

class WasteBinService
{
    public function getAll(array $data)
    {
        $query = WasteBin::query()->with(['household', 'wasteBinType'])->whereNull('deleted_at');

        return DataTables::of($query)
            ->filter(function ($q) use ($data) {
                if (! empty($data['household_id'] ?? null)) {
                    $q->whereHas('household', function ($h) use ($data) {
                        $h->where('household_id', 'ILIKE', '%'.trim((string) $data['household_id']).'%');
                    });
                }
            })
            ->addColumn('household_code', fn ($row) => optional($row->household)->household_id)
            ->addColumn('waste_bin_type_name', fn ($row) => optional($row->wasteBinType)->name)
            ->addColumn('placed_at_buildings_label', fn ($row) => $row->placed_at_buildings ? __('Yes') : __('No'))
            ->make(true);
    }

    public function storeOrUpdate(?WasteBin $wasteBin, array $data): WasteBin
    {
        $wasteBin = $wasteBin ?? new WasteBin();
        $wasteBin->fill($data);
        $wasteBin->save();

        return $wasteBin;
    }
}
