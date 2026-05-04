<?php

namespace App\Services\Swm;

use App\Models\Swm\WasteBin;
use Yajra\DataTables\DataTables;

class WasteBinService
{
    public function getAll(array $data)
    {
        $query = WasteBin::query()->with(['wasteBinType'])->whereNull('deleted_at');

        return DataTables::of($query)
            ->addColumn('waste_bin_type_name', fn ($row) => optional($row->wasteBinType)->name)
            ->addColumn('placed_at_buildings_label', fn ($row) => $row->placed_at_buildings ? __('Yes') : __('No'))
            ->make(true);
    }

    public function storeOrUpdate(?WasteBin $wasteBin, array $data): WasteBin
    {
        $wasteBin = $wasteBin ?? new WasteBin();
        $wasteBin->fill($data);
        $wasteBin->type_other_detail = null;
        $wasteBin->save();

        return $wasteBin;
    }
}
