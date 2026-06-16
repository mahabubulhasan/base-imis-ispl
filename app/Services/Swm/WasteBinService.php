<?php

namespace App\Services\Swm;

use App\Models\Swm\WasteBin;
use App\Models\Swm\WasteBinType;
use App\Support\Swm\SwmExcelColumns;
use App\Support\Swm\SwmExcelFilename;
use App\Support\Swm\SwmExcelTemplateWriter;
use App\Support\Swm\SwmImportTemplateOptions;
use Auth;
use Yajra\DataTables\DataTables;

class WasteBinService
{
    public function getAll(array $data)
    {
        $query = WasteBin::query()->with(['wasteBinType'])->whereNull('deleted_at');

        return DataTables::of($query)
            ->filter(fn ($q) => $this->applyFilters($q, $data))
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

                if (Auth::user()->can('View SW Waste Bin History')) {
                    $content .= '<a title="'.__('History').'" href="'.action('Swm\WasteBinController@history', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
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
        $headers = SwmExcelColumns::exportHeaders($this->excelColumnDefinitions());

        $rows = [];
        $query = WasteBin::query()
            ->with(['wasteBinType'])
            ->whereNull('deleted_at');
        $this->applyFilters($query, $data);
        $query->orderBy('id')
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

        (new SwmExcelTemplateWriter())->downloadData(SwmExcelFilename::export('waste_bins'), $headers, $rows);
    }

    protected function applyFilters($query, array $data): void
    {
        if (! empty($data['waste_bin_id'] ?? null)) {
            $query->where('waste_bin_id', 'ILIKE', '%'.trim((string) $data['waste_bin_id']).'%');
        }
        if (! empty($data['waste_bin_type_id'] ?? null)) {
            $query->where('waste_bin_type_id', (int) $data['waste_bin_type_id']);
        }
        if (! empty($data['bin'] ?? null)) {
            $query->where('bin', 'ILIKE', '%'.trim((string) $data['bin']).'%');
        }
        if (! empty($data['ward_no'] ?? null)) {
            $query->where('ward_no', (int) $data['ward_no']);
        }
        if (array_key_exists('placed_at_buildings', $data) && $data['placed_at_buildings'] !== '' && $data['placed_at_buildings'] !== null) {
            $query->where('placed_at_buildings', filter_var($data['placed_at_buildings'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['placed_at_buildings']);
        }
        if (! empty($data['road_no'] ?? null)) {
            $query->where('road_no', 'ILIKE', '%'.trim((string) $data['road_no']).'%');
        }
        if (! empty($data['road_name'] ?? null)) {
            $query->where('road_name', 'ILIKE', '%'.trim((string) $data['road_name']).'%');
        }
    }

    public function downloadTemplate(): void
    {
        (new SwmExcelTemplateWriter())->download(
            SwmExcelFilename::importTemplate('waste_bins'),
            SwmExcelColumns::importTemplateColumns($this->excelColumnDefinitions())
        );
    }

    /** @return array<int, array{key: string, label: string, export?: bool, import?: bool, required?: bool, dropdown?: array<int, string>, multiselect?: bool, reference_key?: string}> */
    protected function excelColumnDefinitions(): array
    {
        $wasteBinTypes = WasteBinType::query()->whereNull('deleted_at')->orderBy('name')->pluck('name')->all();

        return [
            ['key' => 'waste_bin_id', 'label' => __('Waste Bin ID'), 'import' => false],
            ['key' => 'waste_bin_type_name', 'label' => __('Waste Bin Type'), 'import' => false],
            ['key' => 'waste_bin_type', 'label' => __('Waste Bin Type'), 'required' => true, 'dropdown' => $wasteBinTypes, 'export' => false],
            ['key' => 'placed_at_buildings', 'label' => __('Placed at Buildings?'), 'dropdown' => SwmImportTemplateOptions::yesNo()],
            ['key' => 'household_id', 'label' => __('Household ID'), 'dropdown' => SwmImportTemplateOptions::activeHouseholdDbIds(), 'export' => false],
            ['key' => 'bin', 'label' => __('BIN')],
            ['key' => 'ward_no', 'label' => __('Ward No.'), 'dropdown' => SwmImportTemplateOptions::wardNumberStrings()],
            ['key' => 'sub_location', 'label' => __('Sub Location'), 'export' => false],
            ['key' => 'road_no', 'label' => __('Road No.'), 'export' => false],
            ['key' => 'road_name', 'label' => __('Road Name'), 'export' => false],
            ['key' => 'latitude', 'label' => __('Latitude'), 'export' => false],
            ['key' => 'longitude', 'label' => __('Longitude'), 'export' => false],
            ['key' => 'total_capacity_kg', 'label' => __('Capacity (kg)'), 'required' => true],
        ];
    }
}
