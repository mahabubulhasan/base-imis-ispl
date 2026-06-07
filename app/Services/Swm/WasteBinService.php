<?php

namespace App\Services\Swm;

use App\Models\LayerInfo\Ward;
use App\Models\Swm\WasteBin;
use App\Models\Swm\WasteBinType;
use App\Support\Swm\SwmExcelTemplateWriter;
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

    public function download(array $data): void
    {
        $headers = [
            'waste_bin_id',
            'waste_bin_type_name',
            'placed_at_buildings',
            'bin',
            'ward_no',
            'total_capacity_kg',
        ];

        $rows = [];
        WasteBin::query()
            ->with(['wasteBinType'])
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->chunk(5000, function ($chunk) use (&$rows) {
                foreach ($chunk as $row) {
                    $rows[] = [
                        $row->waste_bin_id,
                        optional($row->wasteBinType)->name,
                        $row->placed_at_buildings ? __('Yes') : __('No'),
                        $row->bin,
                        $row->ward_no,
                        $row->total_capacity_kg,
                    ];
                }
            });

        (new SwmExcelTemplateWriter())->downloadData('SW Waste Bins.xlsx', $headers, $rows);
    }

    public function downloadTemplate(): void
    {
        $wasteBinTypes = WasteBinType::query()->whereNull('deleted_at')->orderBy('name')->pluck('name')->all();
        $wards = array_map('strval', array_keys(Ward::getInAscOrder()));

        (new SwmExcelTemplateWriter())->download('SW Waste Bins Import Template.xlsx', [
            ['key' => 'waste_bin_type', 'label' => 'waste_bin_type', 'required' => true, 'dropdown' => $wasteBinTypes],
            ['key' => 'placed_at_buildings', 'label' => 'placed_at_buildings', 'dropdown' => [__('Yes'), __('No')]],
            ['key' => 'household_id', 'label' => 'household_id'],
            ['key' => 'bin', 'label' => 'bin'],
            ['key' => 'ward_no', 'label' => 'ward_no', 'dropdown' => $wards],
            ['key' => 'sub_location', 'label' => 'sub_location'],
            ['key' => 'road_no', 'label' => 'road_no'],
            ['key' => 'road_name', 'label' => 'road_name'],
            ['key' => 'latitude', 'label' => 'latitude'],
            ['key' => 'longitude', 'label' => 'longitude'],
            ['key' => 'total_capacity_kg', 'label' => 'total_capacity_kg', 'required' => true],
        ]);
    }
}
