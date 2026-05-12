<?php

namespace App\Services\Swm;

use App\Models\Swm\WasteBin;
use Auth;
use Yajra\DataTables\DataTables;

class WasteBinService
{
    public function getAll(array $data)
    {
        $query = WasteBin::query()->with(['wasteBinType'])->whereNull('deleted_at');

        return DataTables::of($query)
            ->editColumn('waste_bin_id', fn ($row) => $row->waste_bin_id ?? '')
            ->addColumn('waste_bin_type_name', fn ($row) => optional($row->wasteBinType)->name)
            ->addColumn('placed_at_buildings_label', fn ($row) => $row->placed_at_buildings ? __('Yes') : __('No'))
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['swm.waste-bins.destroy', $model->id]]);

                if (Auth::user()->can('Edit SW Waste Bin')) {
                    $content .= '<a title="'.__('Edit').'" href="'.action('Swm\WasteBinController@edit', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('View SW Waste Bin')) {
                    $content .= '<a title="'.__('Detail').'" href="'.action('Swm\WasteBinController@show', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }

                if (Auth::user()->can('Delete SW Waste Bin')) {
                    $content .= '<a href="#" title="'.__('Delete').'" class="delete btn btn-danger btn-sm mb-1"><i class="fa fa-trash"></i></a> ';
                }

                $content .= \Form::close();

                return $content;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function storeOrUpdate(?WasteBin $wasteBin, array $data): WasteBin
    {
        $wasteBin = $wasteBin ?? new WasteBin();
        unset($data['waste_bin_id']);
        if ($wasteBin->exists && ! array_key_exists('household_id', $data)) {
            unset($data['household_id']);
        }
        $wasteBin->fill($data);
        $wasteBin->type_other_detail = null;
        $wasteBin->save();

        return $wasteBin;
    }
}
